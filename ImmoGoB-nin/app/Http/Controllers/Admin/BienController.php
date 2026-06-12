<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\BienPhoto;
use App\Models\TypeBien;
use Illuminate\Http\Request;

class BienController extends Controller
{
    public function index()
    {
        $agence = auth()->user()->adminAgence?->agence;
        $biens = Bien::where('agence_id', $agence->id)
            ->with(['photos', 'typeBien'])
            ->latest()->paginate(15);
        return view('admin.biens.index', compact('biens', 'agence'));
    }

    public function create()
    {
        $types = TypeBien::all();
        return view('admin.biens.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'         => 'required|string|max:200',
            'type_bien_id'  => 'required|exists:type_biens,id',
            'description'   => 'nullable|string',
            'prix'          => 'required|numeric|min:0',
            'superficie'    => 'nullable|numeric',
            'localisation'  => 'required|string',
            'ville'         => 'required|string',
            'chambres'      => 'nullable|integer',
            'salles_bain'   => 'nullable|integer',
            'transaction'   => 'required|in:location,vente',
            'avance_mois'           => 'required_if:transaction,location|integer|min:1|max:12',
            'caution_eau'           => 'nullable|numeric|min:0',
            'caution_electricite'   => 'nullable|numeric|min:0',
            'photos'        => 'required|array|min:1',
            'photos.*'      => 'image|max:5120',
        ]);

        if ($data['transaction'] === 'vente') {
            $data['avance_mois']         = 1;
            $data['caution_eau']         = null;
            $data['caution_electricite'] = null;
        }

        $agence = auth()->user()->adminAgence?->agence;
        $data['agence_id'] = $agence->id;
        $data['statut']    = 'disponible';

        $bien = Bien::create($data);

        foreach ($request->file('photos') as $index => $photo) {
            $chemin = $photo->store('biens', 'public');
            BienPhoto::create([
                'bien_id' => $bien->id,
                'chemin' => $chemin,
                'is_principale' => $index === 0,
            ]);
        }

