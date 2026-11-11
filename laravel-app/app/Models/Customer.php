<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'notes'];

    public function isUsed(): bool
    {
        // Phase 4: return $this->orders()->exists();
        return false;
    }
}
