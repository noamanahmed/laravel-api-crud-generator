<?php

namespace NoamanAhmed\Transformers;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseTransformerContract{
    public function setResource(Model|Collection|LengthAwarePaginator $resource);
    public function toArray();
    public function toJson();
}
