<?php

namespace NoamanAhmed\ApiCrudGenerator\Commands;

use Illuminate\Console\Command;

class Init extends Command
{
    public $signature = 'api-crud-generator:Init';

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
    public function handle()
    {

    }
}
