<?php

namespace App\Http\Controllers;

use App\Models\PlaystationUnit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = PlaystationUnit::orderBy('name')->get();

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.form', ['unit' => new PlaystationUnit()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'tv' => 'required|string|max:255',
            'status' => 'required|in:kosong,digunakan,booking,maintenance',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        PlaystationUnit::create($data);

        return redirect()->route('units.index')->with('success', 'Unit PS berhasil disimpan.');
    }

    public function edit(PlaystationUnit $unit)
    {
        return view('units.form', compact('unit'));
    }

    public function update(Request $request, PlaystationUnit $unit)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'tv' => 'required|string|max:255',
            'status' => 'required|in:kosong,digunakan,booking,maintenance',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        $unit->update($data);

        return redirect()->route('units.index')->with('success', 'Unit PS berhasil diperbarui.');
    }

    public function destroy(PlaystationUnit $unit)
    {
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit PS berhasil dihapus.');
    }
}
