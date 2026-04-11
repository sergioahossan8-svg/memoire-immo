<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    protected $fillable = [
        'nom_commercial', 'secteur', 'ville', 'adresse_complete',
        'email', 'telephone', 'logo', 'statut',
        'fedapay_secret_key', 'fedapay_env',
    ];

    // Masquer la clé secrète dans les sérialisations JSON
    protected $hidden = ['fedapay_secret_key'];

    public function getFedapayKey(): ?string
    {
        return $this->fedapay_secret_key;
    }

    public function hasFedapay(): bool
    {
        return !empty($this->fedapay_secret_key);
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
