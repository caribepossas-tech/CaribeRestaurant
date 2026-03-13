<?php

namespace Modules\Inventory\Livewire\Recipes;

use Livewire\Component;
use Modules\Inventory\Entities\Recipe;
use Modules\Inventory\Entities\InventoryItem;
use Modules\Inventory\Entities\Unit;
use App\Models\MenuItem;
use App\Models\MenuItemVariation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class RecipeForm extends Component
{
    use LivewireAlert;
    public $showModal = false;
    public $isEditing = false;
    public $recipeId;
    public $menuItemId;
    public $variationId = null;
    public $ingredients = [];

    // Form properties
    public $selectedMenuItem;
    public $availableMenuItems;
    public $availableVariations = [];
    public $availableInventoryItems;
    public $availableUnits;

    protected $listeners = ['showRecipeForm', 'editRecipe'];

    protected function rules()
    {
        return [
            'menuItemId'  => 'required|exists:menu_items,id',
            'variationId' => 'nullable|exists:menu_item_variations,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'ingredients.*.quantity'          => 'required|numeric|min:0',
            'ingredients.*.unit_id'           => 'required|exists:units,id',
        ];
    }

    public function mount()
    {
        $this->availableMenuItems = MenuItem::where('is_available', 1)
            ->where('branch_id', branch()->id)
            ->get(['id', 'item_name']);

        $this->availableInventoryItems = InventoryItem::with('unit')
            ->get(['id', 'name', 'unit_id']);

        $this->ingredients = [
            $this->getEmptyIngredient()
        ];
    }

    public function showRecipeForm()
    {
        $this->availableMenuItems = MenuItem::where('is_available', 1)
            ->where('branch_id', branch()->id)
            ->get(['id', 'item_name']);

        $this->reset(['menuItemId', 'variationId', 'availableVariations', 'ingredients', 'isEditing', 'recipeId']);
        $this->ingredients = [
            $this->getEmptyIngredient()
        ];
        $this->showModal = true;
    }

    public function updatedMenuItemId($value)
    {
        $this->variationId = null;
        if ($value) {
            $this->availableVariations = MenuItemVariation::where('menu_item_id', $value)
                ->get(['id', 'variation'])
                ->map(fn($v) => ['id' => $v->id, 'name' => $v->variation])
                ->toArray();
        } else {
            $this->availableVariations = [];
        }
    }

    public function editRecipe($recipeId, $variationId = null)
    {
        $this->isEditing = true;
        $this->recipeId  = $recipeId;

        $hasVariationColumn = Schema::hasColumn('recipes', 'menu_item_variation_id');

        $query = Recipe::with(['inventoryItem', 'unit'])
            ->join('menu_items', 'recipes.menu_item_id', '=', 'menu_items.id')
            ->where('menu_items.id', $recipeId)
            ->where('menu_items.is_available', 1)
            ->where('menu_items.branch_id', branch()->id)
            ->select('recipes.*', 'menu_items.item_name');

        if ($hasVariationColumn) {
            if ($variationId) {
                $query->where('recipes.menu_item_variation_id', $variationId);
            } else {
                $query->whereNull('recipes.menu_item_variation_id');
            }
        }

        $recipe = $query->first();

        if (!$recipe) {
            return;
        }

        $this->menuItemId  = $recipe->menu_item_id;
        $this->variationId = $recipe->menu_item_variation_id;

        $this->availableMenuItems = collect([
            (object)['id' => $recipe->menu_item_id, 'item_name' => $recipe->item_name]
        ]);

        $this->availableVariations = MenuItemVariation::where('menu_item_id', $this->menuItemId)
            ->get(['id', 'variation'])
            ->map(fn($v) => ['id' => $v->id, 'name' => $v->variation])
            ->toArray();

        $ingredientsQuery = Recipe::where('menu_item_id', $recipeId)
            ->join('menu_items', 'recipes.menu_item_id', '=', 'menu_items.id')
            ->with(['inventoryItem', 'unit'])
            ->where('menu_items.branch_id', branch()->id)
            ->select('recipes.*', 'menu_items.item_name');

        if ($hasVariationColumn) {
            if ($variationId) {
                $ingredientsQuery->where('recipes.menu_item_variation_id', $variationId);
            } else {
                $ingredientsQuery->whereNull('recipes.menu_item_variation_id');
            }
        }

        $this->ingredients = $ingredientsQuery->get()
            ->map(fn($r) => [
                'inventory_item_id' => $r->inventory_item_id,
                'quantity'          => $r->quantity,
                'unit_id'           => $r->unit_id,
            ])->toArray();

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

        $hasVariationColumn = Schema::hasColumn('recipes', 'menu_item_variation_id');

        // Delete existing recipes for this menu item + variation combination
        $deleteQuery = Recipe::where('menu_item_id', $this->menuItemId);
        if ($hasVariationColumn) {
            if ($this->variationId) {
                $deleteQuery->where('menu_item_variation_id', $this->variationId);
            } else {
                $deleteQuery->whereNull('menu_item_variation_id');
            }
        }
        $deleteQuery->delete();

        // Create new recipes
        $recipeData = ['menu_item_id' => $this->menuItemId];
        if ($hasVariationColumn) {
            $recipeData['menu_item_variation_id'] = $this->variationId ?: null;
        }
        foreach ($this->ingredients as $ingredient) {
            Recipe::create(array_merge($recipeData, [
                'inventory_item_id' => $ingredient['inventory_item_id'],
                'quantity'          => $ingredient['quantity'],
                'unit_id'           => $ingredient['unit_id'],
            ]));
        }

        $this->dispatch('recipeUpdated');
        $this->dispatch('closeAddRecipeModal');

        $this->alert('success', __('inventory::modules.recipe.recipe_saved'));
    }

    private function getEmptyIngredient()
    {
        return [
            'inventory_item_id' => '',
            'quantity'          => '',
            'unit_id'           => '',
        ];
    }

    public function updatedIngredients($value, $key)
    {
        if (str_contains($key, 'inventory_item_id')) {
            $index = explode('.', $key)[0];

            $inventoryItem = $this->availableInventoryItems->find($value);
            if ($inventoryItem) {
                $this->ingredients[$index]['unit_id'] = $inventoryItem->unit_id;
            }
        }
    }

    public function render()
    {
        return view('inventory::livewire.recipes.recipe-form', [
            'inventoryItemsWithUnits' => $this->availableInventoryItems->map(fn($item) => [
                'id'          => $item->id,
                'name'        => $item->name,
                'unit_symbol' => $item->unit->symbol,
            ]),
        ]);
    }
}
