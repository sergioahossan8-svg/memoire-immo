<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle AdminAgence — Class Table Inheritance
 * Table : admin_agences (user_id FK → users, agence_id FK → agences)
 * Colonnes spécifiques : whatsapp, est_principal
 */
class AdminAgence extends Model
{
    protected $table = 'admin_agences';

    protected $fillable = [
        'user_id', 'agence_id', 'est_principal', 'whatsapp',
    ];

    protected function casts(): array
    {
        return [
            'est_principal' => 'boolean',
        ];
    }

    // ── Relation vers la table users (table mère) ──────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Relation vers l'agence ─────────────────────────────────────────────
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function estPrincipal(): bool
    {
        return (bool) $this->est_principal;
    }
}
