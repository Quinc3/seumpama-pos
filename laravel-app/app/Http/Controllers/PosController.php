<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\BusinessDateResolver;
use Illuminate\Http\Request;
use App\Services\OrderService;

class PosController extends Controller
{
    public function index(BusinessDateResolver $resolver)
    {
        $session = $resolver->getOrOpenSession();
        $cart = $this->resolveCart($session);
        $cart->load('items.product');

        $products = Product::with('category')
            ->where('is_active', true)
            ->where('is_available', true)
            ->whereHas('recipe', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $categories = $products->pluck('category')->filter()->unique('id')->sortBy('name')->values();

        $productsPayload = $products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'selling_price' => (float) $p->selling_price,
            'image_path' => $p->image_path,
            'image_url' => $p->image_path ? \Storage::url($p->image_path) : null,
            'category_id' => $p->category_id,
            'category_name' => $p->category?->name,
        ])->values()->toArray();

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'code' => $m->code])
            ->values()
            ->toArray();

        return view('pos.index', [
            'session' => $session,
            'cartData' => $cart->toCartArray(),
            'productsPayload' => $productsPayload,
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function addItem(Request $request, BusinessDateResolver $resolver)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.0001'],
        ]);

        $product = Product::with('recipe')->findOrFail($data['product_id']);

        if (! $product->is_active || ! $product->is_available) {
            return response()->json(['success' => false, 'message' => 'Produk tidak tersedia.'], 422);
        }
        if (! $product->recipe) {
            return response()->json(['success' => false, 'message' => 'Produk belum memiliki resep aktif.'], 422);
        }

        $session = $resolver->getOrOpenSession();
        $cart = $this->resolveCart($session);

        $qty = (float) ($data['quantity'] ?? 1);
        $item = CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->first();
        if ($item) {
            $item->increment('quantity', $qty);
        } else {
            CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => $qty]);
        }

        $cart = Cart::with('items.product')->findOrFail($cart->id);

        return response()->json(['success' => true, 'cart' => $cart->toCartArray()]);
    }

    public function updateItem(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $cartItem->update(['quantity' => $data['quantity']]);

        $cart = Cart::with('items.product')->findOrFail($cartItem->cart_id);

        return response()->json(['success' => true, 'cart' => $cart->toCartArray()]);
    }

    public function removeItem(CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $cartId = $cartItem->cart_id;
        $cartItem->delete();

        $cart = Cart::with('items.product')->findOrFail($cartId);

        return response()->json(['success' => true, 'cart' => $cart->toCartArray()]);
    }

    public function clear(BusinessDateResolver $resolver)
    {
        $session = $resolver->getOrOpenSession();
        $cart = $this->resolveCart($session);

        $cart->items()->delete();

        $cart = Cart::with('items.product')->findOrFail($cart->id);

        return response()->json(['success' => true, 'cart' => $cart->toCartArray()]);
    }

    public function checkout(Request $request, OrderService $orderService, BusinessDateResolver $resolver)
    {
        $session = $resolver->getOrOpenSession();
        $cart = Cart::where([
            'user_id' => auth()->id(),
            'business_session_id' => $session->id,
            'status' => 'active',
        ])->first();

        if (! $cart) {
            return response()->json(['success' => false, 'message' => 'Cart tidak ditemukan.'], 422);
        }

        $data = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $order = $orderService->checkout(
                $cart,
                (int) $data['payment_method_id'],
                (float) $data['amount_paid'],
                $data['payment_reference'] ?? null,
                $data['notes'] ?? null,
            );

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect_url' => route('orders.show', $order),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    protected function resolveCart($session): Cart
    {
        return Cart::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'business_session_id' => $session->id,
                'status' => 'active',
            ]
        );
    }

    protected function authorizeCartItem(CartItem $cartItem): void
    {
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403, 'Cart bukan milik Anda.');
        }
    }
}
