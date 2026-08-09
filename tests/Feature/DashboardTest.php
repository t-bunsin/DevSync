<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_the_dashboard(): void
    {
        $user = User::forceCreate([
            'first_name' => 'Sophea',
            'last_name' => 'Admin',
            'email' => 'sophea@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->get('/home')
            ->assertOk()
            ->assertSee('Good to see you, Sophea.')
            ->assertSee('Application activity')
            ->assertSee('Pipeline health');
    }
}
