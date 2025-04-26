<?php

namespace NoamanAhmed\Policies;

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
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  mixed  $model
     */
    public function view(User $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  mixed  $model
     */
    public function update(User $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  mixed  $model
     */
    public function delete(User $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  mixed  $model
     */
    public function restore(User $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  mixed  $model
     */
    public function forceDelete(User $user, $model): bool
    {
        return true;
    }
}
