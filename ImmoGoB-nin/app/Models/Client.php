<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Client extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('role', function (Builder $builder) {
            $builder->where('role', 'client');
        });

        static::creating(function (Client $model) {
            $model->role = 'client';
        });
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'client_id');
    }

    public function favoris()
    {
        return $this->hasMany(Favori::class, 'user_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'client_id');
    }

    public function notificationsImmogo()
    {
        return $this->hasMany(NotificationImmogo::class, 'user_id');
    }
}
