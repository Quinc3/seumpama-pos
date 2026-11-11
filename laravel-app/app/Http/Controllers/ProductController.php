<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->search, fn($q, $search) => $q->where(
                fn($w) => $w
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
            ))
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->when($request->has('active'), fn($q) => $q->where('is_active', $request->boolean('active')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', "Produk \"{$product->name}\" berhasil ditambahkan.");
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)
            ->orWhere('id', $product->category_id)
            ->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        if ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $data['image_path'] = null;
        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', "Produk \"{$product->name}\" berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "Produk \"{$name}\" berhasil dihapus (soft delete).");
    }

    protected function validateProduct(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_available'] = $request->boolean('is_available');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
