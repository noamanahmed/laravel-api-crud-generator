<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

abstract class BaseCollectionTransformer extends BaseTransformer implements BaseCollectionTransformerContract
{
    abstract public function getEntityTransformer(): BaseTransformerContract;

    public function __construct()
    {
        parent::__construct();
    }

    public function toArray()
    {
        $output = [];
        $output['data'] = $this->resource['data'] ?? [];

        if (
            $this->resource instanceof Collection ||
            $this->resource instanceof SupportCollection
        ) {
            $output = [];
            foreach ($this->resource as $entity) {
                $entityTransformer = $this->getEntityTransformer();
                $transformer = (new $entityTransformer)->setResource($entity);
                $output[] = $transformer->toArray();
            }

            return $output;
        }

        foreach ($this->resource->items() as $entity) {
            $entityTransformer = $this->getEntityTransformer();
            $transformer = (new $entityTransformer)->setResource($entity);
            $output['data'][] = $transformer->toArray();
        }
        $output = $this->buildPaginationMetaData($output, $this->resource);

        return $output;
    }
}
