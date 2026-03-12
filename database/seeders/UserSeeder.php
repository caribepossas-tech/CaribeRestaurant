<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run($branch): void
    {
        User::withoutEvents(function () use ($branch) {

            if ($branch->restaurant->id == 1) {

                $admin = User::firstOrCreate(
                    ['email' => 'admin@example.com'],
                    [
                        'name' => 'John Doe',
                        'password' => bcrypt(123456),
                        'restaurant_id' => $branch->restaurant->id
                    ]
                );

                $waiter = User::firstOrCreate(
                    ['email' => 'waiter@example.com'],
                    [
                        'name' => 'Jaquelyn Battle',
                        'password' => bcrypt(123456),
                        'restaurant_id' => $branch->restaurant->id,
                        'branch_id' => $branch->id
                    ]
                );

                $adminRole = Role::where('name', 'Admin_'.$branch->restaurant_id)->first();
                $waiterRole = Role::where('name', 'Waiter_'.$branch->restaurant_id)->first();

                $admin->assignRole($adminRole);
                $waiter->assignRole($waiterRole);
        

            } else {
                $admin = User::firstOrCreate(
                    ['email' => $branch->restaurant->email],
                    [
                        'name' => fake()->name(),
                        'password' => bcrypt(123456),
                        'restaurant_id' => $branch->restaurant->id
                    ]
                );

                $waiter = User::firstOrCreate(
                    ['email' => fake()->unique()->safeEmail()],
                    [
                        'name' => fake()->name(),
                        'password' => bcrypt(123456),
                        'restaurant_id' => $branch->restaurant->id,
                        'branch_id' => $branch->id
                    ]
                );

                $adminRole = Role::where('name', 'Admin_'.$branch->restaurant_id)->first();
                $waiterRole = Role::where('name', 'Waiter_'.$branch->restaurant_id)->first();

                $admin->assignRole($adminRole);
                $waiter->assignRole($waiterRole);
            }

        });
    }

}
