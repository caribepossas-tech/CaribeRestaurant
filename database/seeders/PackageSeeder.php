<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Package;
use App\Models\GlobalCurrency;
use Illuminate\Database\Seeder;
use App\Enums\PackageType;

class PackageSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Fetch the currency ID
        $currencyID = GlobalCurrency::first()->id;
        $modules = Module::all();

        // Create the Default package
        $package = Package::updateOrCreate(
            ['package_name' => 'Default'],
            [
                'description' => 'Its a default package and cannot be deleted',
                'currency_id' => $currencyID,
                'monthly_status' => 0,
                'annual_status' => 0,
                'annual_price' => null,
                'monthly_price' => null,
                'price' => 0,
                'is_free' => 1,
                'billing_cycle' => 12,
                'sort_order' => 1,
                'is_private' => 0,
                'is_recommended' => 0,
                'package_type' => PackageType::DEFAULT,
            ]
        );

        // Assign all modules to the default package
        $package->modules()->sync($modules->pluck('id')->toArray());

        // Create a Subscription package
        $subscriptionPackage = Package::updateOrCreate(
            ['package_name' => 'Subscription Package'],
            [
                'description' => 'This is a subscription package',
                'currency_id' => $currencyID,
                'monthly_status' => 1,
                'annual_status' => 1,
                'annual_price' => 100,
                'monthly_price' => 10,
                'price' => 0,
                'is_free' => 0,
                'billing_cycle' => 10,
                'sort_order' => 2,
                'is_private' => 0,
                'is_recommended' => 1,
                'package_type' => PackageType::STANDARD,
            ]
        );

        // Assign all modules to the subscription package
        $subscriptionPackage->modules()->sync($modules->pluck('id')->toArray());

        // Create a Lifetime package
        $lifetimePackage = Package::updateOrCreate(
            ['package_name' => 'Life Time'],
            [
                'description' => 'This is a lifetime access package',
                'currency_id' => $currencyID,
                'monthly_status' => 0,
                'annual_status' => 0,
                'annual_price' => null,
                'monthly_price' => null,
                'price' => 199,
                'is_free' => 0,
                'billing_cycle' => 0,
                'sort_order' => 3,
                'is_private' => 0,
                'is_recommended' => 1,
                'additional_features' => json_encode(Package::ADDITIONAL_FEATURES),
                'package_type' => PackageType::LIFETIME,
            ]
        );

        // Assign all modules to the lifetime package
        $lifetimePackage->modules()->sync($modules->pluck('id')->toArray());

        // Create a Private package
        $privatePackage = Package::updateOrCreate(
            ['package_name' => 'Private Package'],
            [
                'description' => 'This is a private package',
                'price' => 0,
                'currency_id' => $currencyID,
                'monthly_status' => 1,
                'annual_status' => 1,
                'annual_price' => 50,
                'monthly_price' => 5,
                'is_free' => 0,
                'billing_cycle' => 12,
                'sort_order' => 4,
                'is_private' => 1,
                'is_recommended' => 0,
                'package_type' => PackageType::STANDARD,
            ]
        );

        // Assign all modules to the private package
        $privatePackage->modules()->sync($modules->pluck('id')->toArray());


        // Create a Trial package
        $trialPackage = Package::updateOrCreate(
            ['package_name' => 'Trial Package'],
            [
                'description' => 'This is a trial package',
                'currency_id' => $currencyID,
                'monthly_status' => 0,
                'annual_status' => 0,
                'annual_price' => null,
                'monthly_price' => null,
                'price' => 0,
                'is_free' => 1,
                'billing_cycle' => 0,
                'sort_order' => null,
                'is_private' => 0,
                'is_recommended' => 0,
                'package_type' => PackageType::TRIAL,
                'additional_features' => json_encode(Package::ADDITIONAL_FEATURES),
                'trial_days' => 30,
                'trial_status' => 1,
                'trial_notification_before_days' => 5,
                'trial_message' => '30 Days Free Trial',
            ]
        );

        // Assign all modules to the trial package
        $trialPackage->modules()->sync($modules->pluck('id')->toArray());
    }

}
