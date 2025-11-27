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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar route model binding para roles
        // Laravel espera 'role' pero nuestro modelo es 'Rol'
        \Illuminate\Support\Facades\Route::bind('role', function ($value) {
            return \App\Models\Rol::findOrFail($value);
        });
    }
}
