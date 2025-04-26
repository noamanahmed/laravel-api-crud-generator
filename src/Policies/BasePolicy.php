<?php

namespace App\Policies;

/**
 * Class BasePolicy
 *
 * Base Policy class providing default authorization logic.
 */
class BasePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  mixed  $model
     * @return bool
     */
    public function view(User $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  mixed  $model
     * @return bool
     */
    public function update(User $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  mixed  $model
     * @return bool
     */
    public function delete(User $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User  $user
     * @param  mixed  $model
     * @return bool
     */
    public function restore(User $user, $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User  $user
     * @param  mixed  $model
     * @return bool
     */
    public function forceDelete(User $user, $model): bool
    {
        return true;
    }
}
