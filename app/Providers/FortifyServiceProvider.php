<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Models\OnboardingStep;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\Facades\Auth;

class FortifyServiceProvider extends ServiceProvider
{

    use AppBoot;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {

            public function toResponse($request)
            {
                $user = User::find(user()->id);
                session(['user' => $user]);

                if ($user->hasRole('Super Admin')) {
                    return redirect(RouteServiceProvider::SUPERADMIN_HOME);
                }

                if ($user->hasRole('Admin_' . $user->restaurant_id)) {
                    $onboardingSteps = OnboardingStep::where('branch_id', $user->restaurant->branches->first()->id)->first();

                    if (
                        $onboardingSteps
                        && (
                            !$onboardingSteps->add_area_completed
                            || !$onboardingSteps->add_table_completed
                            || !$onboardingSteps->add_menu_completed
                            || !$onboardingSteps->add_menu_items_completed
                        )
                    ) {
                        return redirect(RouteServiceProvider::ONBOARDING_STEPS);
                    }
                }

                if (session()->has('url.intended')) {
                    return redirect()->intended();
                }

                $modules = [
                    ['module' => 'Dashboard', 'permission' => 'Show Reports', 'route' => 'dashboard'],
                    ['module' => 'Order', 'permission' => 'Create Order', 'route' => 'pos.index'],
                    ['module' => 'Order', 'permission' => 'Show Order', 'route' => 'orders.index'],
                    ['module' => 'KOT', 'permission' => 'Manage KOT', 'route' => 'kots.index'],
                    ['module' => 'Reservation', 'permission' => 'Show Reservation', 'route' => 'reservations.index'],
                    ['module' => 'Table', 'permission' => 'Show Table', 'route' => 'tables.index'],
                    ['module' => 'Area', 'permission' => 'Show Area', 'route' => 'areas.index'],
                    ['module' => 'Menu', 'permission' => 'Show Menu', 'route' => 'menus.index'],
                    ['module' => 'Menu Item', 'permission' => 'Show Menu Item', 'route' => 'menu-items.index'],
                    ['module' => 'Customer', 'permission' => 'Show Customer', 'route' => 'customers.index'],
                    ['module' => 'Staff', 'permission' => 'Show Staff Member', 'route' => 'staff.index'],
                    ['module' => 'Settings', 'permission' => 'Manage Settings', 'route' => 'settings.index'],
                ];

                $restaurantModules = restaurant_modules();

                foreach ($modules as $module) {
                    if ($module['module'] === 'Dashboard' || in_array($module['module'], $restaurantModules)) {
                        if (user_can($module['permission'])) {
                            return redirect()->route($module['route']);
                        }
                    }
                }

                return redirect(RouteServiceProvider::HOME);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Fortify::loginView(function () {
            $this->showInstall();

            $this->checkMigrateStatus();

//            if (!$this->isLegal()) {
//                if (!module_enabled('Subdomain')) {
//                    return redirect('verify-purchase');
//                }
//
//                // We will only show verify page for super-admin-login
//                // We will check it's opened on main or not
//                if (Str::contains(request()->url(), 'super-admin-login')) {
//                    return redirect('verify-purchase');
//                }
//            }

            return view('auth.login');
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request) {

            \Illuminate\Support\Facades\App::setLocale(session('locale') ?? global_setting()->locale ?? 'en');

            $rules = [
                'email' => 'required|email:rfc|regex:/(.+)@(.+)\.(.+)/i'
            ];

            $request->validate($rules);

            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                // Added for validation of account login in restaurant
                User::validateLoginActiveDisabled($user);

                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }

    public function checkMigrateStatus()
    {
        return check_migrate_status();
    }
}
