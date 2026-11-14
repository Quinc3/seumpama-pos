<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnModel extends Model
{
    protected $fillable = [
        'model_name',
        'model_version',
        'algorithm',
        'architecture',
        'feature_schema',
        'target_definition',
        'artifact_path',
        'scaler_path',
        'status',
        'dataset_version_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'architecture' => 'array',
            'feature_schema' => 'array',
            'target_definition' => 'array',
        ];
    }

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(AnnModelIngredient::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(AnnPrediction::class);
    }

    public function trainingRuns(): HasMany
    {
        return $this->hasMany(AnnTrainingRun::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
