<?php

namespace Tests\Feature;

use App\Models\PlaystationUnit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionHistoryActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_food_drink_transaction_shows_payment_action_without_edit_buttons(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Transaction::create([
            'transaction_number' => 'TRXFD0001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'unknown',
            'payment_method' => 'cash',
            'subtotal' => 5000,
            'discount' => 0,
            'total' => 5000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 0,
        ]);

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertDontSee('Edit Jam');
        $response->assertSee('Selesaikan Bayar');
    }

    public function test_running_transaction_with_unit_still_shows_actions(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $unit = PlaystationUnit::create([
            'name' => 'PS1',
            'type' => 'reguler',
            'tv' => 'LG',
            'status' => 'digunakan',
            'hourly_rate' => 15000,
        ]);

        Transaction::create([
            'transaction_number' => 'TRXUNIT0001',
            'playstation_unit_id' => $unit->id,
            'member_id' => null,
            'customer_name' => 'unknown',
            'payment_method' => 'cash',
            'subtotal' => 10000,
            'discount' => 0,
            'total' => 10000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertSee('Edit Paket');
        $response->assertSee('Selesaikan');
    }

    public function test_food_drink_transaction_shows_payment_completion_action(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Transaction::create([
            'transaction_number' => 'TRXFD0002',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'status' => 'berjalan',
            'payment_status' => 'belum_bayar',
            'started_at' => now(),
            'duration_minutes' => 0,
        ]);

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertSee('Selesaikan Bayar');
    }
}
