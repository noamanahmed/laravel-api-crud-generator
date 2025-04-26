<?php

namespace NoamanAhmed\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Interface BaseRepositoryContract
 */
interface BaseRepositoryContract
{
    /**
     * Set the model instance.
     *
     * @return $this
     */
    public function setModel(Model|Authenticatable $model);

    /**
     * Get the model instance.
     */
    public function getModel(): Model|Authenticatable;

    /**
     * Build repository options from the request.
     *
     * @return void
     */
    public function buildOptionsFromRequest();

    /**
     * Get the QueryBuilder instance.
     */
    public function getQueryBuilder(): QueryBuilder;

    /**
     * Get paginated results.
     *
     * @return mixed
     */
    public function index();

    /**
     * Get dropdown data.
     *
     * @return mixed
     */
    public function dropdown();

    /**
     * Find a record by ID.
     *
     * @return mixed
     */
    public function find(int $id);

    /**
     * Get a record by ID.
     *
     * @return mixed
     */
    public function get(int $id);

    /**
     * Pluck IDs from the model.
     *
     * @return mixed
     */
    public function pluckIds();

    /**
     * Store a new record.
     *
     * @return mixed
     */
    public function store(array $data);

    /**
     * Update a record by ID.
     *
     * @return mixed
     */
    public function update(int $id, array $data);

    /**
     * Delete a record by ID.
     *
     * @return mixed
     */
    public function destroy(int $id);

    /**
     * Delete multiple records by IDs.
     *
     * @return mixed
     */
    public function destroyMulti(array $ids);

    public function getQueryFilters() : array;

    public function addQueryFilter(callable $filterFunction);
}
