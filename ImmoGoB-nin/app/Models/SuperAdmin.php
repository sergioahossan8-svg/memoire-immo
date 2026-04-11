<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class SuperAdmin extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('role', function (Builder $builder) {
            $builder->where('role', 'super_admin');
        });

        static::creating(function (SuperAdmin $model) {
            $model->role = 'super_admin';
        });
    }
}
