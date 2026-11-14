<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Services\BusinessDateResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with(['category', 'createdBy', 'paymentMethod'])
            ->when($request->category_id, fn($q, $id) => $q->where('expense_category_id', $id))
            ->when($request->from, fn($q, $d) => $q->whereDate('expense_date', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('expense_date', '<=', $d))
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        $summary = Expense::query()
            ->when($request->category_id, fn($q, $id) => $q->where('expense_category_id', $id))
            ->when($request->from, fn($q, $d) => $q->whereDate('expense_date', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('expense_date', '<=', $d))
            ->sum('amount');

        return view('expenses.index', compact('expenses', 'categories', 'summary'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('expenses.create', compact('categories', 'paymentMethods'));
    }

    public function store(Request $request, BusinessDateResolver $resolver)
    {
        $data = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
        ]);

        $session = $resolver->getOrOpenSession(\Carbon\Carbon::parse($data['expense_date']));

        Expense::create($data + [
            'business_session_id' => $session->id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Expense $expense)
    {
        if ($expense->created_by !== auth()->id() && ! auth()->user()->hasRole('Owner')) {
            return back()->with('error', 'Anda tidak dapat mengedit pengeluaran yang dibuat oleh user lain.');
        }

        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->created_by !== auth()->id() && ! auth()->user()->hasRole('Owner')) {
            return back()->with('error', 'Anda tidak dapat mengedit pengeluaran ini.');
        }

        $data = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
        ]);

        $expense->update($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->created_by !== auth()->id() && ! auth()->user()->hasRole('Owner')) {
            return back()->with('error', 'Anda tidak dapat menghapus pengeluaran ini.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
