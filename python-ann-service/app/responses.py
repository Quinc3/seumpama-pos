from typing import Any


def success_response(
    message: str, data: Any, request_id: str = ""
) -> dict[str, Any]:
    return {
        "success": True,
        "message": message,
        "data": data,
        "meta": {"request_id": request_id},
    }


def error_response(
    message: str,
    code: str,
    details: Any = None,
    request_id: str = "",
) -> dict[str, Any]:
    return {
        "success": False,
        "message": message,
        "error": {"code": code, "details": details or []},
        "meta": {"request_id": request_id},
    }