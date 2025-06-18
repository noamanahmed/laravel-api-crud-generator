<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use NoamanAhmed\ApiCrudGenerator\Traits\ApiCrudCommandTrait;

class DeleteCrud extends Command
{
    use ApiCrudCommandTrait;

    /**
     * This will delete all the generated files created using create command
     *
     * @var string
     */
    protected $signature = 'api-crud-generator:delete {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all the generated files created using the create command';

    public function handle()
    {
        $crudName = $this->argument('name');
        $componentMap = $this->getComponentMap($crudName);
        $titleCrudName = Str::title($crudName);

        foreach ($componentMap as $component => $config) {
            $path = rtrim($config['path'], '/');
            $fileName = $config['name'] . ($config['extension'] ?? '.php');
            $fullPath = "{$path}/{$fileName}";

            if (File::exists($fullPath)) {
                File::delete($fullPath);
                $this->info("Deleted file: {$fullPath}");
            } elseif (is_dir("{$path}/{$fileName}")) {
                File::deleteDirectory("{$path}/{$fileName}");
                $this->info("Deleted directory: {$path}/{$fileName}");
            } elseif (is_dir("{$path}/{$titleCrudName}")) {
                File::deleteDirectory("{$path}/{$titleCrudName}");
                $this->info("Deleted request directory: {$path}/{$titleCrudName}");
            } else {
                $this->warn("Not found: {$fullPath}");
            }
        }

        // Delete model
        $modelPath = app_path("Models/{$crudName}.php");
        if (File::exists($modelPath)) {
            File::delete($modelPath);
            $this->info("Deleted model: {$modelPath}");
        }

        // Delete migration files
        $this->deleteMigrations($crudName);

        // Delete related test files
        $this->deleteTestFiles($crudName);
    }

    protected function deleteMigrations(string $crudName): void
    {
        $table = Str::snake(Str::pluralStudly($crudName));
        $migrationFiles = File::files(database_path('migrations'));

        foreach ($migrationFiles as $file) {
            if (Str::contains($file->getFilename(), $table)) {
                File::delete($file->getPathname());
                $this->info("Deleted migration: {$file->getFilename()}");
            }
        }
    }

    protected function deleteTestFiles(string $crudName): void
    {
        $paths = [
            base_path("tests/Factories/{$crudName}Factory.php"),
            base_path("database/factories/{$crudName}Factory.php"),
            base_path("database/seeders/{$crudName}Seeder.php"),
            base_path("tests/Feature/Modules/{$crudName}"),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                if (File::isDirectory($path)) {
                    File::deleteDirectory($path);
                    $this->info("Deleted test directory: {$path}");
                } else {
                    File::delete($path);
                    $this->info("Deleted file: {$path}");
                }
            } else {
                $this->warn("Not found: {$path}");
            }
        }
    }
}
