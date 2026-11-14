<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatasetVersion extends Model
{
    protected $fillable = [
        'version',
        'dataset_name',
        'start_date',
        'end_date',
        'total_records',
        'status',
        'feature_schema',
        'validation_summary',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_records' => 'integer',
            'feature_schema' => 'array',
            'validation_summary' => 'array',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(DatasetRecord::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateVersionNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "dataset-";
        $seq = static::where('version', 'like', "dataset-v%")->count() + 1;

        return "{$prefix}v" . str_pad($seq, 3, '0', STR_PAD_LEFT) . "-{$date}";
    }
}
