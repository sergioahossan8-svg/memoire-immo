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

    public function administrateurs()
    {
        return $this->hasMany(User::class)->where('role', 'admin_agence');
    }

    public function adminPrincipal()
    {
        return $this->hasOne(User::class)->where('est_principal', true);
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
