<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\TypeBien;
use Illuminate\Http\Request;

class BienController extends Controller
{
    public function index()
    {
        // Déterminer la ville de l'utilisateur
        $ville = $this->getVilleActive();

        $query = Bien::with(['photos', 'agence', 'typeBien'])
            ->where('is_published', true)
            ->where('statut', 'disponible');

        // Biens récents : d'abord ceux de la ville de l'utilisateur, puis les autres
        $biensVille = (clone $query)
            ->where('transaction', 'location')
            ->where('ville', 'like', '%' . $ville . '%')
            ->latest()->take(6)->get();

        $biensAutres = (clone $query)
            ->where('transaction', 'location')
            ->where('ville', 'not like', '%' . $ville . '%')
            ->latest()->take(max(0, 6 - $biensVille->count()))->get();

        $biensRecents = $biensVille->merge($biensAutres);

        // Recommandations : premium d'abord, puis complément
        $biensRecommandes = (clone $query)
            ->where('is_premium', true)
            ->latest()->take(3)->get();

        if ($biensRecommandes->count() < 3) {
            $ids = $biensRecommandes->pluck('id')->toArray();
            $complement = (clone $query)
                ->whereNotIn('id', $ids)
                ->latest()
                ->take(3 - $biensRecommandes->count())
                ->get();
            $biensRecommandes = $biensRecommandes->merge($complement);
        }

        return view('client.home', compact('biensRecents', 'biensRecommandes', 'ville'));
    }

    public function changerLieu(Request $request)
    {
        $request->validate(['ville' => 'required|string|max:100']);
        $ville = trim($request->ville);

        // Sauvegarder en session pour visiteurs et utilisateurs connectés
        session(['ville_active' => $ville]);

        // Si connecté, mettre à jour la ville dans la table clients
        if (auth()->check()) {
            auth()->user()->client()->updateOrCreate(
                ['user_id' => auth()->id()],
                ['ville' => $ville]
            );
        }

        return redirect()->route('home')->with('success', 'Localisation mise à jour.');
    }

    private function getVilleActive(): string
    {
        // Priorité : session > profil utilisateur > défaut
        if (session('ville_active')) {
            return session('ville_active');
        }
        if (auth()->check() && auth()->user()->client?->ville) {
            return auth()->user()->client->ville;
        }
        return 'Cotonou';
    }
    public function liste(Request $request)
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
        $types = TypeBien::all();

        return view('client.biens.liste', compact('biens', 'types'));
    }

    public function show(Bien $bien)
    {
        if (!$bien->is_published) {
            abort(404);
        }

        $bien->load(['photos', 'agence', 'typeBien']);
        $biensSimiliaires = Bien::with(['photos'])
            ->where('is_published', true)
            ->where('statut', 'disponible')
            ->where('type_bien_id', $bien->type_bien_id)
            ->where('id', '!=', $bien->id)
            ->take(3)
            ->get();

        return view('client.biens.show', compact('bien', 'biensSimiliaires'));
    }
}
