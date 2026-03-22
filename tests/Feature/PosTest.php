<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Restaurant;
use App\Models\MenuItem;
use App\Models\ItemCategory;
use App\Models\Menu;
use Modules\Inventory\Entities\InventoryItem;
use Modules\Inventory\Entities\InventoryItemCategory;
use Modules\Inventory\Entities\Unit;
use Modules\Inventory\Entities\Recipe;
use Modules\Inventory\Entities\InventoryStock;
use Livewire\Livewire;
use App\Livewire\Pos\Pos;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\WithRestaurantSetup;

class PosTest extends TestCase
{
    use RefreshDatabase, WithRestaurantSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRestaurant();
        
        $this->actingAs($this->admin);
        session(['user' => $this->admin]);
        session(['role_permissions' => ['Create Table']]);
    }

    public function test_can_render_pos_component()
    {
        Livewire::test(Pos::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.pos.pos');
    }

    public function test_can_add_item_to_cart_in_pos()
    {
        // Setup inventory and recipe to satisfy stock checks
        $invCategory = InventoryItemCategory::create(['name' => 'Vegetables', 'branch_id' => $this->branch->id]);
        $unit = Unit::create(['name' => 'Gram', 'symbol' => 'g', 'branch_id' => $this->branch->id]);
        $invItem = InventoryItem::create([
            'name' => 'Tomato',
            'inventory_item_category_id' => $invCategory->id,
            'unit_id' => $unit->id,
            'branch_id' => $this->branch->id
        ]);

        InventoryStock::create([
            'inventory_item_id' => $invItem->id,
            'branch_id' => $this->branch->id,
            'quantity' => 1000
        ]);

        $menuItem = $this->createMenuItem(['price' => 10.00]);

        Recipe::create([
            'menu_item_id' => $menuItem->id,
            'inventory_item_id' => $invItem->id,
            'quantity' => 100, // consumes 100g
            'unit_id' => $unit->id
        ]);

        // Test Livewire Component
        Livewire::test(Pos::class)
            ->call('addCartItems', $menuItem->id, 0, 0)
            ->assertCount('orderItemList', 1)
            ->assertSet('subTotal', 10.00)
            ->assertSet('total', 10.00); // Assuming no tax/charges configured
    }
}
