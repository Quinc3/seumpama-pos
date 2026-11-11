import uuid
from fastapi import Request
from starlette.middleware.base import BaseHTTPMiddleware


class RequestIdMiddleware(BaseHTTPMiddleware):
    """Assign X-Request-Id to each request for tracing."""

    async def dispatch(self, request: Request, call_next):
        request_id = request.headers.get("X-Request-Id") or str(uuid.uuid4())
        request.state.request_id = request_id
        response = await call_next(request)
        response.headers["X-Request-Id"] = request_id
        return response


async def verify_bearer_token(request: Request) -> None:
    """
    Verify Bearer token for protected endpoints.
    Use via Depends(verify_bearer_token) on endpoints that need auth.

    B4: not used by /health (per DEPLOYMENT_GUIDE.md §13.3, health is checked
    before auth verification). Will be applied to training/prediction endpoints
    in Phase 7.
    """
    from fastapi import HTTPException
    from app.config import settings

    # Dev mode: if no token configured, allow through
    if not settings.API_TOKEN or settings.API_TOKEN == "change-me-in-development":
        return

    auth = request.headers.get("Authorization", "")
    if not auth.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Missing Bearer token")

    if auth[7:] != settings.API_TOKEN:
        raise HTTPException(status_code=401, detail="Invalid token")