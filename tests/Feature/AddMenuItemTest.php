<?php

namespace Tests\Feature;

use App\Livewire\Forms\AddMenuItem;
use App\Models\MenuItem;
use App\Models\MenuItemVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithRestaurantSetup;

class AddMenuItemTest extends TestCase
{
    use RefreshDatabase, WithRestaurantSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRestaurant();
        $this->actingAs($this->admin);
    }

    public function test_add_menu_item_component_renders(): void
    {
        Livewire::test(AddMenuItem::class)
            ->assertStatus(200);
    }

    public function test_can_create_simple_menu_item(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('translationNames.en', 'Hamburger')
            ->set('translationDescriptions.en', 'Delicious burger')
            ->set('itemPrice', 12.99)
            ->set('itemCategory', $this->category->id)
            ->set('menu', $this->menu->id)
            ->set('itemType', 'non-veg')
            ->set('isAvailable', true)
            ->call('submitForm')
            ->assertDispatched('menuItemAdded');

        $this->assertDatabaseHas('menu_items', [
            'item_name' => 'Hamburger',
            'price' => 12.99,
            'type' => 'non-veg',
            'menu_id' => $this->menu->id,
            'item_category_id' => $this->category->id,
        ]);
    }

    public function test_can_create_menu_item_with_variations(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('translationNames.en', 'Pizza')
            ->set('itemCategory', $this->category->id)
            ->set('menu', $this->menu->id)
            ->set('hasVariations', true)
            ->call('checkVariations')
            ->set('variationName.1', 'Small')
            ->set('variationPrice.1', 8.99)
            ->set('isAvailable', true)
            ->call('submitForm')
            ->assertDispatched('menuItemAdded');

        $menuItem = MenuItem::withoutGlobalScopes()->where('item_name', 'Pizza')->first();
        $this->assertNotNull($menuItem);
        $this->assertEquals(0, $menuItem->price); // Price is 0 when has variations

        $this->assertDatabaseHas('menu_item_variations', [
            'menu_item_id' => $menuItem->id,
            'variation' => 'Small',
            'price' => 8.99,
        ]);
    }

    public function test_menu_item_requires_name(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('itemPrice', 10)
            ->set('itemCategory', $this->category->id)
            ->set('menu', $this->menu->id)
            ->set('isAvailable', true)
            ->call('submitForm')
            ->assertHasErrors(['translationNames.en']);
    }

    public function test_menu_item_requires_category(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('translationNames.en', 'Test Item')
            ->set('itemPrice', 10)
            ->set('menu', $this->menu->id)
            ->set('isAvailable', true)
            ->call('submitForm')
            ->assertHasErrors(['itemCategory']);
    }

    public function test_menu_item_requires_menu(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('translationNames.en', 'Test Item')
            ->set('itemPrice', 10)
            ->set('itemCategory', $this->category->id)
            ->set('isAvailable', true)
            ->call('submitForm')
            ->assertHasErrors(['menu']);
    }

    public function test_menu_item_requires_price_without_variations(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('translationNames.en', 'Test Item')
            ->set('itemCategory', $this->category->id)
            ->set('menu', $this->menu->id)
            ->set('hasVariations', false)
            ->set('isAvailable', true)
            ->call('submitForm')
            ->assertHasErrors(['itemPrice']);
    }

    public function test_menu_item_creates_translations(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('translationNames.en', 'Burger')
            ->set('translationDescriptions.en', 'A tasty burger')
            ->set('itemPrice', 10)
            ->set('itemCategory', $this->category->id)
            ->set('menu', $this->menu->id)
            ->set('isAvailable', true)
            ->call('submitForm')
            ->assertDispatched('menuItemAdded');

        $menuItem = MenuItem::withoutGlobalScopes()->where('item_name', 'Burger')->first();

        $this->assertDatabaseHas('menu_item_translations', [
            'menu_item_id' => $menuItem->id,
            'locale' => 'en',
            'item_name' => 'Burger',
            'description' => 'A tasty burger',
        ]);
    }

    public function test_add_and_remove_variation_fields(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('hasVariations', true)
            ->call('checkVariations')
            ->assertSet('showItemPrice', false)
            ->call('addMoreField', 1)
            ->assertCount('inputs', 2);
    }

    public function test_reset_form_after_submit(): void
    {
        Livewire::test(AddMenuItem::class)
            ->set('translationNames.en', 'Temp Item')
            ->set('itemPrice', 5)
            ->set('itemCategory', $this->category->id)
            ->set('menu', $this->menu->id)
            ->set('isAvailable', true)
            ->call('submitForm')
            ->assertSet('itemPrice', '');
    }
}
