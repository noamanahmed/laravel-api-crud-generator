<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RefreshCrudComponent extends Command
{
    protected $signature = 'api-crud-generator:refresh-component {component}';
    protected $description = 'Refresh a specific component (controller, repository, etc.) for all detected CRUDs';

    public function handle()
    {
        $component = strtolower($this->argument('component'));

        // Reuse the component config from the original command
        $componentMap = (new \NoamanAhmed\ApiCrudGenerator\Commands\CreateCrudComponent())->getComponentMap('Placeholder');

        if (!isset($componentMap[$component])) {
            $this->error("Unsupported component: '{$component}'");
            return 1;
        }

        $configTemplate = $componentMap[$component];
        $crudNames = $this->getCrudNames(); // e.g. from Models or Controllers

        foreach ($crudNames as $crudName) {
            $componentName = str_replace('Placeholder', $crudName, $configTemplate['name']);
            $componentPath = rtrim(str_replace('Placeholder', $crudName, $configTemplate['path']), '/');
            $fileName = $componentName . ($configTemplate['extension'] ?? '.php');
            $filePath = "{$componentPath}/{$fileName}";

            // Confirm before overwrite
            if (File::exists($filePath)) {
                if (!$this->confirm("Overwrite {$filePath}?", false)) {
                    continue;
                }
            }

            $this->makeDirectory($componentPath);
            $this->copyStub($component, $fileName, $componentPath);
            $this->replaceStubVariables($filePath, $crudName);

            $this->info("Updated {$component} for {$crudName}.");
        }

        return 0;
    }

    protected function getCrudNames(): array
    {
        $modelsPath = app_path('Models');
        if (!File::exists($modelsPath)) return [];

        return collect(File::files($modelsPath))
            ->filter(fn ($file) => Str::endsWith($file->getFilename(), '.php'))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->toArray();
    }

    protected function makeDirectory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    protected function copyStub($stubName, $fileName, $destinationFolder)
    {
        $publishedStub = resource_path("stubs/vendor/api-crud-generator/{$stubName}.stub");
        $vendorStub = __DIR__ . "/../stubs/{$stubName}.stub";
        $sourceFilePath = file_exists($publishedStub) ? $publishedStub : $vendorStub;
        $destinationFilePath = "{$destinationFolder}/{$fileName}";

        File::copy($sourceFilePath, $destinationFilePath);
    }

    protected function replaceStubVariables($filePath, $crudName)
    {
        $fileContent = File::get($filePath);
        $replacements = [
            'modelName' => $crudName,
            'model' => strtolower($crudName),
        ];

        foreach ($replacements as $key => $value) {
            $fileContent = str_replace('{{ ' . $key . ' }}', $value, $fileContent);
        }

        File::put($filePath, $fileContent);
    }
}
