<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    protected $fillable = [
        'nom_commercial', 'secteur', 'ville', 'adresse_complete',
        'email', 'telephone', 'logo', 'statut',
        'kkiapay_public_key', 'kkiapay_private_key', 'kkiapay_secret', 'kkiapay_sandbox',
    ];

    protected $hidden = ['kkiapay_private_key', 'kkiapay_secret'];

    public function hasKkiapay(): bool
    {
        return !empty($this->kkiapay_public_key)
            && !empty($this->kkiapay_private_key)
            && !empty($this->kkiapay_secret);
    }

    public function adminAgences()
    {
        return $this->hasMany(\App\Models\AdminAgence::class);
    }

    public function administrateurs()
    {
        // Relation via la table admin_agences (héritage de classe)
        return $this->hasManyThrough(User::class, \App\Models\AdminAgence::class, 'agence_id', 'id', 'id', 'user_id');
    }

    public function adminPrincipal()
    {
        // Admin principal via la table admin_agences
        return $this->hasOneThrough(User::class, \App\Models\AdminAgence::class, 'agence_id', 'id', 'id', 'user_id')
            ->where('admin_agences.est_principal', true);
    }

    public function biens()
    {
        return $this->hasMany(Bien::class);
    }

    public function biensActifs()
    {
        return $this->hasMany(Bien::class)->where('is_published', true)->where('statut', 'disponible');
    }
}
