<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Entities\InventoryItem;
use Modules\Inventory\Entities\InventoryItemCategory;
use Modules\Inventory\Entities\InventoryStock;
use Modules\Inventory\Entities\Recipe;
use Modules\Inventory\Entities\Unit;
use Tests\TestCase;
use Tests\Traits\WithRestaurantSetup;

class MenuItemStockTest extends TestCase
{
    use RefreshDatabase, WithRestaurantSetup;

    protected Unit $unit;
    protected InventoryItemCategory $invCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRestaurant();

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

    protected function createInventoryItem(string $name, float $stockQty): InventoryItem
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
            'quantity' => $stockQty,
        ]);

        return $item;
    }

    protected function createRecipe(MenuItem $menuItem, InventoryItem $inventoryItem, float $qty): Recipe
    {
        return Recipe::create([
            'menu_item_id' => $menuItem->id,
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => $qty,
            'unit_id' => $this->unit->id,
        ]);
    }

    public function test_check_multiple_items_stock_passes_with_sufficient_stock(): void
    {
        $flour = $this->createInventoryItem('Flour', 1000);
        $cheese = $this->createInventoryItem('Cheese', 500);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        $this->createRecipe($pizza, $flour, 200); // 200g flour per pizza
        $this->createRecipe($pizza, $cheese, 100); // 100g cheese per pizza

        $items = [
            ['item' => $pizza, 'quantity' => 2], // 2 pizzas = 400g flour, 200g cheese
        ];

        $result = MenuItem::checkMultipleItemsStock($items, $this->restaurant);

        $this->assertTrue($result['status']);
    }

    public function test_check_multiple_items_stock_fails_with_insufficient_stock(): void
    {
        $flour = $this->createInventoryItem('Flour', 300);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        $this->createRecipe($pizza, $flour, 200);

        $items = [
            ['item' => $pizza, 'quantity' => 2], // Needs 400g, only 300g available
        ];

        $result = MenuItem::checkMultipleItemsStock($items, $this->restaurant);

        $this->assertFalse($result['status']);
        $this->assertEquals('flexible', $result['mode']);
        $this->assertNotEmpty($result['message']);
    }

    public function test_check_multiple_items_stock_aggregates_across_items(): void
    {
        $flour = $this->createInventoryItem('Flour', 500);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        $this->createRecipe($pizza, $flour, 200);

        $bread = $this->createMenuItem(['item_name' => 'Bread']);
        $this->createRecipe($bread, $flour, 150);

        $items = [
            ['item' => $pizza, 'quantity' => 2], // 400g flour
            ['item' => $bread, 'quantity' => 1], // 150g flour
        ];
        // Total needed: 550g, available: 500g

        $result = MenuItem::checkMultipleItemsStock($items, $this->restaurant);

        $this->assertFalse($result['status']);
    }

    public function test_check_multiple_items_stock_passes_with_no_recipes(): void
    {
        $menuItem = $this->createMenuItem(['item_name' => 'Simple Item']);

        $items = [
            ['item' => $menuItem, 'quantity' => 10],
        ];

        $result = MenuItem::checkMultipleItemsStock($items, $this->restaurant);

        $this->assertTrue($result['status']);
    }

    public function test_check_multiple_items_stock_uses_restaurant_stock_check_mode(): void
    {
        $this->restaurant->update(['stock_check_mode' => 'strict']);

        $flour = $this->createInventoryItem('Flour', 100);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        $this->createRecipe($pizza, $flour, 200);

        $items = [
            ['item' => $pizza, 'quantity' => 1],
        ];

        $result = MenuItem::checkMultipleItemsStock($items, $this->restaurant->fresh());

        $this->assertFalse($result['status']);
        $this->assertEquals('strict', $result['mode']);
    }

    public function test_deduct_stock_reduces_inventory_quantities(): void
    {
        $flour = $this->createInventoryItem('Flour', 1000);
        $cheese = $this->createInventoryItem('Cheese', 500);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        $this->createRecipe($pizza, $flour, 200);
        $this->createRecipe($pizza, $cheese, 100);

        $pizza->deductStock(2); // 2 pizzas

        $this->assertEquals(600, InventoryStock::where('inventory_item_id', $flour->id)->first()->quantity);
        $this->assertEquals(300, InventoryStock::where('inventory_item_id', $cheese->id)->first()->quantity);
    }

    public function test_deduct_stock_does_nothing_without_recipe(): void
    {
        $flour = $this->createInventoryItem('Flour', 1000);
        $menuItem = $this->createMenuItem(['item_name' => 'No Recipe Item']);

        $menuItem->deductStock(5);

        $this->assertEquals(1000, InventoryStock::where('inventory_item_id', $flour->id)->first()->quantity);
    }

    public function test_deduct_stock_can_go_negative(): void
    {
        $flour = $this->createInventoryItem('Flour', 100);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        $this->createRecipe($pizza, $flour, 200);

        $pizza->deductStock(1); // Deducts 200 from 100 = -100

        $stock = InventoryStock::where('inventory_item_id', $flour->id)->first();
        $this->assertEquals(-100, $stock->quantity);
    }

    public function test_check_ingredients_stock_single_item(): void
    {
        $flour = $this->createInventoryItem('Flour', 1000);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        $this->createRecipe($pizza, $flour, 200);

        $result = $pizza->checkIngredientsStock(3); // 600g needed, 1000g available

        $this->assertTrue($result['status']);
    }

    public function test_check_ingredients_stock_insufficient(): void
    {
        $flour = $this->createInventoryItem('Flour', 100);

        $pizza = $this->createMenuItem(['item_name' => 'Pizza']);
        $this->createRecipe($pizza, $flour, 200);

        $result = $pizza->checkIngredientsStock(1);

        $this->assertFalse($result['status']);
    }
}
