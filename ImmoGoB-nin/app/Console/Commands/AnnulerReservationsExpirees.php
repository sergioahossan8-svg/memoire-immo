<?php

namespace App\Console\Commands;

use App\Models\Bien;
use App\Models\Contrat;
use App\Models\NotificationImmogo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AnnulerReservationsExpirees extends Command
{
    protected $signature   = 'reservations:annuler-expirees';
    protected $description = 'Annule les réservations dont la date limite de solde est dépassée (acompte payé mais solde non réglé dans les 15 jours).';

    public function handle(): int
    {
        // Contrats en_attente avec date limite dépassée
        // → l'acompte a été payé (ou c'est un virement/espèces en attente)
        // → le solde n'a pas été réglé dans les 15 jours
        $contrats = Contrat::where('statut_contrat', 'en_attente')
            ->where(function ($q) {
                $q->where('date_limite_solde_location', '<', now())
                  ->orWhere('date_limite_solde_vente', '<', now());
            })
            ->with(['bien', 'client'])
            ->get();

        $count = 0;

        foreach ($contrats as $contrat) {
            $bien = $contrat->bien;

            if (!$bien) continue;

            // Annuler le contrat
            $contrat->update(['statut_contrat' => 'annule']);

            // Remettre le bien en disponible
            if (in_array($bien->statut, ['reserve', 'loue'])) {
                $bien->update(['statut' => 'disponible']);
            }

            // Notifier le client
            NotificationImmogo::create([
                'user_id' => $contrat->client_id,
                'titre'   => 'Réservation annulée — délai expiré',
                'message' => 'Votre réservation pour "' . $bien->titre . '" a été annulée car le solde n\'a pas été réglé dans les 15 jours. Le bien est de nouveau disponible.',
                'lien'    => route('biens.show', $bien),
            ]);

            Log::info('Réservation expirée annulée', [
                'contrat_id' => $contrat->id,
                'bien'       => $bien->titre,
                'client_id'  => $contrat->client_id,
            ]);

            $count++;
        }

        $this->info("{$count} réservation(s) expirée(s) annulée(s).");

        return Command::SUCCESS;
    }
}
