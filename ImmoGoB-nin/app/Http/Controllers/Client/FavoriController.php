<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Favori;
use Illuminate\Http\Request;

class FavoriController extends Controller
{
    public function index()
    {
        $favoris = auth()->user()->favoris()->with(['bien.photos', 'bien.agence', 'bien.typeBien'])->get();
        return view('client.favoris', compact('favoris'));
    }

    public function toggle(Bien $bien)
    {
        if (!auth()->check()) {
            if (request()->wantsJson()) {
                return response()->json(['redirect' => route('login')]);
            }
            return redirect()->route('login')->with('info', 'Connectez-vous pour ajouter aux favoris.');
        }

        $user = auth()->user();
        $favori = Favori::where('user_id', $user->id)->where('bien_id', $bien->id)->first();

        if ($favori) {
            $favori->delete();
            $added = false;
        } else {
            Favori::create(['user_id' => $user->id, 'bien_id' => $bien->id]);
            $added = true;
        }

        if (request()->wantsJson()) {
            return response()->json(['added' => $added]);
        }

        return back()->with('success', $added ? 'Ajouté aux favoris.' : 'Retiré des favoris.');
    }
}
