<?php

namespace NoamanAhmed\ApiCrudGenerator\Providers;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use NoamanAhmed\ApiCrudGenerator\Router\ResourceRegistrar;

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

        app()->bind(BaseResourceRegistrar::class, function () {
            return new ResourceRegistrar(app()->make(Router::class));
        });

        Route::macro('apiCrudResource', function ($name, $controller, $options = []) {
            $registrar = app(BaseResourceRegistrar::class);

            $routes = [];
            if (method_exists($registrar, 'getResourceDefaults')) {
                $routes = $registrar->getResourceDefaults();
            }
            $permission = $options['permission'] ?? $name;
            $routeName = $options['name'] ?? $name;

            return Route::resource($name, $controller, array_merge([
                'only' => $routes,
            ], $options))
            ->names($routeName);
            // ->middleware('authorize.api:'.$permission);
        });
    }
}
