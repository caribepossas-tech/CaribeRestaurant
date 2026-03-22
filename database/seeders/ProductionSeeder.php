<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Country;
use App\Models\Restaurant;
use App\Models\RestaurantPayment;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the production database with essential data only.
     * Usage: php artisan db:seed --class=ProductionSeeder
     */
    public function run(): void
    {
        $this->command->info('Seeding production data...');

        // Clear global setting cache to avoid issues with Boot process caching an empty model
        cache()->forget('global_setting');

        // 1. Global data (countries, currencies, settings)
        $this->call(CountrySeeder::class);
        $this->call(GlobalCurrencySeeder::class);
        $this->call(GlobalSettingSeeder::class);

        // 2. System modules and permissions
        $this->call(ModuleSeeder::class);
        $this->call(PackageSeeder::class);
        $this->call(PermissionSeeder::class);

        // 3. Superadmin user
        $this->call(SuperadminSeeder::class);

        // 4. Languages and email config
        $this->call(LanguageSettingSeeder::class);
        $this->call(EmailSettingSeeder::class);
        $this->call(SuperadminPaymentGatewaySeeder::class);
        $this->call(PusherSettinSeeder::class);

        // 5. Production restaurant
        $this->seedProductionRestaurant();

        // 6. Inventory module
        $this->call(\Modules\Inventory\Database\Seeders\InventoryDatabaseSeeder::class);

        $this->command->info('Production seeding completed!');
    }

    private function seedProductionRestaurant(): void
    {
        if (Restaurant::count() > 0) {
            $this->command->info('Restaurant already exists. Skipping restaurant creation...');
            $restaurant = Restaurant::first();
        } else {
            $country = Country::where('countries_code', 'CO')->first()
                ?? Country::where('countries_code', 'US')->first();

            $restaurant = new Restaurant();
            $restaurant->name = 'CaribePos Restaurant';
            $restaurant->address = 'Colombia';
            $restaurant->phone_number = '+57';
            $restaurant->timezone = 'America/Bogota';
            $restaurant->theme_hex = '#f5be22';
            $restaurant->theme_rgb = '245, 190, 34';
            $restaurant->email = 'admin@caribeposrestaurant.online';
            $restaurant->country_id = $country->id ?? 1;
            $restaurant->package_id = 1;
            $restaurant->package_type = 'annual';
            $restaurant->license_type = 'paid';
            $restaurant->about_us = Restaurant::ABOUT_US_DEFAULT_TEXT;
            $restaurant->save();

            $this->command->info('Restaurant created: ' . $restaurant->name);

            // Create main branch
            $branch = new Branch();
            $branch->restaurant_id = $restaurant->id;
            $branch->name = 'Principal';
            $branch->address = 'Colombia';
            $branch->saveQuietly();
            $this->call(OnboardingSeeder::class, false, ['branch' => $branch]);

            $this->command->info('Branch created: ' . $branch->name);
        }

        // Seed restaurant essentials
        $branch = $restaurant->branches->first() ?? Branch::where('restaurant_id', $restaurant->id)->first();

        $this->call(PaymentSettingSeeder::class, false, ['restaurant' => $restaurant]);
        $this->call(TaxSeeder::class, false, ['restaurant' => $restaurant]);
        $this->call(RoleSeeder::class, false, ['restaurant' => $restaurant]);
        $this->call(UserSeeder::class, false, ['branch' => $branch]);
        $this->call(ReservationSettingsSeeder::class, false, ['branch' => $branch]);

        // Mark as paid
        RestaurantPayment::firstOrCreate(
            ['restaurant_id' => $restaurant->id],
            [
                'payment_date_time' => now()->toDateTimeString(),
                'package_id' => 1,
                'amount' => 0,
                'status' => 'paid',
            ]
        );
    }
}
