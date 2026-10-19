<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_import_route_returns_404(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();

        $response = $this->actingAs($admin)->get('/import');
        $response->assertStatus(404);
    }

    public function test_old_import_sakti_post_returns_404(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();

        $response = $this->actingAs($admin)->post('/import/sakti', []);
        $response->assertStatus(404);
    }

    public function test_old_import_siman_post_returns_404(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();

        $response = $this->actingAs($admin)->post('/import/siman', []);
        $response->assertStatus(404);
    }

    public function test_old_import_template_returns_404(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();

        $response = $this->actingAs($admin)->get('/import/template/sakti');
        $response->assertStatus(404);
    }

    public function test_old_import_auto_post_returns_404(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();

        $response = $this->actingAs($admin)->post('/import', []);
        $response->assertStatus(404);
    }
}
