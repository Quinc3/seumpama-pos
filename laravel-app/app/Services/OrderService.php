<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SaleIngredientUsage;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class OrderService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Process checkout dari cart.
     *
     * @param Cart $cart
     * @param int $paymentMethodId
     * @param float $amountPaid
     * @param string|null $paymentReference
     * @param string|null $orderNotes
     * @return Order
     */
    public function checkout(
        Cart $cart,
        int $paymentMethodId,
        float $amountPaid,
        ?string $paymentReference = null,
        ?string $orderNotes = null
    ): Order {
        $cart->load('items.product.recipe.items.ingredient.unit');

        if ($cart->items->isEmpty()) {
            throw new InvalidArgumentException('Cart kosong.');
        }

        $paymentMethod = PaymentMethod::where('is_active', true)->find($paymentMethodId);
        if (! $paymentMethod) {
            throw new InvalidArgumentException('Metode pembayaran tidak valid atau tidak aktif.');
        }

        // Hitung subtotal (fresh dari DB)
        $subtotal = 0.0;
        $preparedItems = [];

        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;
            if (! $product) {
                throw new InvalidArgumentException("Produk tidak ditemukan (cart item #{$cartItem->id}).");
            }
            if (! $product->is_active || ! $product->is_available) {
                throw new InvalidArgumentException("Produk \"{$product->name}\" tidak tersedia.");
            }

            $product->loadMissing('recipe.items.ingredient.unit');
            $recipe = $product->recipe;
            if (! $recipe) {
                throw new InvalidArgumentException("Produk \"{$product->name}\" tidak memiliki resep aktif.");
            }
            if ($recipe->items->isEmpty()) {
                throw new InvalidArgumentException("Resep produk \"{$product->name}\" tidak memiliki bahan.");
            }

            $qty = (float) $cartItem->quantity;
            $unitPrice = (float) $product->selling_price;
            $lineSubtotal = $qty * $unitPrice;
            $subtotal += $lineSubtotal;

            $preparedItems[] = [
                'cart_item' => $cartItem,
                'product' => $product,
                'recipe' => $recipe,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $lineSubtotal,
            ];
        }

        // Validasi pembayaran
        $total = $subtotal; // No discount/tax in v1
        if ($amountPaid < $total - 0.01) {
            throw new InvalidArgumentException(
                "Pembayaran kurang. Total: Rp " . number_format($total, 0, ',', '.') .
                    ", dibayar: Rp " . number_format($amountPaid, 0, ',', '.')
            );
        }

        // Non-cash harus exact (no overpay)
        if (! in_array($paymentMethod->code, ['cash'])) {
            if (abs($amountPaid - $total) > 0.01) {
                throw new InvalidArgumentException(
                    "Metode {$paymentMethod->name} harus dibayar tepat Rp " . number_format($total, 0, ',', '.')
                );
            }
        }

        // Transaction
        return DB::transaction(function () use (
            $cart,
            $preparedItems,
            $subtotal,
            $total,
            $paymentMethod,
            $amountPaid,
            $paymentReference,
            $orderNotes
        ) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'business_session_id' => $cart->business_session_id,
                'user_id' => auth()->id(),
                'customer_id' => null,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $total,
                'status' => 'paid',
                'notes' => $orderNotes,
                'paid_at' => now(),
            ]);

            foreach ($preparedItems as $p) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p['product']->id,
                    'recipe_id' => $p['recipe']->id,
                    'product_name' => $p['product']->name,
                    'quantity' => $p['qty'],
                    'unit_price' => $p['unit_price'],
                    'discount_amount' => 0,
                    'subtotal' => $p['subtotal'],
                ]);

                // Snapshot ingredient usage + stock deduction
                foreach ($p['recipe']->items as $recipeItem) {
                    $ingredient = $recipeItem->ingredient;
                    if (! $ingredient) {
                        throw new RuntimeException("Ingredient resep tidak ditemukan untuk {$p['product']->name}.");
                    }

                    $qtyPerUnit = (float) $recipeItem->quantity;
                    $totalUsage = $qtyPerUnit * $p['qty'];

                    // Snapshot (dataset ANN)
                    SaleIngredientUsage::create([
                        'order_item_id' => $orderItem->id,
                        'ingredient_id' => $ingredient->id,
                        'recipe_id' => $p['recipe']->id,
                        'quantity_per_unit' => $qtyPerUnit,
                        'sold_quantity' => $p['qty'],
                        'total_quantity' => $totalUsage,
                        'unit_id' => $recipeItem->unit_id,
                    ]);

                    // Stock deduction via StockService (atomic + lock)
                    $this->stockService->record(
                        $ingredient,
                        'sale_out',
                        $totalUsage,
                        [
                            'notes' => "Penjualan order {$order->order_number} ({$p['product']->name} × {$p['qty']})",
                            'reference_type' => 'order',
                            'reference_id' => $order->id,
                            'business_session_id' => $cart->business_session_id,
                        ]
                    );
                }
            }

            // Payment
            OrderPayment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $amountPaid,
                'reference_number' => $paymentReference,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // Mark cart converted
            $cart->update(['status' => 'converted']);

            return $order;
        });
    }
    /**
     * Process refund untuk order yang sudah paid.
     * - Reversal stock via return_in movements (per sale_ingredient_usage)
     * - Update order status → refunded
     * - Update payment status → refunded
     *
     * @throws InvalidArgumentException|RuntimeException
     */
    public function refund(Order $order, string $reason): Order
    {
        if ($order->status !== 'paid') {
            throw new InvalidArgumentException('Hanya order berstatus paid yang dapat di-refund.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('Alasan refund wajib diisi.');
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->load('items.usages.ingredient', 'payments');

            $userName = auth()->user()->name ?? 'system';
            $timestamp = now()->format('Y-m-d H:i');

            foreach ($order->items as $orderItem) {
                foreach ($orderItem->usages as $usage) {
                    // Reversal: return_in dengan qty yang sama dengan total_quantity
                    $this->stockService->record(
                        $usage->ingredient,
                        'return_in',
                        (float) $usage->total_quantity,
                        [
                            'notes' => "Refund order {$order->order_number} ({$orderItem->product_name}) — {$reason}",
                            'reference_type' => 'refund',
                            'reference_id' => $order->id,
                            'business_session_id' => $order->business_session_id,
                        ]
                    );
                }
            }

            // Update status payment
            foreach ($order->payments as $payment) {
                $payment->update(['status' => 'refunded']);
            }

            // Update order
            $appendedNotes = trim(($order->notes ?? '') . "\n[Refunded {$timestamp} by {$userName}] {$reason}");

            $order->update([
                'status' => 'refunded',
                'notes' => $appendedNotes,
            ]);

            return $order->fresh();
        });
    }
}
