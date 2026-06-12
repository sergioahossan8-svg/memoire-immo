<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bien extends Model
{
    protected $fillable = [
        'agence_id', 'type_bien_id', 'titre', 'description', 'prix',
        'superficie', 'localisation', 'ville', 'chambres', 'salles_bain',
        'transaction', 'avance_mois', 'caution_eau', 'caution_electricite',
        'statut', 'is_premium', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_premium'          => 'boolean',
            'is_published'        => 'boolean',
            'prix'                => 'decimal:2',
            'caution_eau'         => 'decimal:2',
            'caution_electricite' => 'decimal:2',
            'avance_mois'         => 'integer',
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
        $base = number_format($this->prix, 0, ',', ' ') . ' FCFA';
        return $this->transaction === 'location' ? $base . ' / mois' : $base;
    }

    /**
     * Montant total à payer pour une location :
     * (prix × avance_mois) + caution_eau + caution_electricite
     */
    public function getMontantTotalLocationAttribute(): float
    {
        $avance = (float) $this->prix * ($this->avance_mois ?? 1);
        $eau    = (float) ($this->caution_eau ?? 0);
        $elec   = (float) ($this->caution_electricite ?? 0);
        return $avance + $eau + $elec;
    }

    /**
     * Montant de l'acompte (10% du montant total)
     */
    public function getAcompteAttribute(): float
    {
        $base = $this->transaction === 'location'
            ? $this->montant_total_location
            : (float) $this->prix;
        return $base * 0.10;
    }
}
