<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    protected $fillable = [
        'bien_id', 'client_id', 'type_contrat', 'statut_contrat', 'date_contrat',
        'montant_total_location', 'date_reserv_location', 'date_limite_solde_location',
        'montant_total_vente', 'date_reserv_vente', 'date_limite_solde_vente',
    ];

    protected function casts(): array
    {
        return [
            'date_contrat' => 'date',
            'date_reserv_location' => 'datetime',
            'date_limite_solde_location' => 'datetime',
            'date_reserv_vente' => 'datetime',
            'date_limite_solde_vente' => 'datetime',
        ];
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function getMontantTotal(): float
    {
        return $this->type_contrat === 'location'
            ? (float) $this->montant_total_location
            : (float) $this->montant_total_vente;
    }

    public function getMontantAcompte(): float
    {
        return $this->getMontantTotal() * 0.10;
    }

    public function getMontantPaye(): float
    {
        return $this->paiements()->where('statut', 'confirme')->sum('montant');
    }

    public function getSoldeRestant(): float
    {
        return $this->getMontantTotal() - $this->getMontantPaye();
    }
}
