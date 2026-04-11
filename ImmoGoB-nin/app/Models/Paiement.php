<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'contrat_id', 'client_id', 'montant', 'date_paiement',
        'type_paiement', 'mode_paiement', 'reference', 'statut',
        'fedapay_transaction_id', 'fedapay_token',
    ];

    protected function casts(): array
    {
        return [
            'date_paiement' => 'datetime',
            'montant' => 'decimal:2',
        ];
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
