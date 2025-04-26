<?php

namespace NoamanAhmed\ApiCrudGenerator;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use NoamanAhmed\ApiCrudGenerator\Commands\CreateCrud;
use NoamanAhmed\ApiCrudGenerator\Commands\DeleteCrud;
use NoamanAhmed\ApiCrudGenerator\Providers\CrudServiceProvider;

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
            ->publishesServiceProvider(CrudServiceProvider::class)

            // ->hasViews()
            ->hasCommand(CreateCrud::class)
            ->hasCommand(DeleteCrud::class);
    }
}
