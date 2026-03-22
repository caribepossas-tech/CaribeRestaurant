<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\MenuItemVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Inventory\Entities\InventoryItem;
use Modules\Inventory\Entities\InventoryItemCategory;
use Modules\Inventory\Entities\Recipe;
use Modules\Inventory\Entities\Unit;
use Modules\Inventory\Livewire\Recipes\RecipeForm;
use Tests\TestCase;
use Tests\Traits\WithRestaurantSetup;

class RecipeFormTest extends TestCase
{
    use RefreshDatabase, WithRestaurantSetup;

    protected Unit $unit;
    protected InventoryItemCategory $invCategory;
    protected InventoryItem $inventoryItem;
    protected MenuItem $menuItem;

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

        $this->inventoryItem = InventoryItem::create([
            'name' => 'Flour',
            'branch_id' => $this->branch->id,
            'inventory_item_category_id' => $this->invCategory->id,
            'unit_id' => $this->unit->id,
        ]);

        $this->menuItem = $this->createMenuItem(['item_name' => 'Pizza']);
    }

    public function test_recipe_form_component_renders(): void
    {
        Livewire::test(RecipeForm::class)
            ->assertStatus(200);
    }

    public function test_can_create_recipe(): void
    {
        Livewire::test(RecipeForm::class)
            ->set('menuItemId', $this->menuItem->id)
            ->set('ingredients', [
                [
                    'inventory_item_id' => $this->inventoryItem->id,
                    'quantity' => 200,
                    'unit_id' => $this->unit->id,
                ],
            ])
            ->call('save')
            ->assertDispatched('recipeUpdated');

        $this->assertDatabaseHas('recipes', [
            'menu_item_id' => $this->menuItem->id,
            'inventory_item_id' => $this->inventoryItem->id,
            'quantity' => 200,
            'unit_id' => $this->unit->id,
        ]);
    }

    public function test_can_create_recipe_with_multiple_ingredients(): void
    {
        $cheese = InventoryItem::create([
            'name' => 'Cheese',
            'branch_id' => $this->branch->id,
            'inventory_item_category_id' => $this->invCategory->id,
            'unit_id' => $this->unit->id,
        ]);

        Livewire::test(RecipeForm::class)
            ->set('menuItemId', $this->menuItem->id)
            ->set('ingredients', [
                [
                    'inventory_item_id' => $this->inventoryItem->id,
                    'quantity' => 200,
                    'unit_id' => $this->unit->id,
                ],
                [
                    'inventory_item_id' => $cheese->id,
                    'quantity' => 100,
                    'unit_id' => $this->unit->id,
                ],
            ])
            ->call('save')
            ->assertDispatched('recipeUpdated');

        $this->assertEquals(2, Recipe::where('menu_item_id', $this->menuItem->id)->count());
    }

    public function test_recipe_requires_menu_item(): void
    {
        Livewire::test(RecipeForm::class)
            ->set('ingredients', [
                [
                    'inventory_item_id' => $this->inventoryItem->id,
                    'quantity' => 200,
                    'unit_id' => $this->unit->id,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['menuItemId']);
    }

    public function test_recipe_requires_at_least_one_ingredient(): void
    {
        Livewire::test(RecipeForm::class)
            ->set('menuItemId', $this->menuItem->id)
            ->set('ingredients', [])
            ->call('save')
            ->assertHasErrors(['ingredients']);
    }

    public function test_recipe_ingredient_requires_quantity(): void
    {
        Livewire::test(RecipeForm::class)
            ->set('menuItemId', $this->menuItem->id)
            ->set('ingredients', [
                [
                    'inventory_item_id' => $this->inventoryItem->id,
                    'quantity' => '',
                    'unit_id' => $this->unit->id,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['ingredients.0.quantity']);
    }

    public function test_recipe_ingredient_requires_valid_inventory_item(): void
    {
        Livewire::test(RecipeForm::class)
            ->set('menuItemId', $this->menuItem->id)
            ->set('ingredients', [
                [
                    'inventory_item_id' => 9999,
                    'quantity' => 100,
                    'unit_id' => $this->unit->id,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['ingredients.0.inventory_item_id']);
    }

    public function test_saving_recipe_replaces_existing_recipes(): void
    {
        // Create initial recipe
        Recipe::create([
            'menu_item_id' => $this->menuItem->id,
            'inventory_item_id' => $this->inventoryItem->id,
            'quantity' => 100,
            'unit_id' => $this->unit->id,
        ]);

        $this->assertEquals(1, Recipe::where('menu_item_id', $this->menuItem->id)->count());

        // Save new recipe (should replace)
        Livewire::test(RecipeForm::class)
            ->set('menuItemId', $this->menuItem->id)
            ->set('ingredients', [
                [
                    'inventory_item_id' => $this->inventoryItem->id,
                    'quantity' => 300,
                    'unit_id' => $this->unit->id,
                ],
            ])
            ->call('save')
            ->assertDispatched('recipeUpdated');

        $recipes = Recipe::where('menu_item_id', $this->menuItem->id)->get();
        $this->assertEquals(1, $recipes->count());
        $this->assertEquals(300, $recipes->first()->quantity);
    }

    public function test_add_and_remove_ingredient_fields(): void
    {
        Livewire::test(RecipeForm::class)
            ->assertCount('ingredients', 1)
            ->call('addIngredient')
            ->assertCount('ingredients', 2)
            ->call('removeIngredient', 0)
            ->assertCount('ingredients', 1);
    }

    public function test_updated_menu_item_loads_variations(): void
    {
        $variation = MenuItemVariation::create([
            'variation' => 'Large',
            'price' => 15.00,
            'menu_item_id' => $this->menuItem->id,
        ]);

        Livewire::test(RecipeForm::class)
            ->set('menuItemId', $this->menuItem->id)
            ->assertCount('availableVariations', 1);
    }

    public function test_can_create_variation_specific_recipe(): void
    {
        $variation = MenuItemVariation::create([
            'variation' => 'Large',
            'price' => 15.00,
            'menu_item_id' => $this->menuItem->id,
        ]);

        Livewire::test(RecipeForm::class)
            ->set('menuItemId', $this->menuItem->id)
            ->set('variationId', $variation->id)
            ->set('ingredients', [
                [
                    'inventory_item_id' => $this->inventoryItem->id,
                    'quantity' => 400,
                    'unit_id' => $this->unit->id,
                ],
            ])
            ->call('save')
            ->assertDispatched('recipeUpdated');

        $this->assertDatabaseHas('recipes', [
            'menu_item_id' => $this->menuItem->id,
            'menu_item_variation_id' => $variation->id,
            'quantity' => 400,
        ]);
    }

    public function test_auto_sets_unit_when_ingredient_selected(): void
    {
        Livewire::test(RecipeForm::class)
            ->set('ingredients.0.inventory_item_id', $this->inventoryItem->id)
            ->assertSet('ingredients.0.unit_id', $this->unit->id);
    }
}
