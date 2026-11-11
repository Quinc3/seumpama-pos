import logging
from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import JSONResponse

from app.config import settings
from app.middleware import RequestIdMiddleware
from app.responses import success_response, error_response

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