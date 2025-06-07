<?php

namespace NoamanAhmed\ApiCrudGenerator;

use Spatie\QueryBuilder\QueryBuilder;
use NoamanAhmed\ApiCrudGenerator\Exceptions\UnAllowedFilterException;

/**
 * Class BaseFilter
 * Abstract class that implements the BaseFilterContract and provides dynamic filtering capabilities
 * with support for allowed checks before applying filters.
 */
abstract class BaseFilter implements BaseFilterContract
{
    /**
     * Apply filters to the given QueryBuilder instance.
     *
     * Iterates over all filter methods (methods starting with 'filter'), and if corresponding
     * 'allowed' method exists (filterXXXAllowed), it is called before the filter method.
     * The allowed method can throw UnAllowedFilterException to block the filter.
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     *
     * @throws UnAllowedFilterException
     */
    public function apply(QueryBuilder $queryBuilder): QueryBuilder
    {
        // Get all methods of the current class
        $methods = get_class_methods($this);

        foreach ($methods as $method) {
            if (strpos($method, 'filter') !== 0) {
                // Not a filter method, skip
                continue;
            }

            // Compose the allowed method name. e.g. filterByNameAllowed for filterByName
            $allowedMethod = $method . 'Allowed';

            // Prepare to get reflection parameters for current filter method
            $reflection = new \ReflectionMethod($this, $method);
            $params = $reflection->getParameters();

            // We'll collect arguments for allowed and filter methods
            // The first required param is QueryBuilder, which we have
            // Remaining params for filter method will be null (or could be improved with actual args)
            $filterArgs = [$queryBuilder];
            for ($i = 1; $i < count($params); $i++) {
                $filterArgs[] = null; 
            }

            $allowed = true;
            // Call allowed method if exists, with the same parameters as the filter method
            if (method_exists($this, $allowedMethod)) {
                // Get Reflection of allowed method and its parameters
                $allowedReflection = new \ReflectionMethod($this, $allowedMethod);
                $allowedParams = $allowedReflection->getParameters();

                // Prepare args for allowed method - only fill what is compatible
                // We'll pass $queryBuilder and any other nulls, but allowed methods typically should only rely on $queryBuilder or smaller args
                $allowedArgs = [$queryBuilder];
                for ($i = 1; $i < count($allowedParams); $i++) {
                    $allowedArgs[] = null;
                }

                // Call allowed method, if it throws UnAllowedFilterException it propagates
                $allowed = $this->$allowedMethod(...$allowedArgs);
            }

            if($allowed === false)
            {
                // If allowed method returns false, we'll skip this filter
                continue;
            }
            // Apply the actual filter
            $queryBuilder = $this->$method(...$filterArgs);
        }

        return $queryBuilder;
    }
}

