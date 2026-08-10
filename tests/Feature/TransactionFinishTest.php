<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionFinishTest extends TestCase
{
    use RefreshDatabase;

    public function test_finishing_transaction_without_unit_does_not_throw_server_error(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'TRXTEST0001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'unknown',
            'payment_method' => 'cash',
            'subtotal' => 5000,
            'discount' => 0,
            'total' => 5000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($user)->post('/transactions/' . $transaction->id . '/finish');

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'selesai',
        ]);
    }
}
