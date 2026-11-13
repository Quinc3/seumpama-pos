<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'business_session_id',
        'user_id',
        'customer_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'status',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function businessSession(): BelongsTo
    {
        return $this->belongsTo(BusinessSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "ORD-{$date}-";
        $last = static::where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('order_number')->value('order_number');
        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
