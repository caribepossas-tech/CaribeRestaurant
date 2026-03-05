<?php

namespace Modules\Inventory\Livewire\Recipes;

use Livewire\Component;
use Modules\Inventory\Entities\Recipe;
use Modules\Inventory\Entities\InventoryItem;
use Modules\Inventory\Entities\Unit;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class RecipeForm extends Component
{
    use LivewireAlert;
    public $showModal = false;
    public $isEditing = false;
    public $recipeId;
    public $menuItemId;
    public $ingredients = [];

    // Form properties
    public $selectedMenuItem;
    public $availableMenuItems;
    public $availableInventoryItems;
    public $availableUnits;

    protected $listeners = ['showRecipeForm', 'editRecipe'];

    protected function rules()
    {
        return [
            'menuItemId' => 'required|exists:menu_items,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.unit_id' => 'required|exists:units,id',
        ];
    }

    public function mount()
    {
        // Get menu items that don't have recipes
        $this->availableMenuItems = MenuItem::whereNotIn('id', function ($query) {
            $query->select('menu_item_id')
                ->from('recipes')
                ->distinct();
        })
            ->where('is_available', 1)
            ->where('branch_id', branch()->id)
            ->get(['id', 'item_name']);

        // Load inventory items with their units
        $this->availableInventoryItems = InventoryItem::with('unit')
            ->get(['id', 'name', 'unit_id']);

        $this->ingredients = [
            $this->getEmptyIngredient()
        ];
    }

    public function showRecipeForm()
    {
        // Refresh available menu items when showing form
        $this->availableMenuItems = MenuItem::whereNotIn('id', function ($query) {
            $query->select('menu_item_id')
                ->from('recipes')
                ->distinct();
        })
            ->where('is_available', 1)
            ->where('branch_id', branch()->id)
            ->get(['id', 'item_name']);

        $this->reset(['menuItemId', 'ingredients', 'isEditing', 'recipeId']);
        $this->ingredients = [
            $this->getEmptyIngredient()
        ];
        $this->showModal = true;
    }

    public function editRecipe($recipeId)
    {
        $this->isEditing = true;
        $this->recipeId = $recipeId;

        // Get the first recipe to get menu item details
        $recipe = Recipe::with(['inventoryItem', 'unit'])
            ->join('menu_items', 'recipes.menu_item_id', '=', 'menu_items.id')
            ->where('menu_items.id', $recipeId)
            ->where('menu_items.is_available', 1)
            ->where('menu_items.branch_id', branch()->id)
            ->select('recipes.*', 'menu_items.item_name')
            ->first();

        if (!$recipe) {
            return;
        }

        // Set the menu item ID
        $this->menuItemId = $recipe->menu_item_id;

        // Set available menu items to include only this menu item
        $this->availableMenuItems = collect([
            (object)[
                'id' => $recipe->menu_item_id,
                'item_name' => $recipe->item_name
            ]
        ]);

        // Get all ingredients for this menu item
        $this->ingredients = Recipe::where('menu_item_id', $recipeId)
            ->join('menu_items', 'recipes.menu_item_id', '=', 'menu_items.id')
            ->with(['inventoryItem', 'unit'])
            ->where('menu_items.branch_id', branch()->id)
            ->select('recipes.*', 'menu_items.item_name')
            ->get()
            ->map(function ($recipe) {
                return [
                    'inventory_item_id' => $recipe->inventory_item_id,
                    'quantity' => $recipe->quantity,
                    'unit_id' => $recipe->unit_id,
                ];
            })->toArray();

        $this->showModal = true;
    }

    public function addIngredient()
    {
        $this->ingredients[] = $this->getEmptyIngredient();
    }

    public function removeIngredient($index)
    {
        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);
    }

    public function save()
    {
        $this->validate();

        // Delete existing recipes if editing
        if ($this->isEditing) {
            Recipe::where('menu_item_id', $this->menuItemId)->delete();
        }

        // Create new recipes
        foreach ($this->ingredients as $ingredient) {
            Recipe::create([
                'menu_item_id' => $this->menuItemId,
                'inventory_item_id' => $ingredient['inventory_item_id'],
                'quantity' => $ingredient['quantity'],
                'unit_id' => $ingredient['unit_id'],
            ]);
        }

        $this->dispatch('recipeUpdated');
        // Update parent's showAddRecipe property
        $this->dispatch('closeAddRecipeModal');

        $this->alert('success', __('inventory::modules.recipe.recipe_saved'));
    }

    private function getEmptyIngredient()
    {
        return [
            'inventory_item_id' => '',
            'quantity' => '',
            'unit_id' => '',
        ];
    }

    public function updatedIngredients($value, $key)
    {
        // Check if the updated field is inventory_item_id
        if (str_contains($key, 'inventory_item_id')) {
            $index = explode('.', $key)[0];
            $inventoryItemId = $value;

            // Find the inventory item
            $inventoryItem = $this->availableInventoryItems->find($inventoryItemId);

            if ($inventoryItem) {
                // Set the unit_id to match the inventory item's unit
                $this->ingredients[$index]['unit_id'] = $inventoryItem->unit_id;
            }
        }
    }

    public function render()
    {
        return view('inventory::livewire.recipes.recipe-form', [
            'inventoryItemsWithUnits' => $this->availableInventoryItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit_symbol' => $item->unit->symbol
                ];
            })
        ]);
    }
}
