<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DualAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_register_using_mobile_number()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'contact' => '01711223344',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $user = User::where('mobile', '01711223344')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email);

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_login_using_mobile_number()
    {
        $user = User::factory()->create([
            'mobile' => '01711223344',
            'email' => null,
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => '01711223344', // Form sends 'email' field name
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_register_using_email()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'contact' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->mobile);
    }

    public function test_users_can_login_using_email()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
    }
}
