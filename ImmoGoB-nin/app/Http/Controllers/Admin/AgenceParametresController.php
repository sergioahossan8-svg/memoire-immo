<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgenceParametresController extends Controller
{
    public function index()
    {
        $adminAgence = auth()->user()->adminAgence;
        abort_if(!$adminAgence || !$adminAgence->est_principal, 403, 'Accès réservé à l\'administrateur principal.');
        $agence = $adminAgence->agence;
        return view('admin.agence-parametres', compact('agence'));
    }

    public function update(Request $request)
    {
        $adminAgence = auth()->user()->adminAgence;
        abort_if(!$adminAgence || !$adminAgence->est_principal, 403);

        $agence = $adminAgence->agence;

        $data = $request->validate([
            'nom_commercial'     => 'required|string|max:200',
            'telephone'          => 'nullable|string|max:20',
            'logo'               => 'nullable|image|max:2048',
            'kkiapay_public_key' => 'nullable|string|max:100',
            'kkiapay_private_key'=> 'nullable|string|max:100',
            'kkiapay_secret'     => 'nullable|string|max:100',
            'kkiapay_sandbox'    => 'nullable',
        ]);

        if ($request->hasFile('logo')) {
            if ($agence->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($agence->logo);
            }
            $data['logo'] = $request->file('logo')->store('agences', 'public');
        }

        // Ne pas écraser les clés si laissées vides
        if (empty($data['kkiapay_private_key'])) unset($data['kkiapay_private_key']);
        if (empty($data['kkiapay_secret']))       unset($data['kkiapay_secret']);

        $data['kkiapay_sandbox'] = $request->input('kkiapay_sandbox', '1') === '1';

        $agence->update($data);

        \App\Models\ActivityLog::log('agence_updated', 'Paramètres agence mis à jour : ' . $agence->nom_commercial, $agence);

        return back()->with('success', 'Paramètres de l\'agence mis à jour.');
    }
}
