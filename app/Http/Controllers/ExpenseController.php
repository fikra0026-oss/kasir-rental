<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::orderByDesc('date')->get();

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('expenses.form', ['expense' => new Expense()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:listrik,internet,gaji,servis_ps,pembelian_snack,pembelian_minuman,lain_lain',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }
}
