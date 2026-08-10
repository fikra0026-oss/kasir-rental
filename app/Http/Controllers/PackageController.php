<?php

namespace App\Http\Controllers;

use App\Models\RentalPackage;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = RentalPackage::orderBy('duration_minutes')->get();

        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.form', ['package' => new RentalPackage()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_member' => 'nullable|boolean',
            'is_weekend' => 'nullable|boolean',
            'is_holiday' => 'nullable|boolean',
        ]);

        $data['is_member'] = $request->boolean('is_member');
        $data['is_weekend'] = $request->boolean('is_weekend');
        $data['is_holiday'] = $request->boolean('is_holiday');

        RentalPackage::create($data);

        return redirect()->route('packages.index')->with('success', 'Paket rental berhasil disimpan.');
    }

    public function edit(RentalPackage $package)
    {
        return view('packages.form', compact('package'));
    }

    public function update(Request $request, RentalPackage $package)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_member' => 'nullable|boolean',
            'is_weekend' => 'nullable|boolean',
            'is_holiday' => 'nullable|boolean',
        ]);

        $data['is_member'] = $request->boolean('is_member');
        $data['is_weekend'] = $request->boolean('is_weekend');
        $data['is_holiday'] = $request->boolean('is_holiday');

        $package->update($data);

        return redirect()->route('packages.index')->with('success', 'Paket rental berhasil diperbarui.');
    }

    public function destroy(RentalPackage $package)
    {
        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Paket rental berhasil dihapus.');
    }
}
