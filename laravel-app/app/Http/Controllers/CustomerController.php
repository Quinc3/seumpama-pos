<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::when($request->search, fn($q, $s) => $q->where(
            fn($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
        ))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCustomer($request);
        $customer = Customer::create($data);

        return redirect()->route('customers.index')
            ->with('success', "Customer \"{$customer->name}\" berhasil ditambahkan.");
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $this->validateCustomer($request);
        $customer->update($data);

        return redirect()->route('customers.index')
            ->with('success', "Customer \"{$customer->name}\" berhasil diperbarui.");
    }

    public function destroy(Customer $customer)
    {
        if ($customer->isUsed()) {
            return back()->with('error', 'Customer tidak dapat dihapus karena memiliki riwayat transaksi.');
        }

        $name = $customer->name;
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Customer \"{$name}\" berhasil dihapus.");
    }

    protected function validateCustomer(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
