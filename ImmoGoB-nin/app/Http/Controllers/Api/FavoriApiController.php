<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Favori;

class FavoriApiController extends Controller
{
    public function index()
    {
        $favoris = auth()->user()
            ->favoris()
            ->with(['bien.photos', 'bien.agence', 'bien.typeBien'])
            ->get()
            ->map(function ($f) {
                $bien = $f->bien;
                if (!$bien) return null;
                $bienApi = new BienApiController();
                return $bienApi->formatBien($bien);
            })
            ->filter()
            ->values();

        return response()->json(['favoris' => $favoris]);
    }

    public function toggle(Bien $bien)
    {
        $user   = auth()->user();
        $favori = Favori::where('user_id', $user->id)->where('bien_id', $bien->id)->first();

        if ($favori) {
            $favori->delete();
            return response()->json(['added' => false, 'message' => 'Retiré des favoris.']);
        }

        Favori::create(['user_id' => $user->id, 'bien_id' => $bien->id]);
        return response()->json(['added' => true, 'message' => 'Ajouté aux favoris.']);
    }
}
