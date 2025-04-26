<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;

class Init extends Command
{
    public $signature = 'api-crud-generator:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instanties the CRUD';

    protected $stubsPath = '/stubs';

    /**
     * Execute the console command.
     */
    public function handle() {

        $directories = [
            'tests/Factories',
            'app/Repositories',
            'app/Services',
            'app/Transformers',
            'app/Enums',
            'app/Exporters',
            'app/Importers',
            'resources/lang/en',
        ];
        
        foreach ($directories as $directory) {
            $this->makeDirectory(base_path($directory));
        }
    }

    protected function makeDirectory($path)
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
