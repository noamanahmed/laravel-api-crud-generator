<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CrudStatus extends Command
{
    protected $signature = 'api-crud-generator:crud-status';
    protected $description = 'List installed CRUDs where model, controller, repository, and service exist';

    public function handle()
    {
        $modelsPath = app_path('Models');
        $controllersPath = app_path('Http/Controllers');
        $repositoriesPath = app_path('Repositories');
        $servicesPath = app_path('Services');

        $crudCandidates = $this->getCrudNames($modelsPath);

        $installedCruds = [];

        foreach ($crudCandidates as $crudName) {
            $modelExists = $this->checkExists("{$modelsPath}/{$crudName}.php");
            $controllerExists = $this->checkExists("{$controllersPath}/{$crudName}Controller.php");
            $repositoryExists = $this->checkExists("{$repositoriesPath}/{$crudName}Repository.php");
            $serviceExists = $this->checkExists("{$servicesPath}/{$crudName}Service.php");

            if ($modelExists && $controllerExists && $repositoryExists && $serviceExists) {
                $installedCruds[] = $crudName;
            }
        }

        if (empty($installedCruds)) {
            $this->info("No complete CRUDs found.");
        } else {
            $this->info("Installed CRUDs:");
            foreach ($installedCruds as $crud) {
                $this->line("- {$crud}");
            }
        }

        return 0;
    }

    protected function getCrudNames($modelsPath): array
    {
        if (!File::exists($modelsPath)) {
            return [];
        }

        return collect(File::files($modelsPath))
            ->filter(fn ($file) => Str::endsWith($file->getFilename(), '.php'))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->values()
            ->toArray();
    }

    protected function checkExists($path): bool
    {
        return File::exists($path);
    }
}
