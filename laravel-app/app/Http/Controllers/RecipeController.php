<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'recipe.items'])
            ->when($request->search, fn($q, $s) => $q->where(
                fn($w) => $w
                    ->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
            ))
            ->when($request->status === 'with', fn($q) => $q->whereHas('recipe'))
            ->when($request->status === 'without', fn($q) => $q->whereDoesntHave('recipe'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('recipes.index', compact('products'));
    }

    public function create(Product $product)
    {
        if ($product->recipe()->exists()) {
            return redirect()
                ->route('recipes.show', $product->recipe)
                ->with('error', 'Produk ini sudah memiliki resep aktif. Gunakan fitur Edit untuk membuat versi baru.');
        }

        $ingredients = Ingredient::where('is_active', true)->with('unit')->orderBy('name')->get();

        return view('recipes.create', compact('product', 'ingredients'));
    }

    public function store(Request $request, Product $product)
    {
        if ($product->recipe()->exists()) {
            return back()->with('error', 'Produk ini sudah memiliki resep aktif.');
        }

        $data = $this->validateRecipe($request);

        DB::transaction(function () use ($data, $product) {
            $recipe = Recipe::create([
                'product_id' => $product->id,
                'version' => 1,
                'name' => $data['name'],
                'effective_from' => now(),
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            $this->syncItems($recipe, $data['items']);
        });

        return redirect()
            ->route('recipes.index')
            ->with('success', "Resep untuk \"{$product->name}\" berhasil dibuat (v1).");
    }

    public function show(Recipe $recipe)
    {
        $recipe->load(['product.category', 'items.ingredient.unit', 'items.unit', 'createdBy']);

        $versions = Recipe::where('product_id', $recipe->product_id)
            ->orderByDesc('version')
            ->get();

        return view('recipes.show', compact('recipe', 'versions'));
    }

    public function edit(Recipe $recipe)
    {
        if (! $recipe->is_active) {
            return redirect()
                ->route('recipes.show', $recipe)
                ->with('error', 'Hanya resep aktif yang dapat diedit. Versi arsip bersifat read-only.');
        }

        $recipe->load('items.ingredient.unit', 'product');
        $ingredients = Ingredient::where('is_active', true)->with('unit')->orderBy('name')->get();

        return view('recipes.edit', compact('recipe', 'ingredients'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        if (! $recipe->is_active) {
            return back()->with('error', 'Hanya resep aktif yang dapat diupdate.');
        }

        $data = $this->validateRecipe($request);

        $newRecipe = DB::transaction(function () use ($data, $recipe) {
            // Arsipkan versi lama
            $recipe->update([
                'is_active' => false,
                'effective_until' => now(),
            ]);

            // Buat versi baru
            $newRecipe = Recipe::create([
                'product_id' => $recipe->product_id,
                'version' => Recipe::nextVersion($recipe->product_id),
                'name' => $data['name'],
                'effective_from' => now(),
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            $this->syncItems($newRecipe, $data['items']);

            return $newRecipe;
        });

        return redirect()
            ->route('recipes.show', $newRecipe)
            ->with('success', "Resep versi baru (v{$newRecipe->version}) berhasil dibuat. Versi sebelumnya diarsipkan.");
    }

    public function destroy(Recipe $recipe)
    {
        $productName = $recipe->product?->name ?? 'produk';
        $version = $recipe->version;
        $recipe->delete();

        return redirect()
            ->route('recipes.index')
            ->with('success', "Resep v{$version} untuk \"{$productName}\" berhasil dihapus.");
    }

    protected function validateRecipe(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        // Validasi duplikat ingredient
        $ingredientIds = collect($data['items'])->pluck('ingredient_id');
        if ($ingredientIds->count() !== $ingredientIds->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'Tidak boleh ada bahan yang sama dalam satu resep.',
            ]);
        }

        return $data;
    }

    protected function syncItems(Recipe $recipe, array $items): void
    {
        foreach ($items as $item) {
            $ing = Ingredient::findOrFail($item['ingredient_id']);
            RecipeItem::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ing->id,
                'quantity' => $item['quantity'],
                'unit_id' => $ing->unit_id,
            ]);
        }
    }
}
