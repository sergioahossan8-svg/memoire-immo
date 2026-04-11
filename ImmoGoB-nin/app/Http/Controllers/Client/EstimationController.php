<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\TypeBien;
use Illuminate\Http\Request;

class EstimationController extends Controller
{
    public function index()
    {
        $types = TypeBien::all();
        return view('client.estimation', compact('types'));
    }

    public function estimer(Request $request)
    {
        $data = $request->validate([
            'type_bien_id' => 'required|exists:type_biens,id',
            'ville'        => 'required|string',
            'superficie'   => 'required|numeric|min:1',
            'transaction'  => 'required|in:location,vente',
            'chambres'     => 'nullable|integer|min:0',
        ]);

        // Chercher des biens similaires publiés dans la base
        $biensSimilaires = Bien::where('type_bien_id', $data['type_bien_id'])
            ->where('transaction', $data['transaction'])
            ->where('is_published', true)
            ->where('ville', 'like', '%' . $data['ville'] . '%')
            ->whereNotNull('superficie')
            ->where('superficie', '>', 0)
            ->get();

        $estimation = null;

        if ($biensSimilaires->count() >= 2) {
            // Calculer le prix au m²
            $prixM2 = $biensSimilaires->avg(fn($b) => $b->prix / $b->superficie);
            $prixEstime = $prixM2 * $data['superficie'];

            // Fourchette ±20%
            $estimation = [
                'min'       => round($prixEstime * 0.80 / 1000) * 1000,
                'max'       => round($prixEstime * 1.20 / 1000) * 1000,
                'moyen'     => round($prixEstime / 1000) * 1000,
                'prix_m2'   => round($prixM2),
                'nb_biens'  => $biensSimilaires->count(),
                'ville'     => $data['ville'],
                'type'      => TypeBien::find($data['type_bien_id'])->libelle,
                'superficie'=> $data['superficie'],
                'transaction'=> $data['transaction'],
            ];
        } elseif ($biensSimilaires->count() === 1) {
            // Un seul bien de référence
            $b = $biensSimilaires->first();
            $prixM2 = $b->prix / $b->superficie;
            $prixEstime = $prixM2 * $data['superficie'];

            $estimation = [
                'min'       => round($prixEstime * 0.75 / 1000) * 1000,
                'max'       => round($prixEstime * 1.25 / 1000) * 1000,
                'moyen'     => round($prixEstime / 1000) * 1000,
                'prix_m2'   => round($prixM2),
                'nb_biens'  => 1,
                'ville'     => $data['ville'],
                'type'      => TypeBien::find($data['type_bien_id'])->libelle,
                'superficie'=> $data['superficie'],
                'transaction'=> $data['transaction'],
            ];
        }
        // Si 0 biens similaires → $estimation reste null → message "données insuffisantes"

        $types = TypeBien::all();
        return view('client.estimation', compact('types', 'estimation', 'data'));
    }
}
