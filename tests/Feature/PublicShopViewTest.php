<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRestaurantSetup;

class PublicShopViewTest extends TestCase
{
    use RefreshDatabase, WithRestaurantSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRestaurant();
    }

    public function test_public_shop_page_loads_by_restaurant_hash(): void
    {
        $response = $this->get('/restaurant/' . $this->restaurant->hash);

        $response->assertStatus(200);
        $response->assertViewIs('shop.index');
        $response->assertViewHas('restaurant', $this->restaurant);
    }

    public function test_public_shop_returns_404_for_invalid_hash(): void
    {
        $response = $this->get('/restaurant/non-existent-hash');

        $response->assertStatus(404);
    }

    public function test_public_shop_page_passes_correct_data(): void
    {
        $response = $this->get('/restaurant/' . $this->restaurant->hash);

        $response->assertStatus(200);
        $response->assertViewHas('shopBranch');
        $response->assertViewHas('getTable');
        $response->assertViewHas('canCreateOrder');
    }

    public function test_table_order_page_loads_by_table_hash(): void
    {
        $area = Area::create([
            'area_name' => 'Main Hall',
            'branch_id' => $this->branch->id,
        ]);

        $table = Table::create([
            'table_code' => 'T-1',
            'hash' => md5('test-table-hash'),
            'status' => 'active',
            'available_status' => 'available',
            'area_id' => $area->id,
            'seating_capacity' => 4,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->get('/restaurant/table/' . $table->hash);

        $response->assertStatus(200);
        $response->assertViewIs('shop.index');
        $response->assertViewHas('tableHash', $table->hash);
    }

    public function test_table_order_with_invalid_hash_falls_back_to_restaurant_id(): void
    {
        // When hash doesn't match a table, it tries to find restaurant by ID
        $response = $this->get('/restaurant/table/' . $this->restaurant->id);

        $response->assertStatus(200);
        $response->assertViewHas('tableHash', null);
        $response->assertViewHas('getTable', true);
    }

    public function test_shop_page_includes_cart_livewire_component(): void
    {
        $response = $this->get('/restaurant/' . $this->restaurant->hash);

        $response->assertStatus(200);
        $response->assertSeeLivewire('shop.cart');
    }

    public function test_shop_page_includes_customer_signup_component(): void
    {
        $response = $this->get('/restaurant/' . $this->restaurant->hash);

        $response->assertStatus(200);
        $response->assertSeeLivewire('customer.signup');
    }
}
