<?php

namespace NoamanAhmed\ApiCrudGenerator;

use Spatie\QueryBuilder\QueryBuilder;

/**
 * Interface BaseFilterContract
 * Contract for filter classes that apply filters using Spatie\QueryBuilder\QueryBuilder.
 */
interface BaseFilterContract
{
    /**
     * Apply filters to the given QueryBuilder instance.
     */
    public function apply(QueryBuilder $queryBuilder): QueryBuilder;
}
