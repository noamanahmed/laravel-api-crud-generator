<?php

namespace NoamanAhmed\ApiCrudGenerator;

use NoamanAhmed\ApiCrudGenerator\Commands\CreateCrud;
use NoamanAhmed\ApiCrudGenerator\Commands\CreateCrudComponent;
use NoamanAhmed\ApiCrudGenerator\Commands\DeleteCrud;
use NoamanAhmed\ApiCrudGenerator\Commands\Init;
use NoamanAhmed\ApiCrudGenerator\Providers\CrudServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ApiCrudGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-api-crud-generator')
            ->hasConfigFile()

            // ->hasViews()
            ->hasCommand(Init::class)
            ->hasCommand(CreateCrud::class)
            ->hasCommand(CreateCrudComponent::class)
            ->hasCommand(DeleteCrud::class);

        $this->app->register(CrudServiceProvider::class);

    }

    public function bootingPackage()
    {
        $this->publishes([
            __DIR__.'/stubs' => resource_path('stubs/vendor/api-crud-generator'),
        ], 'api-crud-generator-stubs');
    }
}
