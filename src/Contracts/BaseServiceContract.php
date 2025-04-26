<?php

namespace NoamanAhmed\Services;

/**
 * Interface BaseServiceContract
 *
 * Defines the standard methods to be implemented by service classes.
 */
interface BaseServiceContract
{
    /**
     * Get a listing of all resources.
     *
     * @return mixed
     */
    public function index();

    /**
     * Get a list of resources formatted for dropdown selections.
     *
     * @return mixed
     */
    public function dropdown();

    /**
     * Get a list of statuses formatted for dropdown selections.
     *
     * @return mixed
     */
    public function dropdownForStatus();

    /**
     * Retrieve a specific resource by its ID.
     *
     * @param  int|string  $id
     * @return mixed
     */
    public function get($id);

    /**
     * Store a newly created resource.
     *
     * @param  array  $array
     * @return mixed
     */
    public function store($array);

    /**
     * Update an existing resource.
     *
     * @param  int|string  $id
     * @param  array  $array
     * @return mixed
     */
    public function update($id, $array);

    /**
     * Delete a specific resource by its ID.
     *
     * @param  int|string  $id
     * @return mixed
     */
    public function destroy($id);

    /**
     * Delete multiple resources.
     *
     * @param  array  $array
     * @return mixed
     */
    public function destroyMulti($array);
}
