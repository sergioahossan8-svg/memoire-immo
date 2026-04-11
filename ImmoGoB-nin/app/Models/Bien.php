<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bien extends Model
{
    protected $fillable = [
        'agence_id', 'type_bien_id', 'titre', 'description', 'prix',
        'superficie', 'localisation', 'ville', 'chambres', 'salles_bain',
        'transaction', 'statut', 'is_premium', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'is_published' => 'boolean',
            'prix' => 'decimal:2',
        ];
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function typeBien()
    {
        return $this->belongsTo(TypeBien::class);
    }

    public function photos()
    {
        return $this->hasMany(BienPhoto::class);
    }

    public function photoPrincipale()
    {
        return $this->hasOne(BienPhoto::class)->where('is_principale', true);
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class);
    }

    public function favoris()
    {
        return $this->hasMany(Favori::class);
    }

    public function getPrixFormateAttribute(): string
    {
        return number_format($this->prix, 0, ',', ' ') . ' FCFA';
    }
}
