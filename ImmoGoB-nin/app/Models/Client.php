<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Client — Class Table Inheritance
 * Table : clients (user_id FK → users)
 * Colonnes spécifiques : adresse, ville, avatar
 */
class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'user_id', 'adresse', 'ville', 'avatar',
    ];

    // ── Relation vers la table users (table mère) ──────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Relations métier ───────────────────────────────────────────────────
    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'client_id', 'user_id');
    }

    public function favoris()
    {
        return $this->hasMany(Favori::class, 'user_id', 'user_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'client_id', 'user_id');
    }

    public function notificationsImmogo()
    {
        return $this->hasMany(NotificationImmogo::class, 'user_id', 'user_id');
    }
}
