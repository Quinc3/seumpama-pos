<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnTrainingRun extends Model
{
    protected $fillable = [
        'ann_model_id',
        'dataset_version_id',
        'training_id',
        'training_started_at',
        'training_completed_at',
        'training_status',
        'hyperparameters',
        'train_metrics',
        'validation_metrics',
        'test_metrics',
        'error_message',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'training_started_at' => 'datetime',
            'training_completed_at' => 'datetime',
            'hyperparameters' => 'array',
            'train_metrics' => 'array',
            'validation_metrics' => 'array',
            'test_metrics' => 'array',
        ];
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AnnModel::class, 'ann_model_id');
    }

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }
}
