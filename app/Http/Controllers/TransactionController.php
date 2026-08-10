<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\PlaystationUnit;
use App\Models\Product;
use App\Models\RentalPackage;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['unit', 'member', 'items.product'])->orderByDesc('created_at')->get();

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $units = PlaystationUnit::whereIn('status', ['kosong', 'booking'])->orderBy('name')->get();
        $packages = RentalPackage::orderBy('duration_minutes')->get();
        $members = Member::orderBy('name')->get();
        $products = Product::where('active', true)->where('stock', '>', 0)->orderBy('name')->get();

        return view('transactions.create', compact('units', 'packages', 'members', 'products'));
    }

    public function foodDrinksPage()
    {
        $units = PlaystationUnit::whereIn('status', ['kosong', 'booking'])->orderBy('name')->get();
        $products = Product::where('active', true)->where('stock', '>', 0)->orderBy('name')->get();

        return view('transactions.food-drinks', compact('units', 'products'));
    }

    public function storeFoodDrinks(Request $request)
    {
        $data = $request->validate([
            'playstation_unit_id' => 'nullable|exists:playstation_units,id',
            'member_id' => 'nullable|exists:members,id',
            'customer_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,qris,transfer,e-wallet',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:0',
        ]);

        $unit = !empty($data['playstation_unit_id'] ?? null) ? PlaystationUnit::find($data['playstation_unit_id']) : null;
        $member = !empty($data['member_id'] ?? null) ? Member::find($data['member_id']) : null;
        $productTotal = 0;
        $items = [];

        foreach ($data['products'] ?? [] as $productData) {
            if (empty($productData['product_id']) || !isset($productData['quantity']) || (int) $productData['quantity'] < 1) {
                continue;
            }

            $product = Product::find($productData['product_id']);
            if (! $product || $product->stock <= 0) {
                continue;
            }

            $quantity = min($product->stock, $productData['quantity']);
            $lineTotal = $product->price * $quantity;
            $productTotal += $lineTotal;
            $type = strtolower($product->category) === 'minuman' ? 'drink' : 'food';

            $items[] = [
                'product_id' => $product->id,
                'type' => $type,
                'description' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total_price' => $lineTotal,
            ];

            $product->decrement('stock', $quantity);
        }

        if (empty($items)) {
            return redirect()->back()->withErrors(['products' => 'Pilih minimal satu makanan atau minuman yang tersedia.']);
        }

        DB::transaction(function () use ($unit, $member, $data, $productTotal, $items) {
            $transaction = Transaction::create([
                'transaction_number' => 'TRX'.Str::upper(Str::random(6)).now()->format('YmdHis'),
                'playstation_unit_id' => $unit?->id,
                'member_id' => $member?->id,
                'customer_name' => $data['customer_name'] ?? 'Umum',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'subtotal' => $productTotal,
                'discount' => 0,
                'total' => $productTotal,
                'status' => 'berjalan',
                'payment_status' => 'belum_bayar',
                'started_at' => now(),
                'duration_minutes' => 0,
            ]);

            foreach ($items as $item) {
                $transaction->items()->create($item);
            }

            if ($unit) {
                $unit->update(['status' => 'digunakan']);
            }

            if ($member) {
                $member->increment('points', floor($productTotal / 10000));
                $member->increment('total_transactions');
            }
        });

        return redirect()->route('transactions.index')->with('success', 'Pesanan makanan & minuman berhasil disimpan.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'playstation_unit_id' => 'required|exists:playstation_units,id',
            'package_id' => 'nullable|exists:rental_packages,id',
            'manual_minutes' => 'nullable|integer|min:1',
            'member_id' => 'nullable|exists:members,id',
            'customer_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,qris,transfer,e-wallet',
            'products' => 'array',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:0',
        ]);

        $unit = PlaystationUnit::findOrFail($data['playstation_unit_id']);
        $package = !empty($data['package_id'] ?? null) ? RentalPackage::find($data['package_id']) : null;
        $member = !empty($data['member_id'] ?? null) ? Member::find($data['member_id']) : null;

        $duration = $package ? $package->duration_minutes : ($data['manual_minutes'] ?? 60);
        $rentalSubtotal = $package ? $package->price : round(($unit->hourly_rate / 60) * $duration, 2);

        $productTotal = 0;
        $items = [];

        foreach ($data['products'] ?? [] as $productData) {
            if (empty($productData['product_id']) || !isset($productData['quantity']) || (int) $productData['quantity'] < 1) {
                continue;
            }
            $product = Product::find($productData['product_id']);
            if (! $product || $product->stock <= 0) {
                continue;
            }
            $quantity = min($product->stock, (int) $productData['quantity']);
            $lineTotal = $product->price * $quantity;
            $productTotal += $lineTotal;
            $type = strtolower($product->category) === 'minuman' ? 'drink' : 'food';

            $items[] = [
                'product_id' => $product->id,
                'type' => $type,
                'description' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total_price' => $lineTotal,
            ];

            $product->decrement('stock', $quantity);
        }

        DB::transaction(function () use ($unit, $package, $member, $data, $duration, $rentalSubtotal, $productTotal, $items) {
            $transaction = Transaction::create([
                'transaction_number' => 'TRX'.Str::upper(Str::random(6)).now()->format('YmdHis'),
                'playstation_unit_id' => $unit->id,
                'member_id' => $member?->id,
                'customer_name' => $data['customer_name'] ?? 'Umum',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'subtotal' => $rentalSubtotal + $productTotal,
                'discount' => 0,
                'total' => $rentalSubtotal + $productTotal,
                'status' => 'berjalan',
                'payment_status' => 'belum_bayar',
                'started_at' => now(),
                'duration_minutes' => $duration,
            ]);

            $items[] = [
                'product_id' => null,
                'type' => 'rental',
                'description' => $package ? $package->name : "Manual {$duration} menit",
                'quantity' => 1,
                'unit_price' => $rentalSubtotal,
                'total_price' => $rentalSubtotal,
            ];

            foreach ($items as $item) {
                $transaction->items()->create($item);
            }

            $unit->update(['status' => 'digunakan']);

            if ($member) {
                $member->increment('points', floor(($rentalSubtotal + $productTotal) / 10000));
                $member->increment('total_transactions');
            }
        });

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil disimpan dan unit diaktifkan.');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'package_id' => 'nullable|exists:rental_packages,id',
        ]);

        if ($transaction->status !== 'berjalan') {
            return redirect()->route('transactions.index')->withErrors([
                'package_id' => 'Hanya transaksi yang masih berjalan yang bisa diperbarui paketnya.',
            ]);
        }

        $package = !empty($data['package_id'] ?? null) ? RentalPackage::find($data['package_id']) : null;
        $productTotal = $transaction->items()->whereIn('type', ['food', 'drink'])->sum('total_price');
        $rentalSubtotal = $package ? $package->price : 0;
        $newTotal = $rentalSubtotal + $productTotal;

        $transaction->update([
            'package_id' => $package?->id,
            'duration_minutes' => $package ? $package->duration_minutes : $transaction->duration_minutes,
            'subtotal' => $newTotal,
            'total' => $newTotal,
        ]);

        $transaction->items()->where('type', 'rental')->update([
            'description' => $package ? $package->name : ($transaction->items()->where('type', 'rental')->first()?->description ?? 'Paket PS'),
            'unit_price' => $rentalSubtotal,
            'total_price' => $rentalSubtotal,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Paket transaksi berhasil diperbarui.');
    }

    public function finish(Transaction $transaction)
    {
        $transaction->update([
            'status' => 'selesai',
            'ended_at' => now(),
        ]);

        if ($transaction->playstation_unit_id) {
            $unit = PlaystationUnit::find($transaction->playstation_unit_id);
            if ($unit) {
                $unit->update(['status' => 'kosong']);
            }
        }

        return redirect()->route('transactions.index')->with('success', 'Transaksi selesai dan unit dibebaskan.');
    }

    public function completePayment(Transaction $transaction)
    {
        $transaction->update([
            'payment_status' => 'lunas',
        ]);

        return redirect()->route('transactions.index')->with('success', 'Pembayaran transaksi berhasil diselesaikan.');
    }

    public function destroy(Transaction $transaction)
    {
        if (! $transaction->isDeletable()) {
            return redirect()->route('transactions.index')->with('error', 'Transaksi tidak dapat dihapus karena sudah berumur lebih dari 1 hari.');
        }

        DB::transaction(function () use ($transaction) {
            if ($transaction->status === 'berjalan' && $transaction->playstation_unit_id) {
                $unit = PlaystationUnit::find($transaction->playstation_unit_id);
                if ($unit) {
                    $unit->update(['status' => 'kosong']);
                }
            }

            $transaction->items()->delete();
            $transaction->delete();
        });

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus.');
    }
}
