<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.form', ['product' => new Product()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil disimpan.');
    }

    public function edit(Product $product)
    {
        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
