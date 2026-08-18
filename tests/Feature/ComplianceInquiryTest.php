<?php

namespace Tests\Feature;

use App\Models\Compliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComplianceInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_register_can_be_narrowed_to_an_expiry_window(): void
    {
        $this->records();

        $this->actingAs($this->admin())
            ->get(route('compliance.index', ['from' => '2026-08-01', 'to' => '2026-09-30']))
            ->assertOk()
            ->assertSee('KB Prasac')
            ->assertSee('Wing Bank')
            ->assertDontSee('PPCB Bank')
            ->assertDontSee('Open Ended Co');
    }

    public function test_either_end_of_the_range_works_on_its_own(): void
    {
        $this->records();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('compliance.index', ['from' => '2026-09-01']))
            ->assertOk()
            ->assertSee('Wing Bank')
            ->assertSee('PPCB Bank')
            ->assertDontSee('KB Prasac');

        $this->actingAs($admin)
            ->get(route('compliance.index', ['to' => '2026-08-31']))
            ->assertOk()
            ->assertSee('KB Prasac')
            ->assertDontSee('Wing Bank');
    }

    public function test_a_backwards_range_is_read_as_given_rather_than_returning_nothing(): void
    {
        $this->records();

        $this->actingAs($this->admin())
            ->get(route('compliance.index', ['from' => '2026-09-30', 'to' => '2026-08-01']))
            ->assertOk()
            ->assertSee('KB Prasac')
            ->assertSee('Wing Bank')
            ->assertDontSee('PPCB Bank');
    }

    public function test_the_range_combines_with_the_status_filter(): void
    {
        $this->records();

        $this->actingAs($this->admin())
            ->get(route('compliance.index', ['status' => 'pending', 'from' => '2026-08-01', 'to' => '2026-09-30']))
            ->assertOk()
            ->assertSee('KB Prasac')
            ->assertDontSee('Wing Bank');
    }

    public function test_a_malformed_date_drops_the_filter_instead_of_erroring(): void
    {
        $this->records();

        $this->actingAs($this->admin())
            ->get(route('compliance.index', ['from' => 'not-a-date']))
            ->assertOk()
            ->assertSee('Wing Bank')
            ->assertSee('KB Prasac')
            ->assertSee('PPCB Bank')
            ->assertSee('Open Ended Co');
    }

    private function records(): void
    {
        Compliance::forceCreate([
            'name' => 'Wing Bank', 'category' => 'Business Licence',
            'status' => 'verified', 'expires_on' => '2026-09-05',
        ]);
        Compliance::forceCreate([
            'name' => 'KB Prasac', 'category' => 'Business Licence',
            'status' => 'pending', 'expires_on' => '2026-08-31',
        ]);
        Compliance::forceCreate([
            'name' => 'PPCB Bank', 'category' => 'Business Licence',
            'status' => 'verified', 'expires_on' => '2027-01-17',
        ]);
        Compliance::forceCreate([
            'name' => 'Open Ended Co', 'category' => 'Insurance',
            'status' => 'verified', 'expires_on' => null,
        ]);
    }

    private function admin(): User
    {
        return User::forceCreate([
            'first_name' => 'Toeng',
            'last_name' => 'Bunsin',
            'email' => 'toeng@example.com',
            'phone_number' => '+855 12 000 000',
            'role' => 'Administrator',
            'password' => Hash::make('password123'),
        ]);
    }
}
