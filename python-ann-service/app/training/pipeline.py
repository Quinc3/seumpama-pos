import json
import pickle
import uuid
from datetime import datetime
from pathlib import Path

import numpy as np
import pandas as pd
from sklearn.metrics import mean_absolute_error, mean_squared_error
from sklearn.preprocessing import StandardScaler

import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers

from app.config import settings
from app.schemas import TrainingRecord, TrainingConfig

FEATURE_COLS = [
    "lag_d1", "lag_d2", "lag_d3", "lag_d7",
    "rolling_mean_3d", "rolling_mean_7d",
    "temperature", "rainfall",
    "day_of_week", "is_weekend",
]
TARGET_COL = "actual_demand"


def train_single_ingredient(
    ingredient_id: int,
    ingredient_code: str,
    ingredient_name: str,
    records: list[TrainingRecord],
    config: TrainingConfig,
) -> dict:
    """Train one MLP model for one ingredient. Returns result dict."""
    # Build dataframe
    df = pd.DataFrame([r.model_dump() for r in records])

    if df.empty:
        return _fail(ingredient_id, ingredient_code, ingredient_name, "No records.")

    # Drop rows with missing features or target (needed for supervised learning)
    df = df.dropna(subset=FEATURE_COLS + [TARGET_COL])

    if len(df) < settings.MIN_TRAINING_SAMPLES:
        return _fail(
            ingredient_id, ingredient_code, ingredient_name,
            f"Insufficient samples after dropna: {len(df)} (min {settings.MIN_TRAINING_SAMPLES})."
        )

    # Chronological order
    df = df.sort_values("business_date").reset_index(drop=True)

    X = df[FEATURE_COLS].astype(float).values
    y = df[TARGET_COL].astype(float).values

    # Chronological split 70/15/15
    n = len(df)
    train_end = max(1, int(n * 0.7))
    val_end = max(train_end + 1, int(n * 0.85))
    if val_end >= n:
        # Too few samples to split; put everything remaining in test
        val_end = n - 1 if n > train_end + 1 else train_end

    X_train, y_train = X[:train_end], y[:train_end]
    X_val, y_val = X[train_end:val_end], y[train_end:val_end]
    X_test, y_test = X[val_end:], y[val_end:]

    if len(X_test) == 0:
        return _fail(
            ingredient_id, ingredient_code, ingredient_name,
            "Test set empty after split (samples too few)."
        )

    # Baseline: naive forecast using lag_d1 as prediction for actual_demand
    lag_d1_idx = FEATURE_COLS.index("lag_d1")
    baseline_test_preds = X_test[:, lag_d1_idx]
    baseline_mae = float(mean_absolute_error(y_test, baseline_test_preds))
    baseline_rmse = float(np.sqrt(mean_squared_error(y_test, baseline_test_preds)))

    # StandardScaler fit on TRAIN ONLY
    scaler = StandardScaler()
    X_train_s = scaler.fit_transform(X_train)
    X_val_s = scaler.transform(X_val) if len(X_val) > 0 else np.empty((0, X.shape[1]))
    X_test_s = scaler.transform(X_test)

    # Build MLP (per ANN_SPECIFICATION.md §9.1)
    tf.random.set_seed(42)
    np.random.seed(42)

    model = keras.Sequential([
        layers.Input(shape=(len(FEATURE_COLS),)),
        layers.Dense(16, activation="relu"),
        layers.Dense(8, activation="relu"),
        layers.Dense(1, activation="linear"),
    ])
    model.compile(
        optimizer=keras.optimizers.Adam(learning_rate=config.learning_rate),
        loss="mse",
        metrics=["mae"],
    )

    callbacks = []
    if len(X_val) > 0:
        callbacks.append(keras.callbacks.EarlyStopping(
            monitor="val_loss",
            patience=config.early_stopping_patience,
            restore_best_weights=True,
        ))

    fit_kwargs = dict(
        epochs=config.epochs,
        batch_size=min(config.batch_size, len(X_train)),
        verbose=0,
        callbacks=callbacks,
    )
    if len(X_val) > 0:
        fit_kwargs["validation_data"] = (X_val_s, y_val)

    history = model.fit(X_train_s, y_train, **fit_kwargs)

    # Evaluate on test set
    y_pred_test = model.predict(X_test_s, verbose=0).flatten()
    test_mae = float(mean_absolute_error(y_test, y_pred_test))
    test_rmse = float(np.sqrt(mean_squared_error(y_test, y_pred_test)))

    # Save artifacts
    model_version = f"ann-{ingredient_id}-{datetime.utcnow().strftime('%Y%m%d')}-{uuid.uuid4().hex[:6]}"
    artifact_dir = Path(settings.MODEL_STORAGE_PATH) / model_version
    artifact_dir.mkdir(parents=True, exist_ok=True)

    model_path = artifact_dir / "model.keras"
    scaler_path = artifact_dir / "scaler.pkl"
    metadata_path = artifact_dir / "metadata.json"

    model.save(str(model_path))
    with open(scaler_path, "wb") as f:
        pickle.dump(scaler, f)

    feature_schema = {
        "version": "1.0",
        "features": FEATURE_COLS,
        "target": TARGET_COL,
    }
    with open(metadata_path, "w") as f:
        json.dump({
            "ingredient_id": ingredient_id,
            "ingredient_code": ingredient_code,
            "ingredient_name": ingredient_name,
            "model_version": model_version,
            "feature_schema": feature_schema,
            "training_config": config.model_dump(),
            "samples": {
                "total": int(n),
                "train": int(len(X_train)),
                "val": int(len(X_val)),
                "test": int(len(X_test)),
            },
            "metrics": {"mae": test_mae, "rmse": test_rmse},
            "baseline": {"mae": baseline_mae, "rmse": baseline_rmse},
            "trained_at": datetime.utcnow().isoformat() + "Z",
        }, f, indent=2)

    return {
        "ingredient_id": ingredient_id,
        "ingredient_code": ingredient_code,
        "ingredient_name": ingredient_name,
        "status": "completed",
        "model_version": model_version,
        "metrics": {"mae": round(test_mae, 4), "rmse": round(test_rmse, 4)},
        "baseline": {"mae": round(baseline_mae, 4), "rmse": round(baseline_rmse, 4)},
        "samples": {
            "total": int(n),
            "train": int(len(X_train)),
            "val": int(len(X_val)),
            "test": int(len(X_test)),
        },
        "artifact_path": str(model_path),
        "scaler_path": str(scaler_path),
        "feature_schema": feature_schema,
    }


def _fail(ingredient_id: int, code: str, name: str, error: str) -> dict:
    return {
        "ingredient_id": ingredient_id,
        "ingredient_code": code,
        "ingredient_name": name,
        "status": "failed",
        "error": error,
    }