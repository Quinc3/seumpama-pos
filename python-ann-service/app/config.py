import os
from pathlib import Path
from dotenv import load_dotenv

BASE_DIR = Path(__file__).resolve().parent.parent
load_dotenv(BASE_DIR / ".env")


class Settings:
    APP_ENV: str = os.getenv("APP_ENV", "development")
    APP_VERSION: str = os.getenv("APP_VERSION", "1.0.0")
    SERVICE_HOST: str = os.getenv("SERVICE_HOST", "127.0.0.1")
    SERVICE_PORT: int = int(os.getenv("SERVICE_PORT", "8001"))
    API_PREFIX: str = os.getenv("API_PREFIX", "/api/v1")
    API_TOKEN: str = os.getenv("API_TOKEN", "")
    LOG_LEVEL: str = os.getenv("LOG_LEVEL", "INFO")
    MODEL_STORAGE_PATH: str = os.getenv("MODEL_STORAGE_PATH", str(BASE_DIR / "artifacts" / "models"))
    MIN_TRAINING_SAMPLES: int = int(os.getenv("MIN_TRAINING_SAMPLES", "10"))


settings = Settings()