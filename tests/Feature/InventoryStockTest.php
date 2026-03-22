<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Entities\InventoryItem;
use Modules\Inventory\Entities\InventoryItemCategory;
use Modules\Inventory\Entities\InventoryStock;
use Modules\Inventory\Entities\Recipe;
use Modules\Inventory\Entities\Unit;
use Tests\TestCase;
use Tests\Traits\WithRestaurantSetup;

class InventoryStockTest extends TestCase
{
    use RefreshDatabase, WithRestaurantSetup;

    protected Unit $unit;
    protected InventoryItemCategory $invCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRestaurant();
        $this->actingAs($this->admin);

        $this->unit = Unit::create([
            'name' => 'Gram',
            'symbol' => 'g',
            'branch_id' => $this->branch->id,
        ]);

        $this->invCategory = InventoryItemCategory::create([
            'name' => 'Ingredients',
            'branch_id' => $this->branch->id,
        ]);
    }

    protected function createInventoryItemWithStock(string $name, float $qty): InventoryItem
    {
        $item = InventoryItem::create([
            'name' => $name,
            'branch_id' => $this->branch->id,
            'inventory_item_category_id' => $this->invCategory->id,
            'unit_id' => $this->unit->id,
        ]);

        InventoryStock::create([
            'inventory_item_id' => $item->id,
            'branch_id' => $this->branch->id,
            'quantity' => $qty,
        ]);

        return $item;
    }

    public function test_inventory_stock_is_created_correctly(): void
    {
        $item = $this->createInventoryItemWithStock('Rice', 5000);

        $stock = InventoryStock::where('inventory_item_id', $item->id)
            ->where('branch_id', $this->branch->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(5000, $stock->quantity);
    }

    public function test_stock_decrement_works(): void
    {
        $item = $this->createInventoryItemWithStock('Rice', 5000);

        InventoryStock::where('inventory_item_id', $item->id)
            ->where('branch_id', $this->branch->id)
            ->decrement('quantity', 1500);

        $stock = InventoryStock::where('inventory_item_id', $item->id)->first();
        $this->assertEquals(3500, $stock->quantity);
    }

    public function test_recipe_links_menu_item_to_inventory(): void
    {
        $flour = $this->createInventoryItemWithStock('Flour', 1000);
        $menuItem = $this->createMenuItem(['item_name' => 'Bread']);

        $recipe = Recipe::create([
            'menu_item_id' => $menuItem->id,
            'inventory_item_id' => $flour->id,
            'quantity' => 250,
            'unit_id' => $this->unit->id,
        ]);

        $this->assertDatabaseHas('recipes', [
            'menu_item_id' => $menuItem->id,
            'inventory_item_id' => $flour->id,
            'quantity' => 250,
        ]);

        $this->assertNotNull($recipe->inventoryItem);
        $this->assertEquals('Flour', $recipe->inventoryItem->name);
    }

    public function test_full_order_flow_deducts_stock(): void
    {
        $flour = $this->createInventoryItemWithStock('Flour', 1000);
        $cheese = $this->createInventoryItemWithStock('Cheese', 500);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza', 'price' => 15]);

        Recipe::create([
            'menu_item_id' => $pizza->id,
            'inventory_item_id' => $flour->id,
            'quantity' => 200,
            'unit_id' => $this->unit->id,
        ]);
        Recipe::create([
            'menu_item_id' => $pizza->id,
            'inventory_item_id' => $cheese->id,
            'quantity' => 100,
            'unit_id' => $this->unit->id,
        ]);

        // Simulate stock check before ordering
        $items = [['item' => $pizza, 'quantity' => 3]];
        $check = MenuItem::checkMultipleItemsStock($items, $this->restaurant);
        $this->assertTrue($check['status']);

        // Simulate stock deduction after order
        $pizza->deductStock(3);

        $flourStock = InventoryStock::where('inventory_item_id', $flour->id)->first()->quantity;
        $cheeseStock = InventoryStock::where('inventory_item_id', $cheese->id)->first()->quantity;

        $this->assertEquals(400, $flourStock);  // 1000 - (200 * 3)
        $this->assertEquals(200, $cheeseStock); // 500 - (100 * 3)
    }

    public function test_stock_check_with_zero_stock(): void
    {
        $flour = $this->createInventoryItemWithStock('Flour', 0);
        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);

        Recipe::create([
            'menu_item_id' => $pizza->id,
            'inventory_item_id' => $flour->id,
            'quantity' => 200,
            'unit_id' => $this->unit->id,
        ]);

        $items = [['item' => $pizza, 'quantity' => 1]];
        $check = MenuItem::checkMultipleItemsStock($items, $this->restaurant);

        $this->assertFalse($check['status']);
    }

    public function test_stock_check_without_inventory_stock_record(): void
    {
        // Create inventory item without stock record
        $item = InventoryItem::create([
            'name' => 'New Ingredient',
            'branch_id' => $this->branch->id,
            'inventory_item_category_id' => $this->invCategory->id,
            'unit_id' => $this->unit->id,
        ]);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        Recipe::create([
            'menu_item_id' => $pizza->id,
            'inventory_item_id' => $item->id,
            'quantity' => 100,
            'unit_id' => $this->unit->id,
        ]);

        $items = [['item' => $pizza, 'quantity' => 1]];
        $check = MenuItem::checkMultipleItemsStock($items, $this->restaurant);

        // Should fail because stock defaults to 0
        $this->assertFalse($check['status']);
    }

    public function test_multiple_branches_have_independent_stock(): void
    {
        $flour = $this->createInventoryItemWithStock('Flour', 1000);

        // Create a second branch with different stock
        $branch2 = \App\Models\Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Branch 2',
            'address' => '456 Test St',
        ]);

        InventoryStock::create([
            'inventory_item_id' => $flour->id,
            'branch_id' => $branch2->id,
            'quantity' => 200,
        ]);

        $stock1 = InventoryStock::where('inventory_item_id', $flour->id)
            ->where('branch_id', $this->branch->id)->first();
        $stock2 = InventoryStock::where('inventory_item_id', $flour->id)
            ->where('branch_id', $branch2->id)->first();

        $this->assertEquals(1000, $stock1->quantity);
        $this->assertEquals(200, $stock2->quantity);
    }
}
