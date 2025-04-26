<?php

namespace NoamanAhmed\ApiCrudGenerator\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NoamanAhmed\ApiCrudGenerator\ApiCrudGenerator
 */
class ApiCrudGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NoamanAhmed\ApiCrudGenerator\ApiCrudGenerator::class;
    }
}
