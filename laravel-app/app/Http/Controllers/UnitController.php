<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('unit_type')->orderBy('name')->paginate(20);

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateUnit($request);
        Unit::create($data);

        return redirect()
            ->route('units.index')
            ->with('success', "Unit \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $this->validateUnit($request, $unit);
        $unit->update($data);

        return redirect()
            ->route('units.index')
            ->with('success', "Unit \"{$data['name']}\" berhasil diperbarui.");
    }

    public function destroy(Unit $unit)
    {
        if ($unit->isUsed()) {
            return back()->with('error', 'Unit tidak dapat dihapus karena masih digunakan oleh bahan baku.');
        }

        $name = $unit->name;
        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', "Unit \"{$name}\" berhasil dihapus.");
    }

    protected function validateUnit(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'symbol' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'symbol')->ignore($unit?->id),
            ],
            'unit_type' => ['required', Rule::in(['weight', 'volume', 'count'])],
            'is_active' => ['sometimes', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
