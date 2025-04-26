<?php

namespace NoamanAhmed\Transformers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use NoamanAhmed\ApiCrudGenerator\Contracts\BaseTransformerContract;

class BaseTransformer implements BaseTransformerContract
{
    public Model|Collection|LengthAwarePaginator $resource;

    public function __construct() {}

    public function setResource(Model|Collection|LengthAwarePaginator $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    public function toArray()
    {
        return $this->resource->toArray();
    }

    public function toJson()
    {
        return $this->resource->toJson();
    }

    public function __invoke()
    {
        return $this->toArray();
    }

    public function buildPaginationMetaData($output, $resource)
    {
        $output['meta']['currentPage'] = $resource->currentPage();
        $output['meta']['perPage'] = $resource->perPage();
        $output['meta']['total'] = $resource->total();
        $output['meta']['lastPage'] = $resource->lastPage();

        return $output;
    }

    public function buildEnumData($enum, $value)
    {
        if (is_null($value)) {
            return [
                'value' => $value,
                'pretty_value' => '',
                'css_class' => '',
            ];

        }

        return [
            'value' => $value,
            'pretty_value' => $enum::getPrettyKeyfromValue($value),
            'css_class' => $enum::getColorType($value),
        ];
    }
}
