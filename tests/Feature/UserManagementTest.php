<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_or_submit_the_create_user_form(): void
    {
        $this->get(route('user.create'))->assertRedirect(route('login'));

        $this->post(route('users.store'), [])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_the_redesigned_create_user_form(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('user.create'))
            ->assertOk()
            ->assertSee('Add a new user')
            ->assertSee('Personal information')
            ->assertSee('Access and security')
            ->assertSee('css/users-create.css', false);
    }

    public function test_authenticated_user_can_create_a_user_with_phone_and_valid_role(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'first_name' => 'Dara',
            'last_name' => 'Sok',
            'email' => 'dara@example.com',
            'phone_number' => '+855 12 345 678',
            'role' => 'Manager',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);

        $response
            ->assertRedirect(route('users'))
            ->assertSessionHas('success', 'User created successfully!');

        $this->assertDatabaseHas('users', [
            'first_name' => 'Dara',
            'last_name' => 'Sok',
            'email' => 'dara@example.com',
            'phone_number' => '+855 12 345 678',
            'role' => 'Manager',
        ]);

        $this->assertTrue(Hash::check('SecurePass123', User::where('email', 'dara@example.com')->firstOrFail()->password));
    }

    public function test_create_user_rejects_an_unknown_role_and_short_password(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->from(route('user.create'))
            ->post(route('users.store'), [
                'first_name' => 'Test',
                'last_name' => 'Person',
                'email' => 'test@example.com',
                'phone_number' => '+855 10 000 000',
                'role' => 'Superuser',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertRedirect(route('user.create'))
            ->assertSessionHasErrors(['role', 'password']);

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    private function createAdmin(): User
    {
        return User::forceCreate([
            'first_name' => 'Sophea',
            'last_name' => 'Admin',
            'email' => 'sophea@example.com',
            'phone_number' => '+855 12 000 000',
            'role' => 'Administrator',
            'password' => Hash::make('password123'),
        ]);
    }
}
