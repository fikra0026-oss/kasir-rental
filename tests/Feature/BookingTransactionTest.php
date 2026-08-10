<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\PlaystationUnit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_booking_creates_transaction_and_marks_unit_in_use(): void
    {
        $user = User::factory()->create();
        $unit = PlaystationUnit::create([
            'name' => 'PS1',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'kosong',
            'hourly_rate' => 30000,
        ]);

        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'customer_name' => 'Budi',
            'phone' => '08123456789',
            'date' => '2026-07-02',
            'start_time' => '14:00',
            'duration_minutes' => 60,
            'playstation_unit_id' => $unit->id,
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('bookings.index'));

        $booking = Booking::latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertDatabaseHas('transactions', ['booking_id' => $booking->id]);
        $this->assertEquals('digunakan', $unit->fresh()->status);
    }

    public function test_completed_booking_returns_unit_to_available_status(): void
    {
        $user = User::factory()->create();
        $unit = PlaystationUnit::create([
            'name' => 'PS2',
            'type' => 'PS4',
            'tv' => 'TV Test',
            'status' => 'booking',
            'hourly_rate' => 25000,
        ]);

        $booking = Booking::create([
            'customer_name' => 'Siti',
            'phone' => '0811222333',
            'date' => '2026-07-02',
            'start_time' => '16:00',
            'duration_minutes' => 90,
            'playstation_unit_id' => $unit->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($user)->patch(route('bookings.update-status', $booking), [
            'status' => 'selesai',
        ]);

        $this->assertEquals('kosong', $unit->fresh()->status);
    }

    public function test_dashboard_shows_countdown_for_active_rental(): void
    {
        $user = User::factory()->create();
        $unit = PlaystationUnit::create([
            'name' => 'PS3',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'digunakan',
            'hourly_rate' => 40000,
        ]);

        Transaction::create([
            'transaction_number' => 'TRXTEST001',
            'playstation_unit_id' => $unit->id,
            'customer_name' => 'Ari',
            'payment_method' => 'cash',
            'subtotal' => 40000,
            'discount' => 0,
            'total' => 40000,
            'status' => 'berjalan',
            'started_at' => now()->subMinutes(10),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Sisa');
    }

    public function test_expired_running_transaction_is_finished_when_dashboard_is_loaded(): void
    {
        $user = User::factory()->create();
        $unit = PlaystationUnit::create([
            'name' => 'PS6',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'digunakan',
            'hourly_rate' => 40000,
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'TRXTEST003',
            'playstation_unit_id' => $unit->id,
            'customer_name' => 'Rara',
            'payment_method' => 'cash',
            'subtotal' => 40000,
            'discount' => 0,
            'total' => 40000,
            'status' => 'berjalan',
            'started_at' => now()->subMinutes(90),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeText('Kosong');
        $transaction->refresh();
        $this->assertSame('selesai', $transaction->status);
        $this->assertSame('kosong', $unit->fresh()->status);
    }

    public function test_running_transaction_can_update_duration_and_recalculate_total(): void
    {
        $user = User::factory()->create();
        $unit = PlaystationUnit::create([
            'name' => 'PS4',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'digunakan',
            'hourly_rate' => 30000,
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'TRXTEST002',
            'playstation_unit_id' => $unit->id,
            'customer_name' => 'Dina',
            'payment_method' => 'cash',
            'subtotal' => 30000,
            'discount' => 0,
            'total' => 30000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 60,
        ]);

        $transaction->items()->create([
            'type' => 'rental',
            'description' => 'Manual 60 menit',
            'quantity' => 1,
            'unit_price' => 30000,
            'total_price' => 30000,
        ]);

        $package = \App\Models\RentalPackage::create([
            'name' => 'Paket 90 Menit',
            'price' => 45000,
            'duration_minutes' => 90,
            'description' => 'Paket 90 menit',
            'is_member' => false,
            'is_weekend' => false,
            'is_holiday' => false,
        ]);

        $response = $this->actingAs($user)->patch(route('transactions.update', $transaction), [
            'package_id' => $package->id,
        ]);

        $response->assertRedirect(route('transactions.index'));

        $transaction->refresh();
        $this->assertSame(90, $transaction->duration_minutes);
        $this->assertEquals(45000, $transaction->subtotal);
        $this->assertEquals(45000, $transaction->total);
        $this->assertEquals(45000, $transaction->items()->where('type', 'rental')->first()->total_price);
    }

    public function test_deleting_running_transaction_returns_unit_to_available_status(): void
    {
        $user = User::factory()->create();
        $unit = PlaystationUnit::create([
            'name' => 'PS7',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'digunakan',
            'hourly_rate' => 35000,
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'TRXTEST004',
            'playstation_unit_id' => $unit->id,
            'customer_name' => 'Tono',
            'payment_method' => 'cash',
            'subtotal' => 35000,
            'discount' => 0,
            'total' => 35000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        $this->assertSame('kosong', $unit->fresh()->status);
    }

    public function test_store_with_rental_and_products_saves_items(): void
    {
        $user = User::factory()->create();
        $unit = PlaystationUnit::create([
            'name' => 'PS5',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'kosong',
            'hourly_rate' => 30000,
        ]);

        $product = \App\Models\Product::create([
            'name' => 'Kebab',
            'price' => 20000,
            'stock' => 10,
            'category' => 'Makanan',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'playstation_unit_id' => $unit->id,
            'manual_minutes' => 60,
            'payment_method' => 'cash',
            'products' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('transactions.index'));

        $transaction = \App\Models\Transaction::latest('id')->first();
        $this->assertNotNull($transaction);
        $this->assertDatabaseHas('transaction_items', ['transaction_id' => $transaction->id, 'description' => 'Kebab']);
        $this->assertDatabaseHas('transaction_items', ['transaction_id' => $transaction->id, 'type' => 'rental']);
    }
}
