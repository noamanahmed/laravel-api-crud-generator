<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use NoamanAhmed\ApiCrudGenerator\Traits\ApiCrudCommandTrait;

class CreateCrud extends Command
{
    use ApiCrudCommandTrait;

    public $signature = 'api-crud-generator:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a crud';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $crudName = $this->argument('name');
            $snakedCrudName = Str::snake($crudName);
            $crudPath = "Feature/Modules/{$crudName}";

            Artisan::call('make:model', [
                'name' => $crudName,
            ]);

            Artisan::call('make:migration', [
                'name' => 'create'.str($crudName)->plural().'_table',
                '-n' => true,
            ]);

            Artisan::call('make:factory', [
                'name' => "{$crudName}Factory",
                '--model' => $crudName,
            ]);

            Artisan::call('make:seeder', [
                'name' => "{$crudName}Seeder",
            ]);

            $testPath = base_path("tests/{$crudPath}");
            $this->makeDirectory($testPath);
            $this->makeDirectory(base_path('app/Http/Requests/'.$crudName));

            $this->copyStub('controller', $crudName.'Controller', base_path('app/Http/Controllers/'));
            $this->copyStub('store.request', 'StoreRequest', base_path('app/Http/Requests/'.$crudName));
            $this->copyStub('update.request', 'UpdateRequest', base_path('app/Http/Requests/'.$crudName));
            $this->copyStub('import.request', 'ImportRequest', base_path('app/Http/Requests/'.$crudName));
            $this->copyStub('export.request', 'ExportRequest', base_path('app/Http/Requests/'.$crudName));
            $this->copyStub('analytics.request', 'AnalyticsRequest', base_path('app/Http/Requests/'.$crudName));
            $this->copyStub('test.pest', $crudName.'CrudTest', base_path("tests/Feature/Modules/{$crudName}"));
            $this->copyStub('test.factory', $crudName.'Factory', base_path('tests/Factories'));
            $this->copyStub('repository', $crudName.'Repository', base_path('app/Repositories'));
            $this->copyStub('service', $crudName.'Service', base_path('app/Services'));
            $this->copyStub('transformer', $crudName.'Transformer', base_path('app/Transformers'));
            $this->copyStub('exporter', $crudName.'Exporter', base_path('app/Exporters'));
            $this->copyStub('importer', $crudName.'Importer', base_path('app/Importers'));
            $this->copyStub('transformer-collection', $crudName.'CollectionTransformer', base_path('app/Transformers'));
            $this->copyStub('enum', $crudName.'StatusEnum', base_path('app/Enums'));
            $this->copyStub('language', $snakedCrudName, base_path('resources/lang/en'));
            $this->copyStub('filter', $crudName.'Filter', base_path('app/Filters'));

            $this->replaceStubVariables(app_path("Http/Controllers/{$crudName}Controller.php"), $crudName);
            $this->replaceStubVariables(app_path("Services/{$crudName}Service.php"), $crudName);
            $this->replaceStubVariables(app_path("Repositories/{$crudName}Repository.php"), $crudName);
            $this->replaceStubVariables(app_path("Transformers/{$crudName}Transformer.php"), $crudName);
            $this->replaceStubVariables(app_path("Transformers/{$crudName}CollectionTransformer.php"), $crudName);
            $this->replaceStubVariables(app_path("Importers/{$crudName}Importer.php"), $crudName);
            $this->replaceStubVariables(app_path("Exporters/{$crudName}Exporter.php"), $crudName);
            $this->replaceStubVariables(app_path("Enums/{$crudName}StatusEnum.php"), $crudName);
            $this->replaceStubVariables(base_path("tests/Feature/Modules/{$crudName}/{$crudName}CrudTest.php"), $crudName);
            $this->replaceStubVariables(base_path("tests/Factories/{$crudName}Factory.php"), $crudName);
            $this->replaceStubVariables(base_path("database/seeders/{$crudName}Seeder.php"), $crudName);
            $this->replaceStubVariables(base_path("resources/lang/en/{$snakedCrudName}.php"), $crudName);
            $this->replaceStubVariables(base_path('app/Http/Controllers/'.$crudName.'Controller.php'), $crudName);
            $this->replaceStubVariables(base_path('app/Http/Requests/'.$crudName.'/StoreRequest.php'), $crudName);
            $this->replaceStubVariables(base_path('app/Http/Requests/'.$crudName.'/UpdateRequest.php'), $crudName);
            $this->replaceStubVariables(base_path('app/Http/Requests/'.$crudName.'/ImportRequest.php'), $crudName);
            $this->replaceStubVariables(base_path('app/Http/Requests/'.$crudName.'/ExportRequest.php'), $crudName);
            $this->replaceStubVariables(base_path('app/Http/Requests/'.$crudName.'/AnalyticsRequest.php'), $crudName);
            $this->replaceStubVariables(app_path("Filters/{$crudName}Filter.php"), $crudName);

            $this->info('CRUD files generated successfully.');

            return 0;
            // code...
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    protected function copyStub($stubName, $stubCrudName, $destinationFolder)
    {

        // Check if the user has published the stubs
        $publishedStub = resource_path("stubs/vendor/api-crud-generator/{$stubName}.stub");
        $vendorStub = __DIR__."/../stubs/{$stubName}.stub";

        // If the published stub exists, use it; otherwise, fall back to the default stub path in your package
        $sourceFilePath = file_exists($publishedStub) ? $publishedStub : $vendorStub;

        $destinationFilePath = "{$destinationFolder}/{$stubCrudName}.php";

        if (! File::exists($destinationFilePath)) {
            File::copy($sourceFilePath, $destinationFilePath);
            $this->info("File '{$sourceFilePath}' copied successfully to '{$destinationFilePath}'.");
        } else {
            $this->info("The file '{$sourceFilePath}' already exists in '{$destinationFilePath}'.");
        }
    }
}
