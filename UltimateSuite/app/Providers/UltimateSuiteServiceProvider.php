<?php

namespace Pterodactyl\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class UltimateSuiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->loadRoutes();
    }

    /**
     * Load the extension routes.
     */
    protected function loadRoutes()
    {
        Route::middleware(['web', 'auth', 'csrf'])
            ->group(base_path('routes/extensions/ultimate_suite.php'));
    }
}
