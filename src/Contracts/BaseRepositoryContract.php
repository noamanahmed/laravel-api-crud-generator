<?php

namespace App\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Interface BaseRepositoryContract
 *
 * @package App\Repositories
 */
interface BaseRepositoryContract
{
    /**
     * Set the model instance.
     *
     * @param Model|Authenticatable $model
     * @return $this
     */
    public function setModel(Model|Authenticatable $model);

    /**
     * Get the model instance.
     *
     * @return Model|Authenticatable
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
     *
     * @return QueryBuilder
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
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * Get a record by ID.
     *
     * @param int $id
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
     * @param array $data
     * @return mixed
     */
    public function store(array $data);

    /**
     * Update a record by ID.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data);

    /**
     * Delete a record by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id);

    /**
     * Delete multiple records by IDs.
     *
     * @param array $ids
     * @return mixed
     */
    public function destroyMulti(array $ids);
}
