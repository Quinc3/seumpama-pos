import logging
from datetime import datetime

from fastapi import BackgroundTasks, FastAPI, HTTPException, Request
from fastapi.responses import JSONResponse

import app.state as training_state
from app.config import settings
from app.middleware import RequestIdMiddleware
from app.responses import error_response, success_response
from app.schemas import DemandPredictionRequest, TrainingRequest
from app.training.pipeline import train_single_ingredient
from app.training.predictor import list_available_models, predict as run_prediction

logging.basicConfig(
    level=settings.LOG_LEVEL,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger("seumpama-ann")


app = FastAPI(
    title="Seumpama ANN Service",
    version=settings.APP_VERSION,
    docs_url=f"{settings.API_PREFIX}/docs" if settings.APP_ENV == "development" else None,
    redoc_url=None,
    openapi_url=f"{settings.API_PREFIX}/openapi.json"
    if settings.APP_ENV == "development"
    else None,
)

app.add_middleware(RequestIdMiddleware)


@app.exception_handler(HTTPException)
async def http_exception_handler(request: Request, exc: HTTPException):
    request_id = getattr(request.state, "request_id", "")
    logger.warning(f"[{request_id}] HTTP {exc.status_code}: {exc.detail}")
    return JSONResponse(
        status_code=exc.status_code,
        content=error_response(
            message=exc.detail,
            code="HTTP_ERROR",
            request_id=request_id,
        ),
    )


@app.get(f"{settings.API_PREFIX}/health")
async def health(request: Request):
    request_id = getattr(request.state, "request_id", "")
    logger.info(f"[{request_id}] health check")
    return success_response(
        message="ANN Service is healthy",
        data={
            "service": "seumpama-ann-service",
            "status": "healthy",
            "version": settings.APP_VERSION,
        },
        request_id=request_id,
    )


@app.post(f"{settings.API_PREFIX}/model/train", status_code=202)
async def submit_training(
    payload: TrainingRequest,
    request: Request,
    background_tasks: BackgroundTasks,
):
    request_id = getattr(request.state, "request_id", "")

    if training_state.job_exists(payload.training_id):
        return JSONResponse(
            status_code=409,
            content=error_response(
                message=f"Training ID '{payload.training_id}' sudah ada. Gunakan ID unik.",
                code="DUPLICATE_TRAINING_ID",
                request_id=request_id,
            ),
        )

    training_state.create_job(payload.training_id)
    logger.info(
        f"[{request_id}] training {payload.training_id} queued "
        f"({len(payload.target_ingredients)} ingredient(s), dataset={payload.dataset_version})"
    )

    background_tasks.add_task(_run_training, payload.training_id, payload, request_id)

    return success_response(
        message="Training request accepted",
        data={"training_id": payload.training_id, "status": "queued"},
        request_id=request_id,
    )


@app.get(f"{settings.API_PREFIX}/model/train/{{training_id}}")
async def get_training_status(training_id: str, request: Request):
    request_id = getattr(request.state, "request_id", "")
    job = training_state.get_job(training_id)

    if job is None:
        return JSONResponse(
            status_code=404,
            content=error_response(
                message=f"Training ID '{training_id}' tidak ditemukan.",
                code="RESOURCE_NOT_FOUND",
                request_id=request_id,
            ),
        )

    return success_response(
        message=f"Training status: {job['status']}",
        data={
            "training_id": training_id,
            "status": job["status"],
            "started_at": job["started_at"],
            "completed_at": job["completed_at"],
            "results": job["results"],
            "error": job["error"],
        },
        request_id=request_id,
    )


@app.get(f"{settings.API_PREFIX}/model/status")
async def get_model_status(request: Request):
    request_id = getattr(request.state, "request_id", "")
    models = list_available_models()

    return success_response(
        message=f"{len(models)} model tersedia",
        data={
            "model_available": len(models) > 0,
            "available_models": len(models),
            "models": models,
        },
        request_id=request_id,
    )


@app.post(f"{settings.API_PREFIX}/predictions/demand")
async def predict_demand(payload: DemandPredictionRequest, request: Request):
    request_id = getattr(request.state, "request_id", "")

    results = []
    for p in payload.predictions:
        try:
            features = p.features.model_dump()
            predicted = run_prediction(p.model_version, features)
            results.append({
                "ingredient_id": p.ingredient_id,
                "ingredient_code": p.ingredient_code,
                "model_version": p.model_version,
                "prediction_date": p.prediction_date.isoformat(),
                "predicted_demand": predicted,
                "status": "valid",
                "error": None,
            })
            logger.info(f"[{request_id}] prediction OK {p.ingredient_code} v{p.model_version} = {predicted}")
        except Exception as e:
            logger.warning(f"[{request_id}] prediction failed {p.ingredient_code}: {e}")
            results.append({
                "ingredient_id": p.ingredient_id,
                "ingredient_code": p.ingredient_code,
                "model_version": p.model_version,
                "prediction_date": p.prediction_date.isoformat(),
                "predicted_demand": None,
                "status": "failed",
                "error": f"{type(e).__name__}: {e}",
            })

    all_ok = all(r["status"] == "valid" for r in results)
    return success_response(
        message="Demand prediction completed" if all_ok else "Prediction completed dengan error",
        data={"predictions": results},
        request_id=request_id,
    )


def _run_training(training_id: str, payload: TrainingRequest, request_id: str):
    """Background task — jangan sampai crash service."""
    try:
        training_state.update_job(
            training_id,
            status="running",
            started_at=datetime.utcnow().isoformat() + "Z",
        )
        logger.info(f"[{request_id}] training {training_id} RUNNING")

        results = []
        for ing in payload.target_ingredients:
            try:
                r = train_single_ingredient(
                    ingredient_id=ing.ingredient_id,
                    ingredient_code=ing.ingredient_code,
                    ingredient_name=ing.ingredient_name,
                    records=ing.records,
                    config=payload.training_config,
                )
                results.append(r)
            except Exception as e:
                logger.exception(f"[{request_id}] train error for {ing.ingredient_code}")
                results.append({
                    "ingredient_id": ing.ingredient_id,
                    "ingredient_code": ing.ingredient_code,
                    "ingredient_name": ing.ingredient_name,
                    "status": "failed",
                    "error": f"{type(e).__name__}: {e}",
                })

        completed_count = sum(1 for r in results if r.get("status") == "completed")
        failed_count = len(results) - completed_count

        training_state.update_job(
            training_id,
            status="completed" if failed_count == 0 else "failed",
            completed_at=datetime.utcnow().isoformat() + "Z",
            results=results,
            error=None if failed_count == 0 else f"{failed_count} ingredient gagal.",
        )
        logger.info(f"[{request_id}] training {training_id} DONE ({completed_count} ok, {failed_count} failed)")

    except Exception as e:
        logger.exception(f"[{request_id}] training {training_id} FATAL")
        training_state.update_job(
            training_id,
            status="failed",
            completed_at=datetime.utcnow().isoformat() + "Z",
            error=f"{type(e).__name__}: {e}",
        )