<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthAndAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Rose Colection');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin_test@rose.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test2@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'admin_test2@rose.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::create([
            'name' => 'Inactive Cashier',
            'email' => 'inactive@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'inactive@rose.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    public function test_kasir_redirected_to_pos_after_login(): void
    {
        $kasir = User::create([
            'name' => 'Kasir Test',
            'email' => 'kasir_test@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'kasir_test@rose.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($kasir);
        $response->assertRedirect(route('pos.index'));
    }

    public function test_kasir_cannot_access_admin_settings_or_profit_reports(): void
    {
        $kasir = User::create([
            'name' => 'Kasir Test 2',
            'email' => 'kasir_test2@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->actingAs($kasir);

        // Forbidden routes for cashier
        $this->get('/settings')->assertStatus(403);
        $this->get('/profit')->assertStatus(403);
        $this->get('/reports')->assertStatus(403);
        $this->get('/users')->assertStatus(403);
        $this->get('/purchases/create')->assertStatus(403);
    }

    public function test_admin_can_access_all_modules(): void
    {
        $admin = User::create([
            'name' => 'Admin Full',
            'email' => 'admin_full@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $this->get('/dashboard')->assertStatus(200);
        $this->get('/products')->assertStatus(200);
        $this->get('/inventory')->assertStatus(200);
        $this->get('/profit')->assertStatus(200);
        $this->get('/reports')->assertStatus(200);
        $this->get('/settings')->assertStatus(200);
    }
}
