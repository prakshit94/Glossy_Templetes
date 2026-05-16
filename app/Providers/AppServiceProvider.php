<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Contracts\UserRepositoryInterface::class, \App\Repositories\UserRepository::class);
        $this->app->bind(\App\Contracts\AuthServiceInterface::class, \App\Services\AuthService::class);
        $this->app->bind(\App\Contracts\MfaServiceInterface::class, \App\Services\MfaService::class);

        // Inventory & Order services are singletons — one instance per request.
        // This prevents duplicate DB connections and is safe because both services
        // are stateless (all state lives in the database, not in memory).
        $this->app->singleton(\App\Services\InventoryService::class);
        $this->app->singleton(\App\Services\OrderService::class, function ($app) {
            return new \App\Services\OrderService(
                $app->make(\App\Services\InventoryService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class);

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Register Activity Log Listeners
        \Illuminate\Support\Facades\Event::listen(
            [\Illuminate\Auth\Events\Login::class, \Illuminate\Auth\Events\Logout::class, \Illuminate\Auth\Events\Failed::class],
            \App\Listeners\LogAuthenticationActivity::class
        );

        // Share customer-form data globally so the Add Customer modal in the
        // app layout has the data it needs on every authenticated page.
        \Illuminate\Support\Facades\View::composer('components.layout.header', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $view->with([
                    'globalCrops'           => \App\Models\Crop::where('status', 'active')->orderBy('name')->get(),
                    'globalIrrigationTypes' => \App\Models\IrrigationType::where('status', 'active')->orderBy('name')->get(),
                    'globalLandUnits'       => \App\Models\LandUnit::where('status', 'active')->orderBy('name')->get(),
                ]);
            } else {
                $view->with([
                    'globalCrops'           => collect(),
                    'globalIrrigationTypes' => collect(),
                    'globalLandUnits'       => collect(),
                ]);
            }
        });
    }
}
