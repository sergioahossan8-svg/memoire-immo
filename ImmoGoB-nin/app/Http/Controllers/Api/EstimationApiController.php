<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\TypeBien;
use Illuminate\Http\Request;

class EstimationApiController extends Controller
{
    public function estimer(Request $request)
    {
        $data = $request->validate([
            'type_bien_id' => 'required|exists:type_biens,id',
            'ville'        => 'required|string',
            'superficie'   => 'required|numeric|min:1',
            'transaction'  => 'required|in:location,vente',
            'chambres'     => 'nullable|integer|min:0',
        ]);

        $biensSimilaires = Bien::where('type_bien_id', $data['type_bien_id'])
            ->where('transaction', $data['transaction'])
            ->where('is_published', true)
            ->where('ville', 'like', '%' . $data['ville'] . '%')
            ->whereNotNull('superficie')
            ->where('superficie', '>', 0)
            ->get();

        $type = TypeBien::find($data['type_bien_id']);
        $estimation = null;

        if ($biensSimilaires->count() >= 2) {
            $prixM2    = $biensSimilaires->avg(fn($b) => $b->prix / $b->superficie);
            $prixEstime = $prixM2 * $data['superficie'];

            $estimation = [
                'min'        => round($prixEstime * 0.80 / 1000) * 1000,
                'max'        => round($prixEstime * 1.20 / 1000) * 1000,
                'moyen'      => round($prixEstime / 1000) * 1000,
                'prix_m2'    => round($prixM2),
                'nb_biens'   => $biensSimilaires->count(),
                'ville'      => $data['ville'],
                'type'       => $type->libelle,
                'superficie' => $data['superficie'],
                'transaction'=> $data['transaction'],
            ];
        } elseif ($biensSimilaires->count() === 1) {
            $b         = $biensSimilaires->first();
            $prixM2    = $b->prix / $b->superficie;
            $prixEstime = $prixM2 * $data['superficie'];

            $estimation = [
                'min'        => round($prixEstime * 0.75 / 1000) * 1000,
                'max'        => round($prixEstime * 1.25 / 1000) * 1000,
                'moyen'      => round($prixEstime / 1000) * 1000,
                'prix_m2'    => round($prixM2),
                'nb_biens'   => 1,
                'ville'      => $data['ville'],
                'type'       => $type->libelle,
                'superficie' => $data['superficie'],
                'transaction'=> $data['transaction'],
            ];
        }

        return response()->json([
            'estimation' => $estimation,
            'message'    => $estimation
                ? 'Estimation calculée avec ' . $estimation['nb_biens'] . ' bien(s) de référence.'
                : 'Données insuffisantes pour estimer ce type de bien dans cette ville.',
        ]);
    }
}
