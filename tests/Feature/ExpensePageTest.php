<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_index_page_can_be_rendered_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/expenses');

        $response->assertStatus(200);
        $response->assertSee('Pengeluaran');
    }
}
