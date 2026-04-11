<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'name', 'prenom', 'email', 'telephone', 'whatsapp', 'adresse', 'ville', 'avatar',
        'role', 'agence_id', 'est_principal', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'est_principal' => 'boolean',
        ];
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'client_id');
    }

    public function favoris()
    {
        return $this->hasMany(Favori::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'client_id');
    }

    public function notificationsImmogo()
    {
        return $this->hasMany(NotificationImmogo::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminAgence(): bool
    {
        return $this->role === 'admin_agence';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Retourne le modèle enfant correspondant au rôle
     */
    public function asTyped(): static
    {
        return match($this->role) {
            'client'      => Client::withoutGlobalScopes()->find($this->id),
            'admin_agence'=> AdminAgence::withoutGlobalScopes()->find($this->id),
            'super_admin' => SuperAdmin::withoutGlobalScopes()->find($this->id),
            default       => $this,
        };
    }
}
