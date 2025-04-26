<?php

namespace NoamanAhmed\Policies;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class BasePolicy
 *
 * Base Policy class providing default authorization logic.
 */
class BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  mixed  $model
     */
    public function view(Authenticatable $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authenticatable $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  mixed  $model
     */
    public function update(Authenticatable $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  mixed  $model
     */
    public function delete(Authenticatable $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  mixed  $model
     */
    public function restore(Authenticatable $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  mixed  $model
     */
    public function forceDelete(Authenticatable $user, $model): bool
    {
        return true;
    }
}
