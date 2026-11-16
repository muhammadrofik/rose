<?php

namespace App\Providers;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Role Gates
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('kasir', fn (User $user) => $user->isKasir());

        // Functional Gates
        Gate::define('manage-products', fn (User $user) => $user->isAdmin());
        Gate::define('manage-inventory', fn (User $user) => $user->isAdmin());
        Gate::define('view-hpp', fn (User $user) => $user->isAdmin());
        Gate::define('view-profit', fn (User $user) => $user->isAdmin());
        Gate::define('view-reports', fn (User $user) => $user->isAdmin());
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('manage-settings', fn (User $user) => $user->isAdmin());

        Gate::define('create-sale', fn (User $user) => $user->is_active);
        Gate::define('view-sale', function (User $user, Sale $sale) {
            return $user->isAdmin() || $sale->user_id === $user->id;
        });
    }
}
