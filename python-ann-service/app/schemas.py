from datetime import date, datetime
from typing import Optional
from pydantic import BaseModel, Field


class TrainingRecord(BaseModel):
    business_date: date
    actual_demand: float = Field(ge=0)
    lag_d1: Optional[float] = None
    lag_d2: Optional[float] = None
    lag_d3: Optional[float] = None
    lag_d7: Optional[float] = None
    rolling_mean_3d: Optional[float] = None
    rolling_mean_7d: Optional[float] = None
    temperature: Optional[float] = None
    rainfall: Optional[float] = None
    day_of_week: int = Field(ge=0, le=6)
    is_weekend: bool


class TargetIngredient(BaseModel):
    ingredient_id: int
    ingredient_code: str
    ingredient_name: str
    records: list[TrainingRecord] = Field(min_length=1)


class TrainingConfig(BaseModel):
    epochs: int = Field(default=100, ge=1, le=1000)
    batch_size: int = Field(default=16, ge=1, le=128)
    learning_rate: float = Field(default=0.001, gt=0)
    early_stopping_patience: int = Field(default=10, ge=1, le=50)


class TrainingRequest(BaseModel):
    training_id: str = Field(min_length=1, max_length=100)
    dataset_version: str = Field(min_length=1, max_length=100)
    target_ingredients: list[TargetIngredient] = Field(min_length=1)
    training_config: TrainingConfig = Field(default_factory=TrainingConfig)


class IngredientResult(BaseModel):
    ingredient_id: int
    ingredient_code: str
    ingredient_name: str
    status: str
    error: Optional[str] = None
    model_version: Optional[str] = None
    metrics: Optional[dict] = None
    baseline: Optional[dict] = None
    samples: Optional[dict] = None
    artifact_path: Optional[str] = None
    scaler_path: Optional[str] = None
    feature_schema: Optional[dict] = None


class TrainingStatus(BaseModel):
    training_id: str
    status: str  # queued | running | completed | failed
    started_at: Optional[datetime] = None
    completed_at: Optional[datetime] = None
    results: Optional[list[IngredientResult]] = None
    error: Optional[str] = None