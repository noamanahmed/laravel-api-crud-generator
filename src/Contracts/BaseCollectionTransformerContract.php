<?php

namespace NoamanAhmed\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseCollectionTransformerContract
{
    public function setResource(Model|Collection|LengthAwarePaginator $resource);

    public function getEntityTransformer(): BaseTransformerContract;
}
