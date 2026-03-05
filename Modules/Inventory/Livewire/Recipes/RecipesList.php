<?php

namespace Modules\Inventory\Livewire\Recipes;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Inventory\Entities\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Models\ItemCategory;

class RecipesList extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $category = '';
    public $sortBy = 'menu_items.item_name';
    public $perPage = 10;
    public $page = 1;
    public $showAddRecipe = false;
    public $isEditing = false;
    public $confirmDeleteRecipe = false;
    public $recipeToDelete = null;

    protected $queryString = [
        'page' => ['except' => 1],
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'sortBy' => ['except' => 'menu_items.item_name'],
    ];

    protected $listeners = [
        'recipeUpdated' => '$refresh',
        'closeAddRecipeModal' => 'closeModal'
    ];

    // Add watchers for real-time filtering
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function paginationView()
    {
        return 'vendor.livewire.tailwind';
    }

    public function render()
    {
        // Set MySQL to non-strict mode for this query
        DB::statement("SET SESSION sql_mode=''");

        $query = Recipe::query()
            ->join('menu_items', 'recipes.menu_item_id', '=', 'menu_items.id')
            ->join('item_categories', 'menu_items.item_category_id', '=', 'item_categories.id')
            ->select([
                'recipes.menu_item_id',
                'menu_items.item_name',
                'menu_items.image',
                'menu_items.preparation_time',
                'item_categories.category_name'
            ])
            ->where('menu_items.branch_id', branch()->id)
            ->where('menu_items.is_available', 1)
            ->when($this->search, function ($query) {
                $query->where('menu_items.item_name', 'like', '%' . $this->search . '%');
            })
            ->when($this->category, function ($query) {
                $query->where('item_categories.category_name', $this->category);
            })
            ->when($this->sortBy, function ($query) {
                switch ($this->sortBy) {
                    case 'name':
                        $query->orderBy('menu_items.item_name');
                        break;
                    case 'category':
                        $query->orderBy('item_categories.category_name')
                            ->orderBy('menu_items.item_name');
                        break;
                    case 'prep_time':
                        $query->orderBy('menu_items.preparation_time')
                            ->orderBy('menu_items.item_name');
                        break;
                    default:
                        $query->orderBy('menu_items.item_name');
                }
            })
            ->groupBy('recipes.menu_item_id');

        // Get total count for pagination
        $total = $query->get()->count();

        // Get paginated results
        $recipes = $query->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get()
            ->map(function ($recipe) {
                // Get all ingredients for this menu item
                $ingredients = Recipe::where('menu_item_id', $recipe->menu_item_id)
                    ->with(['inventoryItem', 'unit', 'menuItemData'])
                    ->get();

                return [
                    'menu_item' => [
                        'id' => $recipe->menu_item_id,
                        'name' => $recipe->item_name,
                        'image' => $recipe->menuItemData->item_photo_url,
                        'preparation_time' => $recipe->preparation_time,
                        'category' => $recipe->category_name
                    ],
                    'ingredients' => $ingredients->map(function ($ingredient) {
                        return [
                            'name' => $ingredient->inventoryItem->name,
                            'quantity' => $ingredient->quantity,
                            'unit' => $ingredient->unit->symbol
                        ];
                    }),
                    'ingredients_cost' => $ingredients->sum(function ($ingredient) {
                        return $ingredient->inventoryItem->unit_purchase_price * $ingredient->quantity;
                    })
                ];
            });

        // Create paginator instance
        $recipes = new LengthAwarePaginator(
            $recipes,
            $total,
            $this->perPage,
            $this->page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        // Get categories for filter dropdown
        $categories = ItemCategory::get();

        // Get statistics
        $totalRecipes = DB::table('recipes')
            ->join('menu_items', 'recipes.menu_item_id', '=', 'menu_items.id')
            ->where('menu_items.branch_id', branch()->id)
            ->where('menu_items.is_available', 1)
            ->distinct('menu_item_id')
            ->count('menu_item_id');

        $mainCourseCount = DB::table('recipes')
            ->join('menu_items', 'recipes.menu_item_id', '=', 'menu_items.id')
            ->join('item_categories', 'menu_items.item_category_id', '=', 'item_categories.id')
            ->where('menu_items.branch_id', branch()->id)
            ->where('menu_items.is_available', 1)
            ->where('item_categories.category_name', 'like', '%Main Course%')
            ->distinct('menu_item_id')
            ->count('menu_item_id');

        $avgPrepTime = DB::table('recipes')
            ->join('menu_items', 'recipes.menu_item_id', '=', 'menu_items.id')
            ->where('menu_items.branch_id', branch()->id)
            ->where('menu_items.is_available', 1)
            ->avg('menu_items.preparation_time');

            // Reset SQL mode back to default after query execution
        DB::statement("SET SESSION sql_mode=(SELECT @@global.sql_mode)");
        
        return view('inventory::livewire.recipes.recipes-list', [
            'recipes' => $recipes,
            'categories' => $categories,
            'totalRecipes' => $totalRecipes,
            'mainCoursesCount' => $mainCourseCount,
            'avgPrepTime' => round($avgPrepTime ?? 0)
        ]);
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'category', 'sortBy']);
        $this->resetPage();
    }

    public function addRecipe()
    {
        $this->isEditing = false;
        $this->showAddRecipe = true;
        $this->dispatch('showRecipeForm');
    }

    public function editRecipe($recipeId)
    {
        $this->isEditing = true;
        $this->showAddRecipe = true;
        $this->dispatch('editRecipe', $recipeId);
    }

    public function closeModal()
    {
        $this->showAddRecipe = false;
        $this->isEditing = false;
    }

    public function showDeleteRecipe($recipeId)
    {
        $this->recipeToDelete = $recipeId;
        $this->confirmDeleteRecipe = true;
    }

    public function deleteRecipe()
    {
        Recipe::where('menu_item_id', $this->recipeToDelete)->delete();

        $this->alert('success', __('inventory::modules.recipe.recipe_deleted'));
        $this->confirmDeleteRecipe = false;
        $this->recipeToDelete = null;
    }
}
