<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle SuperAdmin — Class Table Inheritance
 * Table : super_admins (user_id FK → users)
 * Colonnes spécifiques : whatsapp
 */
class SuperAdmin extends Model
{
    protected $table = 'super_admins';

    protected $fillable = [
        'user_id', 'whatsapp',
    ];

    // ── Relation vers la table users (table mère) ──────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
