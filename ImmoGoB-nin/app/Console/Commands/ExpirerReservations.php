<?php

namespace App\Console\Commands;

use App\Models\Bien;
use App\Models\Contrat;
use App\Models\NotificationImmogo;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Commande : expirer les réservations dépassées
 *
 * Logique :
 * - Un contrat en_attente dont la date_limite_solde est dépassée → annulé
 * - Le bien associé repasse en « disponible » automatiquement
 * - Le client est notifié et son argent lui sera restitué
 *
 * Délai de réservation : 15 jours
 *
 * Planifier via cron (toutes les heures) :
 *   0 * * * * php /path/to/artisan schedule:run
 */
class ExpirerReservations extends Command
{
    protected $signature   = 'reservations:expirer';
    protected $description = 'Expire les réservations dont la date limite est dépassée et remet le bien en disponible.';

    public function handle(): int
    {
        $maintenant = Carbon::now();

        // Récupérer tous les contrats en_attente dont la date limite est passée
        $contratsExpires = Contrat::where('statut_contrat', 'en_attente')
            ->where(function ($query) use ($maintenant) {
                $query->where(function ($q) use ($maintenant) {
                    // Contrats de type location
                    $q->where('type_contrat', 'location')
                      ->whereNotNull('date_limite_solde_location')
                      ->where('date_limite_solde_location', '<', $maintenant);
                })->orWhere(function ($q) use ($maintenant) {
                    // Contrats de type vente
                    $q->where('type_contrat', 'vente')
                      ->whereNotNull('date_limite_solde_vente')
                      ->where('date_limite_solde_vente', '<', $maintenant);
                });
            })
            ->with('bien', 'client')
            ->get();

        $count = 0;

        foreach ($contratsExpires as $contrat) {
            $bien = $contrat->bien;

            if (!$bien) {
                continue;
            }

            // Annuler le contrat
            $contrat->update(['statut_contrat' => 'annule']);

            // Remettre le bien en disponible uniquement si son statut est encore "reserve"
            if ($bien->statut === 'reserve') {
                $bien->update(['statut' => 'disponible']);
            }

            // Notifier le client
            NotificationImmogo::create([
                'user_id' => $contrat->client_id,
                'titre'   => 'Réservation expirée',
                'message' => 'Votre réservation pour "' . $bien->titre . '" a expiré car le solde n\'a pas été réglé dans les 15 jours impartis. Le bien est de nouveau disponible et votre acompte vous sera restitué.',
                'lien'    => '/client/historique',
                'lu'      => false,
            ]);

            $this->line("Contrat #{$contrat->id} expiré → bien \"{$bien->titre}\" remis en disponible.");
            $count++;
        }

        $this->info("Expiration terminée : {$count} réservation(s) expirée(s).");

        return Command::SUCCESS;
    }
}
