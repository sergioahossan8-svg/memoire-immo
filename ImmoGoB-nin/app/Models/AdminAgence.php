<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class AdminAgence extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('role', function (Builder $builder) {
            $builder->where('role', 'admin_agence');
        });

        static::creating(function (AdminAgence $model) {
            $model->role = 'admin_agence';
        });
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function estPrincipal(): bool
    {
        return (bool) $this->est_principal;
    }
}
