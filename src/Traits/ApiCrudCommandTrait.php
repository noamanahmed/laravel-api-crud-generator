<?php

namespace NoamanAhmed\ApiCrudGenerator\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait ApiCrudCommandTrait
{
    protected function copyStub($stubName, $stubCrudName, $destinationFolder)
    {
        $publishedStub = resource_path("stubs/vendor/api-crud-generator/{$stubName}.stub");
        $vendorStub = __DIR__."/../stubs/{$stubName}.stub";

        $sourceFilePath = file_exists($publishedStub) ? $publishedStub : $vendorStub;
        $destinationFilePath = "{$destinationFolder}/{$stubCrudName}";

        if (! File::exists($destinationFilePath)) {
            File::copy($sourceFilePath, $destinationFilePath);
            $this->info("Copied stub from '{$sourceFilePath}' to '{$destinationFilePath}'.");
        } else {
            $this->info("File already exists at '{$destinationFilePath}'.");
        }
    }

    protected function replaceStubVariables($filePath, $crudName)
    {
        $fileContent = File::get($filePath);
        $replaceVariablesArray = [
            'modelNameTitle' => str($crudName)->snake()->headline()->toString(),
            'modelName' => $crudName,
            'model' => str($crudName)->snake()->toString(),
        ];

        foreach ($replaceVariablesArray as $key => $value) {
            $fileContent = str_replace('{{ '.$key.' }}', $value, $fileContent);
        }

        File::put($filePath, $fileContent);
    }

    protected function makeDirectory($path)
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    public function getComponentMap(string $crudName): array
    {
        $snakedCrudName = Str::snake($crudName);

        return [
            'controller' => [
                'stub' => 'controller',
                'name' => "{$crudName}Controller",
                'path' => app_path('Http/Controllers'),
            ],
            'store-request' => [
                'stub' => 'store.request',
                'name' => 'StoreRequest',
                'path' => app_path("Http/Requests/{$crudName}"),
            ],
            'update-request' => [
                'stub' => 'update.request',
                'name' => 'UpdateRequest',
                'path' => app_path("Http/Requests/{$crudName}"),
            ],
            'import-request' => [
                'stub' => 'import.request',
                'name' => 'ImportRequest',
                'path' => app_path("Http/Requests/{$crudName}"),
            ],
            'export-request' => [
                'stub' => 'export.request',
                'name' => 'ExportRequest',
                'path' => app_path("Http/Requests/{$crudName}"),
            ],
            'analytics-request' => [
                'stub' => 'analytics.request',
                'name' => 'AnalyticsRequest',
                'path' => app_path("Http/Requests/{$crudName}"),
            ],
            'service' => [
                'stub' => 'service',
                'name' => "{$crudName}Service",
                'path' => app_path('Services'),
            ],
            'repository' => [
                'stub' => 'repository',
                'name' => "{$crudName}Repository",
                'path' => app_path('Repositories'),
            ],
            'transformer' => [
                'stub' => 'transformer',
                'name' => "{$crudName}Transformer",
                'path' => app_path('Transformers'),
            ],
            'collection-transformer' => [
                'stub' => 'transformer-collection',
                'name' => "{$crudName}CollectionTransformer",
                'path' => app_path('Transformers'),
            ],
            'exporter' => [
                'stub' => 'exporter',
                'name' => "{$crudName}Exporter",
                'path' => app_path('Exporters'),
            ],
            'importer' => [
                'stub' => 'importer',
                'name' => "{$crudName}Importer",
                'path' => app_path('Importers'),
            ],
            'enum' => [
                'stub' => 'enum',
                'name' => "{$crudName}StatusEnum",
                'path' => app_path('Enums'),
            ],
            'filter' => [
                'stub' => 'filter',
                'name' => "{$crudName}Filter",
                'path' => app_path('Filters'),
            ],
            'lang' => [
                'stub' => 'language',
                'name' => $snakedCrudName,
                'path' => base_path('resources/lang/en'),
                'extension' => '.php',
            ],
            'module_lang' => [
                'stub' => 'language',
                'name' => $snakedCrudName,
                'path' => base_path('resources/lang/en/modules'),
                'extension' => '.php',
            ],
        ];
    }

    public function stubsPath()
    {
        return base_path('stubs');
    }
}
