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
            'titre' => 'required|string|max:200',
            'type_bien_id' => 'required|exists:type_biens,id',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
            'superficie' => 'nullable|numeric',
            'localisation' => 'required|string',
            'ville' => 'required|string',
            'chambres' => 'nullable|integer',
            'salles_bain' => 'nullable|integer',
            'transaction' => 'required|in:location,vente',
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|max:5120',
        ]);

        $agence = auth()->user()->adminAgence?->agence;
        $data['agence_id'] = $agence->id;
        $data['statut'] = 'disponible';

        $bien = Bien::create($data);

        // Sauvegarder les photos
        foreach ($request->file('photos') as $index => $photo) {
            $chemin = $photo->store('biens', 'public');
            BienPhoto::create([
                'bien_id' => $bien->id,
                'chemin' => $chemin,
                'is_principale' => $index === 0,
            ]);
        }

        \App\Models\ActivityLog::log('bien_created', 'Bien créé : ' . $bien->titre, $bien);
        return redirect()->route('admin.biens.index')->with('success', 'Bien créé avec succès.');
    }

    public function edit(Bien $bien)    {
        $this->authorizeAgence($bien);
        $types = TypeBien::all();
        $bien->load('photos');
        return view('admin.biens.edit', compact('bien', 'types'));
    }

    public function update(Request $request, Bien $bien)
    {
        $this->authorizeAgence($bien);

        $data = $request->validate([
            'titre' => 'required|string|max:200',
            'type_bien_id' => 'required|exists:type_biens,id',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
            'superficie' => 'nullable|numeric',
            'localisation' => 'required|string',
            'ville' => 'required|string',
            'chambres' => 'nullable|integer',
            'salles_bain' => 'nullable|integer',
            'transaction' => 'required|in:location,vente',
            'photos.*' => 'nullable|image|max:5120',
        ]);

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

        // Un bien vendu est définitif — l'admin ne peut plus changer
        if ($bien->statut === 'vendu') {
            return back()->withErrors(['statut' => 'Un bien vendu ne peut plus être modifié.']);
        }

        $request->validate(['statut' => 'required|in:disponible,reserve,loue,indisponible']);

        $ancienStatut = $bien->statut;
        $nouveauStatut = $request->statut;

        $bien->update(['statut' => $nouveauStatut]);

        // Si on remet en disponible, on annule les contrats en attente liés
        if ($nouveauStatut === 'disponible' && in_array($ancienStatut, ['reserve', 'loue'])) {
            // Annuler les contrats actifs/en_attente sur ce bien
            $bien->contrats()
                ->whereIn('statut_contrat', ['en_attente', 'actif'])
                ->update(['statut_contrat' => 'annule']);

            // Notifier les clients concernés
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
            'disponible'   => 'Bien remis en disponible. Il réapparaît sur le site.',
            'reserve'      => 'Bien marqué comme réservé.',
            'loue'         => 'Bien marqué comme loué.',
            'indisponible' => 'Bien marqué comme indisponible.',
        ];

        \App\Models\ActivityLog::log('bien_statut', 'Statut bien "' . $bien->titre . '" → ' . $nouveauStatut, $bien);
        return back()->with('success', $messages[$nouveauStatut] ?? 'Statut mis à jour.');
    }

    public function publier(Bien $bien)
    {
        $this->authorizeAgence($bien);
        $bien->update(['is_published' => !$bien->is_published]);
        return back()->with('success', $bien->is_published ? 'Bien publié.' : 'Bien dépublié.');
    }

    private function authorizeAgence(Bien $bien): void
    {
        abort_if($bien->agence_id !== auth()->user()->adminAgence?->agence_id, 403);
    }
}
