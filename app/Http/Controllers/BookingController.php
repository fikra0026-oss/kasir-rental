<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PlaystationUnit;
use App\Models\RentalPackage;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with('unit', 'package')->orderBy('date')->orderBy('start_time')->get();

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $units = PlaystationUnit::where('status', 'kosong')->orWhere('status', 'booking')->orderBy('name')->get();
        $packages = RentalPackage::orderBy('duration_minutes')->get();

        return view('bookings.form', ['booking' => new Booking(), 'units' => $units, 'packages' => $packages]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:30',
            'playstation_unit_id' => 'required|exists:playstation_units,id',
            'package_id' => 'nullable|exists:rental_packages,id',
            'status' => 'required|in:pending,aktif,selesai,dibatalkan',
        ]);

        $booking = Booking::create($data);

        $this->syncUnitStatus($booking);

        if ($booking->status === 'aktif' && ! $booking->transactions()->exists()) {
            $this->createTransactionFromBooking($booking);
        }

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil disimpan.');
    }

    public function edit(Booking $booking)
    {
        $units = PlaystationUnit::orderBy('name')->get();
        $packages = RentalPackage::orderBy('duration_minutes')->get();

        return view('bookings.form', compact('booking', 'units', 'packages'));
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:30',
            'playstation_unit_id' => 'required|exists:playstation_units,id',
            'package_id' => 'nullable|exists:rental_packages,id',
            'status' => 'required|in:pending,aktif,selesai,dibatalkan',
        ]);

        $oldStatus = $booking->status;
        $newStatus = $data['status'];

        $booking->update($data);

        $this->syncUnitStatus($booking);

        if ($newStatus === 'aktif' && $oldStatus !== 'aktif' && ! $booking->transactions()->exists()) {
            $this->createTransactionFromBooking($booking);
        }

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil diperbarui.');
    }

    public function destroy(Booking $booking)
    {
        // Delete related transactions
        $booking->transactions()->delete();
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil dihapus.');
    }

    private function createTransactionFromBooking(Booking $booking)
    {
        if ($booking->transactions()->exists()) {
            return $booking->transactions()->latest()->first();
        }

        $unit = $booking->unit;
        $rentalPrice = round(($unit->hourly_rate / 60) * $booking->duration_minutes, 2);

        $transaction = Transaction::create([
            'transaction_number' => 'TRX' . Str::upper(Str::random(6)) . now()->format('YmdHis'),
            'playstation_unit_id' => $booking->playstation_unit_id,
            'booking_id' => $booking->id,
            'customer_name' => $booking->customer_name,
            'payment_method' => 'cash',
            'subtotal' => $rentalPrice,
            'discount' => 0,
            'total' => $rentalPrice,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => $booking->duration_minutes,
        ]);

        $transaction->items()->create([
            'product_id' => null,
            'type' => 'rental',
            'description' => "Booking: {$booking->date->format('d M Y')} {$booking->start_time}",
            'quantity' => 1,
            'unit_price' => $rentalPrice,
            'total_price' => $rentalPrice,
        ]);

        return $transaction;
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,aktif,selesai,dibatalkan',
        ]);

        $oldStatus = $booking->status;
        $newStatus = $data['status'];

        $booking->update($data);

        $this->syncUnitStatus($booking);

        if ($newStatus === 'aktif' && $oldStatus !== 'aktif' && ! $booking->transactions()->exists()) {
            $this->createTransactionFromBooking($booking);
        }

        return redirect()->back()->with('success', "Booking status berhasil diubah menjadi " . ucfirst($newStatus));
    }

    private function syncUnitStatus(Booking $booking): void
    {
        if (! $booking->relationLoaded('unit')) {
            $booking->load('unit');
        }

        if (! $booking->unit) {
            return;
        }

        $status = match ($booking->status) {
            'aktif' => 'digunakan',
            'pending' => 'booking',
            'selesai', 'dibatalkan' => 'kosong',
            default => 'kosong',
        };

        $booking->unit->update(['status' => $status]);
    }
}
