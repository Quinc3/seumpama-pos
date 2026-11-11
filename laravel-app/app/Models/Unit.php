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
        return $this->ingredients()->exists();
    }

    public function ingredients()
    {
        return $this->hasMany(\App\Models\Ingredient::class);
    }
}
