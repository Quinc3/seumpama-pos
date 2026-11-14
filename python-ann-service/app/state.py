from threading import Lock
from typing import Any

# In-memory training jobs store
# Production: ganti dengan redis/database
_jobs: dict[str, dict[str, Any]] = {}
_lock = Lock()


def create_job(training_id: str) -> None:
    with _lock:
        _jobs[training_id] = {
            "status": "queued",
            "started_at": None,
            "completed_at": None,
            "results": None,
            "error": None,
        }


def update_job(training_id: str, **fields: Any) -> None:
    with _lock:
        if training_id in _jobs:
            _jobs[training_id].update(fields)


def get_job(training_id: str) -> dict[str, Any] | None:
    with _lock:
        return _jobs.get(training_id, {}).copy() if training_id in _jobs else None


def job_exists(training_id: str) -> bool:
    with _lock:
        return training_id in _jobs