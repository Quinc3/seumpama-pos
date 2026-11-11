<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'symbol', 'unit_type', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function isUsed(): bool
    {
        // Phase 2c: return $this->ingredients()->exists();
        // Belum ada relasi karena Ingredient belum dibuat.
        return false;
    }
}
