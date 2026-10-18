<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\PlaystationUnit;
use App\Models\Product;
use App\Models\RentalPackage;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['unit', 'member', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate(15);
        $totalTransactions = Transaction::count();
        $totalOmzet = Transaction::sum('total');
        $packages = RentalPackage::orderBy('duration_minutes')->get();
        $availableProducts = Product::where('active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('transactions.index', compact(
            'transactions',
            'totalTransactions',
            'totalOmzet',
            'packages',
            'availableProducts',
        ));
    }

    public function create()
    {
        $units = PlaystationUnit::orderByRaw("CASE WHEN status IN ('kosong', 'booking') THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
        $packages = RentalPackage::orderBy('duration_minutes')->get();
        $members = Member::orderBy('name')->get();
        $products = Product::where('active', true)->where('stock', '>', 0)->orderBy('name')->get();

        return view('transactions.create', compact('units', 'packages', 'members', 'products'));
    }

    public function foodDrinksPage()
    {
        $units = PlaystationUnit::orderByRaw("CASE WHEN status IN ('kosong', 'booking') THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
        $products = Product::where('active', true)->where('stock', '>', 0)->orderBy('name')->get();
        $members = Member::orderBy('name')->get();

        return view('transactions.food-drinks', compact('units', 'products', 'members'));
    }

    public function storeFoodDrinks(Request $request)
    {
        $data = $request->validate([
            'playstation_unit_id' => 'nullable|exists:playstation_units,id',
            'member_id' => 'nullable|exists:members,id',
            'customer_name' => 'nullable|string|max:255',
            'occurred_at' => 'required|date|before_or_equal:now',
            'payment_method' => 'required|in:cash,qris,transfer,e-wallet',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:0',
        ]);

        $unit = !empty($data['playstation_unit_id'] ?? null) ? PlaystationUnit::find($data['playstation_unit_id']) : null;
        $occurredAt = Carbon::parse($data['occurred_at'], config('app.timezone'));
        $isHistorical = $occurredAt->isBefore(now()->startOfDay());
        $startedAt = $isHistorical ? $occurredAt : now();

        if ($unit && ! $isHistorical && ! in_array($unit->status, ['kosong', 'booking'], true)) {
            return redirect()->back()->withInput()->withErrors([
                'playstation_unit_id' => 'Unit tersebut sedang tidak tersedia.',
            ]);
        }

        $member = !empty($data['member_id'] ?? null) ? Member::find($data['member_id']) : null;
        $productTotal = 0;
        $items = [];
        $stockAdjustments = [];
        $historicalProductQuantities = [];

        foreach ($data['products'] ?? [] as $productData) {
            if (empty($productData['product_id']) || !isset($productData['quantity']) || (int) $productData['quantity'] < 1) {
                continue;
            }

            $product = Product::find($productData['product_id']);
            if (! $product || $product->stock <= 0) {
                if ($isHistorical && (int) ($productData['quantity'] ?? 0) > 0) {
                    return redirect()->back()->withInput()->withErrors([
                        'products' => 'Stok produk saat ini tidak cukup untuk mencatat jumlah transaksi lampau.',
                    ]);
                }

                continue;
            }

            $requestedQuantity = (int) $productData['quantity'];
            $totalRequestedQuantity = ($historicalProductQuantities[$product->id] ?? 0) + $requestedQuantity;
            if ($isHistorical && $totalRequestedQuantity > $product->stock) {
                return redirect()->back()->withInput()->withErrors([
                    'products' => "Stok {$product->name} saat ini tidak cukup untuk mencatat jumlah tersebut.",
                ]);
            }
            $historicalProductQuantities[$product->id] = $totalRequestedQuantity;

            $quantity = $isHistorical ? $requestedQuantity : min($product->stock, $requestedQuantity);
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

            $stockAdjustments[] = ['product_id' => $product->id, 'quantity' => $quantity];
        }

        if (empty($items)) {
            return redirect()->back()->withErrors(['products' => 'Pilih minimal satu makanan atau minuman yang tersedia.']);
        }

        DB::transaction(function () use ($unit, $member, $data, $occurredAt, $startedAt, $isHistorical, $productTotal, $items, $stockAdjustments) {
            foreach ($stockAdjustments as $adjustment) {
                Product::whereKey($adjustment['product_id'])->decrement('stock', $adjustment['quantity']);
            }

            $transaction = Transaction::create([
                'transaction_number' => 'TRX'.Str::upper(Str::random(6)).now()->format('YmdHis'),
                'playstation_unit_id' => $unit?->id,
                'member_id' => $member?->id,
                'customer_name' => $data['customer_name'] ?? 'Umum',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'subtotal' => $productTotal,
                'discount' => 0,
                'total' => $productTotal,
                'status' => $isHistorical ? 'selesai' : 'berjalan',
                'payment_status' => $isHistorical ? 'lunas' : 'belum_bayar',
                'started_at' => $startedAt,
                'ended_at' => $isHistorical ? $occurredAt : null,
                'duration_minutes' => 0,
            ]);
            if ($isHistorical) {
                $transaction->created_at = $occurredAt;
                $transaction->save();
            }

            foreach ($items as $item) {
                $transaction->items()->create($item);
            }

            if ($unit && ! $isHistorical) {
                $unit->update(['status' => 'digunakan']);
            }

            if ($member) {
                $member->increment('points', floor($productTotal / 10000));
                $member->increment('total_transactions');
            }
        });

        return redirect()->route('transactions.index')->with('success', 'Pesanan makanan & minuman berhasil disimpan.');
    }

    public function storeFoodDrinksForTransaction(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'berjalan') {
            return redirect()->route('transactions.index')->withErrors([
                'products' => 'Makanan dan minuman hanya dapat ditambahkan ke transaksi yang masih berjalan.',
            ]);
        }

        $data = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($transaction, $data) {
            $lockedTransaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($lockedTransaction->status !== 'berjalan') {
                throw ValidationException::withMessages([
                    'products' => 'Makanan dan minuman hanya dapat ditambahkan ke transaksi yang masih berjalan.',
                ]);
            }

            $addedTotal = 0;
            $addedItems = 0;

            foreach ($data['products'] as $productData) {
                if (empty($productData['product_id']) || (int) ($productData['quantity'] ?? 0) < 1) {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->find($productData['product_id']);
                $quantity = (int) $productData['quantity'];
                if (! $product || ! $product->active || $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'products' => 'Produk tidak aktif atau stoknya tidak cukup untuk memenuhi jumlah yang diminta.',
                    ]);
                }

                $lineTotal = $product->price * $quantity;
                $product->decrement('stock', $quantity);
                $lockedTransaction->items()->create([
                    'product_id' => $product->id,
                    'type' => strtolower($product->category) === 'minuman' ? 'drink' : 'food',
                    'description' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'total_price' => $lineTotal,
                ]);

                $addedTotal += $lineTotal;
                $addedItems++;
            }

            if ($addedItems === 0) {
                throw ValidationException::withMessages([
                    'products' => 'Pilih minimal satu makanan atau minuman.',
                ]);
            }

            $lockedTransaction->update([
                'subtotal' => $lockedTransaction->subtotal + $addedTotal,
                'total' => $lockedTransaction->total + $addedTotal,
            ]);

            if ($lockedTransaction->member_id) {
                Member::whereKey($lockedTransaction->member_id)->increment('points', floor($addedTotal / 10000));
            }
        });

        return redirect()->route('transactions.index')->with('success', 'Pesanan berhasil ditambahkan ke transaksi.');
    }

    public function destroyFoodDrinkItem(Transaction $transaction, TransactionItem $item)
    {
        abort_unless(
            $item->transaction_id === $transaction->id && in_array($item->type, ['food', 'drink'], true),
            404,
        );

        if (! $transaction->isDeletable()) {
            return redirect()->route('transactions.index')->with('error', 'Item pesanan tidak dapat dihapus karena transaksi sudah berumur lebih dari 1 hari.');
        }

        DB::transaction(function () use ($transaction, $item) {
            $lockedTransaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $lockedItem = $lockedTransaction->items()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedItem->type, ['food', 'drink'], true)) {
                abort(404);
            }

            if ($lockedItem->product_id) {
                Product::whereKey($lockedItem->product_id)->increment('stock', $lockedItem->quantity);
            }

            $itemTotal = (float) $lockedItem->total_price;
            $lockedItem->delete();
            $lockedTransaction->update([
                'subtotal' => max(0, (float) $lockedTransaction->subtotal - $itemTotal),
                'total' => max(0, (float) $lockedTransaction->total - $itemTotal),
            ]);

            if ($lockedTransaction->member_id) {
                $pointsToRemove = (int) floor($itemTotal / 10000);
                if ($pointsToRemove > 0) {
                    $member = Member::whereKey($lockedTransaction->member_id)->lockForUpdate()->first();
                    if ($member) {
                        $member->decrement('points', min($member->points, $pointsToRemove));
                    }
                }
            }
        });

        return redirect()->route('transactions.index')->with('success', 'Item pesanan berhasil dihapus.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'playstation_unit_id' => 'required|exists:playstation_units,id',
            'package_id' => 'nullable|exists:rental_packages,id',
            'manual_minutes' => 'nullable|integer|min:1',
            'member_id' => 'nullable|exists:members,id',
            'customer_name' => 'nullable|string|max:255',
            'occurred_at' => 'required|date|before_or_equal:now',
            'payment_method' => 'required|in:cash,qris,transfer,e-wallet',
            'products' => 'array',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:0',
        ]);

        $unit = PlaystationUnit::findOrFail($data['playstation_unit_id']);
        $occurredAt = Carbon::parse($data['occurred_at'], config('app.timezone'));
        $isHistorical = $occurredAt->isBefore(now()->startOfDay());
        $startedAt = $isHistorical ? $occurredAt : now();

        if (! $isHistorical && ! in_array($unit->status, ['kosong', 'booking'], true)) {
            return redirect()->back()->withInput()->withErrors([
                'playstation_unit_id' => 'Unit tersebut sedang tidak tersedia.',
            ]);
        }

        $package = !empty($data['package_id'] ?? null) ? RentalPackage::find($data['package_id']) : null;
        $member = !empty($data['member_id'] ?? null) ? Member::find($data['member_id']) : null;

        $duration = $package ? $package->duration_minutes : ($data['manual_minutes'] ?? 60);
        $rentalSubtotal = $package ? $package->price : round(($unit->hourly_rate / 60) * $duration, 2);

        $productTotal = 0;
        $items = [];
        $stockAdjustments = [];
        $historicalProductQuantities = [];

        foreach ($data['products'] ?? [] as $productData) {
            if (empty($productData['product_id']) || !isset($productData['quantity']) || (int) $productData['quantity'] < 1) {
                continue;
            }
            $product = Product::find($productData['product_id']);
            if (! $product || $product->stock <= 0) {
                if ($isHistorical && (int) ($productData['quantity'] ?? 0) > 0) {
                    return redirect()->back()->withInput()->withErrors([
                        'products' => 'Stok produk saat ini tidak cukup untuk mencatat jumlah transaksi lampau.',
                    ]);
                }

                continue;
            }
            $requestedQuantity = (int) $productData['quantity'];
            $totalRequestedQuantity = ($historicalProductQuantities[$product->id] ?? 0) + $requestedQuantity;
            if ($isHistorical && $totalRequestedQuantity > $product->stock) {
                return redirect()->back()->withInput()->withErrors([
                    'products' => "Stok {$product->name} saat ini tidak cukup untuk mencatat jumlah tersebut.",
                ]);
            }
            $historicalProductQuantities[$product->id] = $totalRequestedQuantity;

            $quantity = $isHistorical ? $requestedQuantity : min($product->stock, $requestedQuantity);
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

            $stockAdjustments[] = ['product_id' => $product->id, 'quantity' => $quantity];
        }

        DB::transaction(function () use ($unit, $package, $member, $data, $occurredAt, $startedAt, $isHistorical, $duration, $rentalSubtotal, $productTotal, $items, $stockAdjustments) {
            foreach ($stockAdjustments as $adjustment) {
                Product::whereKey($adjustment['product_id'])->decrement('stock', $adjustment['quantity']);
            }

            $transaction = Transaction::create([
                'transaction_number' => 'TRX'.Str::upper(Str::random(6)).now()->format('YmdHis'),
                'playstation_unit_id' => $unit->id,
                'member_id' => $member?->id,
                'customer_name' => $data['customer_name'] ?? 'Umum',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'subtotal' => $rentalSubtotal + $productTotal,
                'discount' => 0,
                'total' => $rentalSubtotal + $productTotal,
                'status' => $isHistorical ? 'selesai' : 'berjalan',
                'payment_status' => $isHistorical ? 'lunas' : 'belum_bayar',
                'started_at' => $startedAt,
                'ended_at' => $isHistorical ? $occurredAt->copy()->addMinutes($duration) : null,
                'duration_minutes' => $duration,
            ]);
            if ($isHistorical) {
                $transaction->created_at = $occurredAt;
                $transaction->save();
            }

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

            if (! $isHistorical) {
                $unit->update(['status' => 'digunakan']);
            }

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
