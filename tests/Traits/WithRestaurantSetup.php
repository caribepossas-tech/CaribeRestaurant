<?php

namespace Tests\Traits;

use App\Models\Branch;
use App\Models\Country;
use App\Models\Currency;
use App\Models\GlobalSetting;
use App\Models\ItemCategory;
use App\Models\LanguageSetting;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemVariation;
use App\Models\Package;
use App\Models\Restaurant;
use App\Models\User;

trait WithRestaurantSetup
{
    protected Restaurant $restaurant;
    protected Branch $branch;
    protected User $admin;
    protected Menu $menu;
    protected ItemCategory $category;

    protected function setUpRestaurant(): void
    {
        // Create base data
        $currency = Currency::firstOrCreate(
            ['currency_code' => 'USD'],
            ['currency_name' => 'US Dollar', 'currency_symbol' => '$', 'usd_price' => 1]
        );

        $country = Country::firstOrCreate(
            ['countries_code' => 'US'],
            ['countries_name' => 'United States', 'phonecode' => 1]
        );

        GlobalSetting::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'TestApp',
                'theme_hex' => '#f5be22',
                'theme_rgb' => '245, 190, 34',
                'default_currency_id' => $currency->id,
                'locale' => 'en',
            ]
        );

        LanguageSetting::firstOrCreate(
            ['language_code' => 'en'],
            ['language_name' => 'English', 'flag_code' => 'en', 'active' => 1]
        );

        $package = Package::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Test Package',
                'price' => 0,
                'branch_limit' => 5,
                'additional_features' => json_encode(['Order', 'Table Reservation']),
            ]
        );

        // Create restaurant
        $this->restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'hash' => 'test-restaurant-hash',
            'address' => '123 Test St',
            'phone_number' => '+1234567890',
            'email' => 'test@restaurant.com',
            'timezone' => 'America/New_York',
            'theme_hex' => '#A78BFA',
            'theme_rgb' => '167, 139, 250',
            'country_id' => $country->id,
            'currency_id' => $currency->id,
            'package_id' => $package->id,
            'stock_check_mode' => 'flexible',
        ]);

        // Create branch
        $this->branch = Branch::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Main Branch',
            'address' => '123 Test St',
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'branch_id' => $this->branch->id,
            'locale' => 'en',
        ]);

        // Set session for branch/restaurant scopes
        session([
            'restaurant' => $this->restaurant,
            'branch' => $this->branch,
        ]);

        // Create menu and category
        $this->category = ItemCategory::create([
            'category_name' => 'Test Category',
            'branch_id' => $this->branch->id,
        ]);

        $this->menu = Menu::create([
            'menu_name' => 'Test Menu',
            'branch_id' => $this->branch->id,
        ]);
    }

    protected function createMenuItem(array $overrides = []): MenuItem
    {
        return MenuItem::withoutGlobalScopes()->create(array_merge([
            'item_name' => 'Test Item',
            'price' => 10.00,
            'type' => 'veg',
            'menu_id' => $this->menu->id,
            'item_category_id' => $this->category->id,
            'branch_id' => $this->branch->id,
            'is_available' => true,
        ], $overrides));
    }

    protected function createMenuItemWithVariation(string $name = 'Item with Variation'): array
    {
        $menuItem = $this->createMenuItem(['item_name' => $name, 'price' => 0]);

        $variation = MenuItemVariation::create([
            'variation' => 'Large',
            'price' => 15.00,
            'menu_item_id' => $menuItem->id,
        ]);

        return [$menuItem, $variation];
    }
}
