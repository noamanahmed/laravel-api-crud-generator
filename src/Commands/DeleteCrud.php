<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DeleteCrud extends Command
{
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
    protected $description = 'This will delete all the generated files created using create command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $crudName = $this->argument('name');
        $snakedCrudName = Str::snake($crudName);
        $titleCrudName = Str::title($crudName);

        $filePaths = [
            'app/Enums/'.$crudName.'StatusEnum.php',
            'app/Exporters/'.$crudName.'Exporter.php',
            'app/Http/Controllers/Api/V1/'.$crudName.'Controller.php',
            'app/Http/Requests/'.$titleCrudName.'/',
            'app/Importers/'.$crudName.'Importer.php',
            'app/Models/'.$crudName.'.php',
            'app/Repositories/'.$crudName.'Repository.php',
            'app/Services/'.$crudName.'Service.php',
            'app/Transformers/'.$crudName.'CollectionTransformer.php',
            'app/Transformers/'.$crudName.'Transformer.php',
            'database/factories/'.$crudName.'Factory.php',
            'database/seeders/'.$crudName.'Seeder.php',
            'resources/lang/en/'.$crudName.'.php',
            'tests/Factories/'.$crudName.'Factory.php',
            'tests/Feature/Modules/'.$crudName.'/',
            'tests/Feature/Modules/'.$crudName.'/',
        ];
        foreach ($filePaths as $filePath) {
            if (File::exists($filePath)) {
                if (File::isDirectory($filePath)) {
                    File::deleteDirectory($filePath);
                } else {
                    File::delete($filePath);
                }
                $this->info("File '{$filePath}' deleted successfully.");

                continue;
            } else {
                $this->warn("The file path'{$filePath}' doesn't exist.");
            }
        }
        $this->deleteMigrations($crudName);
    }

    public function deleteMigrations($crudName)
    {
        $snakeCrudName = Str::snake(Str::plural($crudName)).'_table';
        $migrationPath = database_path('migrations');
        $migrationFiles = File::files($migrationPath);
        foreach ($migrationFiles as $file) {
            if (Str::contains($file->getFilename(), $snakeCrudName)) {
                // Delete the matching migration file
                File::delete($file->getRealPath());
                $this->info('Deleted migration file: '.$file->getFilename().PHP_EOL);
            }
        }
    }
}
