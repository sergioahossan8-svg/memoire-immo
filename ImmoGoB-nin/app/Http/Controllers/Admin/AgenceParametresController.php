<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgenceParametresController extends Controller
{
    public function index()
    {
        // Seul l'admin principal peut accéder
        abort_if(!auth()->user()->est_principal, 403, 'Accès réservé à l\'administrateur principal.');
        $agence = auth()->user()->agence;
        return view('admin.agence-parametres', compact('agence'));
    }

    public function update(Request $request)
    {
        abort_if(!auth()->user()->est_principal, 403);

        $agence = auth()->user()->agence;

        $data = $request->validate([
            'nom_commercial'     => 'required|string|max:200',
            'telephone'          => 'nullable|string|max:20',
            'logo'               => 'nullable|image|max:2048',
            'fedapay_secret_key' => ['nullable', 'string', 'max:100', function($attr, $val, $fail) {
                if (!empty($val) && str_starts_with($val, 'pk_')) {
                    $fail('Vous avez saisi la clé PUBLIQUE. Veuillez saisir la clé SECRÈTE (commence par sk_).');
                }
            }],
            'fedapay_env'        => 'nullable|in:sandbox,live',
        ]);

        if ($request->hasFile('logo')) {
            if ($agence->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($agence->logo);
            }
            $data['logo'] = $request->file('logo')->store('agences', 'public');
        }

        // Ne pas écraser la clé FedaPay si le champ est vide ou masqué
        if (empty($data['fedapay_secret_key']) || str_contains($data['fedapay_secret_key'] ?? '', '•')) {
            unset($data['fedapay_secret_key']);
        }

        $agence->update($data);

        \App\Models\ActivityLog::log('agence_updated', 'Paramètres agence mis à jour : ' . $agence->nom_commercial, $agence);

        return back()->with('success', 'Paramètres de l\'agence mis à jour.');
    }
}
