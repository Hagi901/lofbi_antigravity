<?php

namespace Tests\Feature;

use App\Models\Aset;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_all_web_pages(): void
    {
        $this->seed();
        $user = User::first();

        // 1. Dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // 2. Assets
        $response = $this->actingAs($user)->get('/assets');
        $response->assertStatus(200);

        // 3. Assets Create
        $response = $this->actingAs($user)->get('/assets/create');
        $response->assertStatus(200);

        // 4. Inventory
        $response = $this->actingAs($user)->get('/inventory');
        $response->assertStatus(200);

        // 5. Inventory In & Out
        $response = $this->actingAs($user)->get('/inventory/in');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/inventory/out');
        $response->assertStatus(200);

        // 6. Opname
        $response = $this->actingAs($user)->get('/opname');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/opname/create');
        $response->assertStatus(200);

        // 7. Reports & Download
        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);

        // 8. Profile & Settings
        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(200);
    }
}
