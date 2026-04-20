<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\TypeBien;
use Illuminate\Http\Request;

class BienApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Bien::with(['photos', 'agence', 'typeBien'])
            ->where('is_published', true)
            ->where('statut', 'disponible');

        if ($request->filled('transaction')) {
            $query->where('transaction', $request->transaction);
        }
        if ($request->filled('type_bien_id')) {
            $query->where('type_bien_id', $request->type_bien_id);
        }
        if ($request->filled('ville')) {
            $query->where('ville', 'like', '%' . $request->ville . '%');
        }
        if ($request->filled('prix_min')) {
            $query->where('prix', '>=', $request->prix_min);
        }
        if ($request->filled('prix_max')) {
            $query->where('prix', '<=', $request->prix_max);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('titre', 'like', '%' . $request->search . '%')
                  ->orWhere('localisation', 'like', '%' . $request->search . '%')
                  ->orWhere('ville', 'like', '%' . $request->search . '%');
            });
        }

        $biens = $query->latest()->paginate(12)->withQueryString();

        return response()->json([
            'data'       => $biens->map(fn($b) => $this->formatBien($b)),
            'pagination' => [
                'current_page' => $biens->currentPage(),
                'last_page'    => $biens->lastPage(),
                'total'        => $biens->total(),
                'per_page'     => $biens->perPage(),
            ],
        ]);
    }

    public function show(Bien $bien)
    {
        if (!$bien->is_published) {
            return response()->json(['message' => 'Bien introuvable.'], 404);
        }

        $bien->load(['photos', 'agence', 'typeBien']);

        $similaires = Bien::with(['photos'])
            ->where('is_published', true)
            ->where('statut', 'disponible')
            ->where('type_bien_id', $bien->type_bien_id)
            ->where('id', '!=', $bien->id)
            ->take(4)
            ->get();

        return response()->json([
            'bien'      => $this->formatBienDetail($bien),
            'similaires' => $similaires->map(fn($b) => $this->formatBien($b)),
        ]);
    }

    public function types()
    {
        return response()->json(['types' => TypeBien::all(['id', 'libelle'])]);
    }

    public function villes()
    {
        $villes = Bien::where('is_published', true)
            ->where('statut', 'disponible')
            ->distinct()
            ->pluck('ville')
            ->sort()
            ->values();

        return response()->json(['villes' => $villes]);
    }

    public function formatBien(Bien $bien): array
    {
        $photo = $bien->photos->where('is_principale', true)->first()
            ?? $bien->photos->first();

        // Gérer les URLs externes (Unsplash) et les chemins locaux
        $photoUrl = null;
        if ($photo) {
            if (str_starts_with($photo->chemin, 'http://') || str_starts_with($photo->chemin, 'https://')) {
                $photoUrl = $photo->chemin; // URL externe
            } else {
                $photoUrl = storage_url($photo->chemin); // URL absolue adaptée
            }
        }

        return [
            'id'          => $bien->id,
            'titre'       => $bien->titre,
            'prix'        => (float) $bien->prix,
            'prix_formate' => number_format($bien->prix, 0, ',', ' ') . ' FCFA',
            'superficie'  => $bien->superficie,
            'localisation'=> $bien->localisation,
            'ville'       => $bien->ville,
            'chambres'    => $bien->chambres,
            'salles_bain' => $bien->salles_bain,
            'transaction' => $bien->transaction,
            'statut'      => $bien->statut,
            'photo'       => $photoUrl,
            'type_bien'   => $bien->typeBien?->libelle,
            'agence'      => $bien->agence?->nom_commercial,
        ];
    }

    private function formatBienDetail(Bien $bien): array
    {
        return array_merge($this->formatBien($bien), [
            'description' => $bien->description,
            'photos'      => $bien->photos->map(fn($p) => [
                'id'            => $p->id,
                'url'           => str_starts_with($p->chemin, 'http://') || str_starts_with($p->chemin, 'https://')
                    ? $p->chemin
                    : storage_url($p->chemin), // URL absolue
                'is_principale' => $p->is_principale,
            ]),
            'agence_detail' => $bien->agence ? [
                'id'     => $bien->agence->id,
                'nom'    => $bien->agence->nom_commercial,
                'ville'  => $bien->agence->ville,
                'secteur'=> $bien->agence->secteur,
                'logo'   => $bien->agence->logo ? storage_url($bien->agence->logo) : null,
            ] : null,
        ]);
    }
}
