<?php

namespace NoamanAhmed\ApiCrudGenerator\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Scope a query to only include payments for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
