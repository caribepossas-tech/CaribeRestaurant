<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\WithRestaurantSetup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Order;
use App\Models\Payment;
use App\Models\MenuItem;
use App\Livewire\Dashboard\TodayOrderCount;
use App\Livewire\Dashboard\TodayEarnings;
use Livewire\Livewire;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithRestaurantSetup;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->forget('package');
        session()->forget('package');
        $this->setUpRestaurant();
        
        $currency = \App\Models\GlobalCurrency::create([
            'currency_name' => 'US Dollar',
            'currency_symbol' => '$',
            'currency_code' => 'USD',
        ]);

        $package = \App\Models\Package::first();
        if ($package) {
            $package->update(['currency_id' => $currency->id]);
        } else {
            \App\Models\Package::create([
                'package_name' => 'Basic',
                'currency_id' => $currency->id,
                'price' => 0,
            ]);
        }

        \App\Models\GlobalSetting::create([
            'name' => 'Test Restaurant',
            'default_currency_id' => $currency->id,
            'timezone' => 'UTC',
            'locale' => 'en'
        ]);
        
        $this->actingAs($this->admin);
        session(['user' => $this->admin]);
        session(['role_permissions' => ['Show Order', 'Show Reports', 'Show Customer']]);
    }

    public function test_dashboard_page_loads_successfully()
    {
        $this->actingAs($this->admin);
        session(['user' => $this->admin]);
        
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
    }

    public function test_dashboard_components_render_successfully()
    {
        $this->actingAs($this->admin);
        session(['user' => $this->admin]);

        // Test a couple of key components
        Livewire::test(TodayOrderCount::class)
            ->assertStatus(200);

        Livewire::test(TodayEarnings::class)
            ->assertStatus(200);
    }
}
