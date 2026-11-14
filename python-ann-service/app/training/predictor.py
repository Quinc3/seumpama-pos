import json
import pickle
from pathlib import Path

import numpy as np
import tensorflow as tf

from app.config import settings


# Feature order HARUS sama dengan pipeline.py FEATURE_COLS
FEATURE_COLS = [
    "lag_d1", "lag_d2", "lag_d3", "lag_d7",
    "rolling_mean_3d", "rolling_mean_7d",
    "temperature", "rainfall",
    "day_of_week", "is_weekend",
]


def _model_dir(model_version: str) -> Path:
    return Path(settings.MODEL_STORAGE_PATH) / model_version


def load_metadata(model_version: str) -> dict:
    path = _model_dir(model_version) / "metadata.json"
    if not path.exists():
        raise FileNotFoundError(f"Metadata tidak ditemukan: {model_version}")
    with open(path) as f:
        return json.load(f)


def predict(model_version: str, features: dict) -> float:
    base = _model_dir(model_version)
    model_path = base / "model.keras"
    scaler_path = base / "scaler.pkl"

    if not model_path.exists() or not scaler_path.exists():
        raise FileNotFoundError(f"Model artifacts incomplete untuk {model_version}")

    missing = [f for f in FEATURE_COLS if f not in features]
    if missing:
        raise ValueError(f"Missing features: {missing}")

    x_raw = np.array([[float(features[f]) for f in FEATURE_COLS]], dtype=float)

    model = tf.keras.models.load_model(str(model_path))
    with open(scaler_path, "rb") as f:
        scaler = pickle.load(f)

    x_scaled = scaler.transform(x_raw)
    y_pred = float(model.predict(x_scaled, verbose=0).flatten()[0])

    return max(0.0, round(y_pred, 4))


def list_available_models() -> list[dict]:
    base = Path(settings.MODEL_STORAGE_PATH)
    if not base.exists():
        return []

    models = []
    for model_dir in sorted(base.iterdir()):
        if not model_dir.is_dir():
            continue
        meta_path = model_dir / "metadata.json"
        if not meta_path.exists():
            continue
        try:
            with open(meta_path) as f:
                meta = json.load(f)
            models.append({
                "model_version": meta.get("model_version"),
                "ingredient_id": meta.get("ingredient_id"),
                "ingredient_code": meta.get("ingredient_code"),
                "ingredient_name": meta.get("ingredient_name"),
                "metrics": meta.get("metrics"),
                "trained_at": meta.get("trained_at"),
            })
        except Exception:
            continue

    return models