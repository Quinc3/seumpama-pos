<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::withCount('products')
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true)
                ->where('start_at', '<=', now())->where('end_at', '>=', now()))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($request->status === 'expired', fn($q) => $q->where('end_at', '<', now()))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('promotions.index', compact('promotions'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('promotions.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePromotion($request);

        $promotion = Promotion::create($data);

        if (! empty($data['product_ids'])) {
            $promotion->products()->sync($data['product_ids']);
        }

        return redirect()
            ->route('promotions.index')
            ->with('success', "Promosi \"{$promotion->name}\" berhasil ditambahkan.");
    }

    public function edit(Promotion $promotion)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $selectedProducts = $promotion->products()->pluck('products.id')->toArray();

        return view('promotions.edit', compact('promotion', 'products', 'selectedProducts'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validatePromotion($request);

        $promotion->update($data);
        $promotion->products()->sync($data['product_ids'] ?? []);

        return redirect()
            ->route('promotions.index')
            ->with('success', "Promosi \"{$promotion->name}\" berhasil diperbarui.");
    }

    public function destroy(Promotion $promotion)
    {
        $name = $promotion->name;
        $promotion->delete();

        return redirect()
            ->route('promotions.index')
            ->with('success', "Promosi \"{$name}\" berhasil dihapus.");
    }

    protected function validatePromotion(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:100'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'minimum_purchase' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['minimum_purchase'] = $data['minimum_purchase'] ?? null;
        $data['maximum_discount'] = $data['maximum_discount'] ?? null;

        // Percentage harus <= 100
        if ($data['discount_type'] === 'percentage' && (float) $data['discount_value'] > 100) {
            abort(back()->withErrors(['discount_value' => 'Percentage maksimal 100%.'])->withInput());
        }

        return $data;
    }
}
