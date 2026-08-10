<?php

namespace App\Http\Controllers;

use App\Models\AdditionalFund;
use Illuminate\Http\Request;

class AdditionalFundController extends Controller
{
    public function index()
    {
        $additionalFunds = AdditionalFund::orderByDesc('date')->get();

        return view('additional-funds.index', compact('additionalFunds'));
    }

    public function create()
    {
        return view('additional-funds.form', ['additionalFund' => new AdditionalFund()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source' => 'required|in:modal_owner,pinjaman,donasi,lain_lain',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        AdditionalFund::create($data);

        return redirect()->route('additional-funds.index')->with('success', 'Dana tambahan berhasil dicatat.');
    }
}
