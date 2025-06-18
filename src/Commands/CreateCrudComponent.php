<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use NoamanAhmed\ApiCrudGenerator\Traits\ApiCrudCommandTrait;

class CreateCrudComponent extends Command
{
    use ApiCrudCommandTrait;

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
        $this->replaceStubVariables($filePath,$crudName);

        $this->info("Component '{$component}' generated successfully for {$crudName}.");

        return 0;
    }

    
}
