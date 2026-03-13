<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MenuItem;
use App\Models\MenuItemVariation;

class Recipe extends Model
{
    protected $fillable = [
        'menu_item_id',
        'menu_item_variation_id',
        'inventory_item_id',
        'quantity',
        'unit_id'
    ];

    protected $with = ['menuItemData'];

    public function menuItemData(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id')
            ->select(['id', 'item_name', 'image', 'preparation_time', 'item_category_id']);
    }

    public function menuItemVariation(): BelongsTo
    {
        return $this->belongsTo(MenuItemVariation::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getCategoryNameAttribute()
    {
        return $this->menuItemData?->category->getTranslation('category_name', user()->locale);
    }
}
