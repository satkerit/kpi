<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest yang mengunjungi root akan dialihkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    /**
     * Dashboard tidak dapat diakses tanpa login.
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('kpi.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * User yang sudah login diarahkan dari root ke dashboard.
     */
    public function test_authenticated_user_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('kpi.index'));
    }

    /**
     * User yang sudah login dapat mengakses dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('kpi.index'));

        $response->assertStatus(200);
    }
}
