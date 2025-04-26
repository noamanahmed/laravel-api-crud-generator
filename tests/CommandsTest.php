<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('can run the api-crud-generator:init command successfully and create necessary directories', function () {
    // Arrange: Define the list of expected directories
    $expectedDirectories = [
        base_path('tests/Factories'),
        base_path('app/Repositories'),
        base_path('app/Services'),
        base_path('app/Transformers'),
        base_path('app/Enums'),
        base_path('app/Exporters'),
        base_path('app/Importers'),
        base_path('resources/lang/en'),
    ];

    // Act: Run the command
    $exitCode = Artisan::call('api-crud-generator:init');

    // Assert: Command exits successfully
    expect($exitCode)->toBe(0);

    // Assert: All expected directories exist
    foreach ($expectedDirectories as $directory) {
        expect(is_dir($directory))->toBeTrue();
    }
});

it('can run the api-crud-generator:create command successfully and generate CRUD files', function () {
    // Arrange
    $crudName = 'Post'; // or any dummy name
    $snakedCrudName = 'post';

    $expectedFiles = [
        app_path("Http/Controllers/{$crudName}Controller.php"),
        app_path("Services/{$crudName}Service.php"),
        app_path("Repositories/{$crudName}Repository.php"),
        app_path("Transformers/{$crudName}Transformer.php"),
        app_path("Transformers/{$crudName}CollectionTransformer.php"),
        app_path("Importers/{$crudName}Importer.php"),
        app_path("Exporters/{$crudName}Exporter.php"),
        app_path("Enums/{$crudName}StatusEnum.php"),
        base_path("tests/Feature/Modules/{$crudName}/{$crudName}CrudTest.php"),
        base_path("tests/Factories/{$crudName}Factory.php"),
        base_path("database/seeders/{$crudName}Seeder.php"),
        base_path("resources/lang/en/{$snakedCrudName}.php"),
        app_path("Http/Requests/{$crudName}/StoreRequest.php"),
        app_path("Http/Requests/{$crudName}/UpdateRequest.php"),
        app_path("Http/Requests/{$crudName}/ImportRequest.php"),
        app_path("Http/Requests/{$crudName}/ExportRequest.php"),
        app_path("Http/Requests/{$crudName}/AnalyticsRequest.php"),
    ];

    $exitCode = Artisan::call('api-crud-generator:init');

    // Act
    $exitCode = Artisan::call('api-crud-generator:create', [
        'name' => $crudName,
    ]);

    // Assert
    expect($exitCode)->toBe(0);

    foreach ($expectedFiles as $file) {
        $exists = File::exists($file);
        if (! $exists) {
            dd($file);
        }
        expect($exists)->toBeTrue();
    }
});

it('can run the api-crud-generator:delete command successfully', function () {
    $exitCode = Artisan::call('api-crud-generator:delete Post');

    expect($exitCode)->toBe(0);
});