        \App\Models\ActivityLog::log('bien_created', 'Bien créé : ' . $bien->titre, $bien);
        return redirect()->route('admin.biens.index')
            ->with('success', 'Bien créé avec succès.')
            ->with('info', 'Le bien est créé mais non publié. Activez le bouton "Publié" pour qu\'il apparaisse dans l\'application mobile.');
    }

    public function edit(Bien $bien)
    {
        $this->authorizeAgence($bien);
        $types = TypeBien::all();
        $bien->load('photos');
        return view('admin.biens.edit', compact('bien', 'types'));
    }

    public function update(Request $request, Bien $bien)
    {
        $this->authorizeAgence($bien);

        $data = $request->validate([
            'titre'         => 'required|string|max:200',
            'type_bien_id'  => 'required|exists:type_biens,id',
            'description'   => 'nullable|string',
            'prix'          => 'required|numeric|min:0',
            'superficie'    => 'nullable|numeric',
            'localisation'  => 'required|string',
            'ville'         => 'required|string',
            'chambres'      => 'nullable|integer',
            'salles_bain'   => 'nullable|integer',
            'transaction'   => 'required|in:location,vente',
            'avance_mois'           => 'required_if:transaction,location|integer|min:1|max:12',
            'caution_eau'           => 'nullable|numeric|min:0',
            'caution_electricite'   => 'nullable|numeric|min:0',
            'photos.*'      => 'nullable|image|max:5120',
        ]);

        if ($data['transaction'] === 'vente') {
            $data['avance_mois']         = 1;
            $data['caution_eau']         = null;
            $data['caution_electricite'] = null;
        }

        $bien->update($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $chemin = $photo->store('biens', 'public');
                BienPhoto::create(['bien_id' => $bien->id, 'chemin' => $chemin, 'is_principale' => false]);
            }
        }

        \App\Models\ActivityLog::log('bien_updated', 'Bien modifié : ' . $bien->titre, $bien);
        return redirect()->route('admin.biens.index')->with('success', 'Bien mis à jour.');
    }

    public function destroy(Bien $bien)
    {
        $this->authorizeAgence($bien);
        \App\Models\ActivityLog::log('bien_deleted', 'Bien supprimé : ' . $bien->titre, $bien);
        $bien->delete();
        return back()->with('success', 'Bien supprimé.');
    }

    public function updateStatut(Request $request, Bien $bien)
    {
        $this->authorizeAgence($bien);

        if ($bien->statut === 'vendu') {
            return back()->withErrors(['statut' => 'Un bien vendu ne peut plus être modifié.']);
        }

        $transitionsAutorisees = [
            'disponible'    => ['reserve', 'indisponible'],
            'reserve'       => ['libere', 'vendu', 'loue'],
            'loue'          => ['libere'],
            'indisponible'  => ['disponible'],
        ];

        $request->validate(['statut' => 'required|in:disponible,reserve,vendu,loue,libere,indisponible']);

        $ancienStatut  = $bien->statut;
        $nouveauStatut = $request->statut;

        $autorise = $transitionsAutorisees[$ancienStatut] ?? [];
        if (!in_array($nouveauStatut, $autorise)) {
            return back()->withErrors([
                'statut' => "Transition interdite : de « {$ancienStatut} » vers « {$nouveauStatut} »."
            ]);
        }

        $statutReel = ($nouveauStatut === 'libere') ? 'disponible' : $nouveauStatut;
        $bien->update(['statut' => $statutReel]);

        if ($statutReel === 'disponible' && in_array($ancienStatut, ['reserve', 'loue'])) {
            $bien->contrats()
                ->whereIn('statut_contrat', ['en_attente', 'actif'])
                ->update(['statut_contrat' => 'annule']);

            foreach ($bien->contrats()->where('statut_contrat', 'annule')->get() as $contrat) {
                \App\Models\NotificationImmogo::create([
                    'user_id' => $contrat->client_id,
                    'titre'   => 'Contrat annulé',
                    'message' => 'Votre contrat pour "' . $bien->titre . '" a été annulé par l\'agence. Le bien est de nouveau disponible.',
                    'lien'    => route('biens.show', $bien),
                ]);
            }
        }

        $messages = [
            'disponible'   => 'Bien remis en disponible.',
            'reserve'      => 'Bien marqué comme réservé.',
            'vendu'        => 'Bien marqué comme vendu (définitif).',
            'loue'         => 'Bien marqué comme loué.',
            'libere'       => 'Bien libéré et remis en disponible.',
            'indisponible' => 'Bien marqué comme indisponible.',
        ];

        \App\Models\ActivityLog::log('bien_statut', 'Statut bien "' . $bien->titre . '" → ' . $statutReel, $bien);
        return back()->with('success', $messages[$nouveauStatut] ?? 'Statut mis à jour.');
    }

    public function publier(Bien $bien)
    {
        $this->authorizeAgence($bien);
        $bien->update(['is_published' => !$bien->is_published]);
        return back()->with('success', $bien->is_published ? 'Bien publié.' : 'Bien dépublié.');
    }

    /**
     * Confirmer un paiement en espèces (paiement complet sur place).
     * Clic droit → "Payé en espèces" → bien passe à loué/vendu.
     */
    public function payerEspeces(Request $request, Bien $bien)
    {
        return $this->confirmerPaiementHorsLigne($bien, 'especes', 'ESP');
    }

    /**
     * Confirmer un virement bancaire (paiement complet).
     * Clic droit → "Virement confirmé" → bien passe à loué/vendu.
     */
    public function confirmerVirement(Request $request, Bien $bien)
    {
        return $this->confirmerPaiementHorsLigne($bien, 'virement', 'VIR');
    }

    /**
     * Logique commune pour confirmer espèces ou virement (paiement complet hors ligne).
     */
    private function confirmerPaiementHorsLigne(Bien $bien, string $mode, string $prefixeRef)
    {
        $this->authorizeAgence($bien);
        abort_if($bien->statut === 'vendu', 422, 'Ce bien est déjà vendu.');

        $montantTotal = $bien->transaction === 'location'
            ? $bien->montant_total_location
            : (float) $bien->prix;

        $reference = $prefixeRef . '-' . strtoupper(\Illuminate\Support\Str::random(10));

        // Chercher un contrat en_attente existant (client qui a soumis une demande)
        $contrat = $bien->contrats()
            ->where('statut_contrat', 'en_attente')
            ->latest()
            ->first();

        if (!$contrat) {
            // Aucune demande préalable — l'admin crée le contrat manuellement
            $contrat = \App\Models\Contrat::create([
                'bien_id'                                   => $bien->id,
                'client_id'                                 => auth()->id(),
                'type_contrat'                              => $bien->transaction,
                'statut_contrat'                            => 'en_attente',
                'date_contrat'                              => now(),
                'montant_total_' . $bien->transaction       => $montantTotal,
                'date_reserv_' . $bien->transaction         => now(),
            ]);
        }

        \App\Models\Paiement::create([
            'contrat_id'    => $contrat->id,
            'client_id'     => $contrat->client_id,
            'montant'       => $montantTotal,
            'date_paiement' => now(),
            'type_paiement' => 'complet',
            'mode_paiement' => $mode,
            'reference'     => $reference,
            'statut'        => 'confirme',
        ]);

        $nouveauStatut = $bien->transaction === 'location' ? 'loue' : 'vendu';
        $contrat->update(['statut_contrat' => 'actif']);
        $bien->update(['statut' => $nouveauStatut]);

        $modeLabel = $mode === 'virement' ? 'Virement bancaire' : 'Paiement espèces';

        \App\Models\NotificationImmogo::create([
            'user_id' => $contrat->client_id,
            'titre'   => $modeLabel . ' confirmé ✓',
            'message' => 'Votre paiement de ' . number_format($montantTotal, 0, ',', ' ') . ' FCFA pour "' . $bien->titre . '" a été confirmé. Réf : ' . $reference,
            'lien'    => route('client.historique'),
        ]);

        \App\Models\ActivityLog::log($mode . '_confirme', $modeLabel . ' confirmé — bien : ' . $bien->titre . ' → ' . $nouveauStatut, $bien);

        return response()->json(['success' => true, 'message' => $modeLabel . ' confirmé. Le bien est maintenant ' . $nouveauStatut . '.']);
    }

    private function authorizeAgence(Bien $bien): void
    {
        abort_if($bien->agence_id !== auth()->user()->adminAgence?->agence_id, 403);
    }
}
