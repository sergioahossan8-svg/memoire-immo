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

    /**
     * Colonnes communes à tous les utilisateurs (table mère users).
     * Les colonnes spécifiques (adresse, ville, avatar, whatsapp, agence_id, est_principal)
     * sont dans les tables spécialisées : clients, admin_agences, super_admins.
     */
    protected $fillable = [
        'name', 'prenom', 'email', 'telephone', 'role', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relations CTI (Class Table Inheritance) ────────────────────────────

    /** Données spécifiques au client (adresse, ville, avatar) */
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    /** Données spécifiques à l'admin d'agence (agence_id, est_principal, whatsapp) */
    public function adminAgence()
    {
        return $this->hasOne(AdminAgence::class);
    }

    /** Données spécifiques au super admin (whatsapp) */
    public function superAdmin()
    {
        return $this->hasOne(SuperAdmin::class);
    }

    // ── Relations métier (conservées sur User pour la compatibilité Sanctum/contrats) ──

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

    // ── Helpers rôle ───────────────────────────────────────────────────────

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
     * Retourne le modèle enfant correspondant au rôle (table spécialisée).
     */
    public function asTyped(): ?Model
    {
        return match($this->role) {
            'client'       => Client::where('user_id', $this->id)->first(),
            'admin_agence' => AdminAgence::where('user_id', $this->id)->first(),
            'super_admin'  => SuperAdmin::where('user_id', $this->id)->first(),
            default        => null,
        };
    }
}
