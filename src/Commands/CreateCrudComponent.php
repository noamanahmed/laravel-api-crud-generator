<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateCrudComponent extends Command
{
    protected $signature = 'api-crud-generator:crud-component {name} {component}';

    protected $description = 'Create a specific CRUD component like controller, request, service, etc.';

    public function handle()
    {
        $crudName = $this->argument('name');
        $component = strtolower($this->argument('component'));
        $snakedCrudName = Str::snake($crudName);

        $componentsMap = $this->getComponentMap($crudName);

        if (! isset($componentsMap[$component])) {
            $this->error("Unsupported component: '{$component}'");

            return 1;
        }

        $config = $componentsMap[$component];

        $fileName = $config['name'].($config['extension'] ?? '.php');
        $filePath = rtrim($config['path'], '/').'/'.$fileName;

        $this->makeDirectory($config['path']);

        $this->copyStub($config['stub'], $fileName, $config['path']);
        $this->replaceStubVariables($filePath);

        $this->info("Component '{$component}' generated successfully for {$crudName}.");

        return 0;
    }

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

    protected function replaceStubVariables($filePath)
    {
        $fileContent = File::get($filePath);
        $replaceVariablesArray = [
            'modelName' => $this->argument('name'),
            'modelNameTitle' => str($this->argument('name'))->snake()->headline()->toString(),
            'model' => str($this->argument('name'))->snake()->toString(),
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
}
