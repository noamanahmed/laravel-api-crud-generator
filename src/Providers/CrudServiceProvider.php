<?php

namespace NoamanAhmed\ApiCrudGenerator\Providers;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider
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

        Route::macro('apiCrudResource', function ($name, $controller, $options = []) {
            $routes = app(BaseResourceRegistrar::class)->getResourceDefaults();
            $permission = $options['permission'] ?? $name;
            $routeName = $options['name'] ?? $name;

            return Route::resource($name, $controller, array_merge([
                'only' => $routes,
            ], $options))->names($routeName)->middleware('authorize.api:'.$permission);
        });
    }
}
