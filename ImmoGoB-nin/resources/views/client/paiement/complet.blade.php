@extends('layouts.app')

@section('title', 'Paiement complet - ImmoGo')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="card p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Paiement complet</h1>
        <p class="text-gray-500 text-sm mb-6">Payez la totalité du montant pour finaliser votre acquisition.</p>

        @php
            $photo = $bien->photos->first();
            $montantTotal = $bien->transaction === 'location' ? $bien->montant_total_location : (float) $bien->prix;
        @endphp
        <div class="bg-gray-50 rounded-xl p-4 mb-4 flex gap-4">
            <div class="w-20 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-gray-200">
                @if($photo)
                    <img src="{{ str_starts_with($photo->chemin, 'http') ? $photo->chemin : asset('storage/' . $photo->chemin) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-home text-gray-300"></i>
                    </div>
                @endif
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $bien->titre }}</p>
                <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                    <i class="fas fa-map-marker-alt text-cyan-400"></i>
                    {{ $bien->localisation }}, {{ $bien->ville }}
                </p>
                <p class="text-xl font-bold text-cyan-500 mt-1">{{ $bien->prix_formate }}</p>
            </div>
        </div>

        {{-- Conditions de location --}}
        @if($bien->transaction === 'location')
            <div class="border border-cyan-200 bg-cyan-50 rounded-xl p-4 mb-4">
                <p class="text-xs font-bold text-cyan-700 uppercase tracking-wider mb-3">
                    <i class="fas fa-file-contract mr-1"></i> Conditions de location
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><i class="fas fa-home text-cyan-400 mr-1"></i> Loyer mensuel</span>
                        <span class="font-semibold">{{ $bien->prix_formate }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><i class="fas fa-calendar-alt text-cyan-400 mr-1"></i> Avance ({{ $bien->avance_mois ?? 1 }} mois)</span>
                        <span class="font-semibold">{{ number_format($bien->prix * ($bien->avance_mois ?? 1), 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if($bien->caution_eau > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600"><i class="fas fa-tint text-blue-400 mr-1"></i> Caution eau</span>
                            <span class="font-semibold">{{ number_format($bien->caution_eau, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if($bien->caution_electricite > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600"><i class="fas fa-bolt text-yellow-400 mr-1"></i> Caution électricité</span>
                            <span class="font-semibold">{{ number_format($bien->caution_electricite, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div class="border-t border-cyan-200 pt-2 flex justify-between items-center font-bold text-cyan-800 text-base">
                        <span><i class="fas fa-calculator mr-1"></i> Total à payer</span>
                        <span>{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->has('kkiapay'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first('kkiapay') }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.payer.complet.post', $bien) }}" class="space-y-4" id="formComplet">
            @csrf

            {{-- Type de contrat : défini par l'admin, non modifiable --}}
            <div>
                <label class="form-label">Type de contrat</label>
                <div class="form-input bg-gray-50 flex items-center gap-2 cursor-not-allowed">
                    <i class="fas fa-{{ $bien->transaction === 'location' ? 'key' : 'home' }} text-cyan-400 text-sm"></i>
                    <span class="text-gray-700 font-medium">
                        {{ $bien->transaction === 'location' ? 'Location' : 'Vente' }}
                    </span>
                    <span class="ml-auto text-xs text-gray-400 italic">Non modifiable</span>
                </div>
                <input type="hidden" name="type_contrat" value="{{ $bien->transaction }}">
            </div>

            {{-- Mode de paiement --}}
            <div>
                <label class="form-label">Mode de paiement</label>
                <select name="mode_paiement" id="modePaiement" class="form-input" required onchange="toggleEspeces()">
                    <option value="mobile_money">Mobile Money (MTN, Moov)</option>
                    <option value="virement">Virement bancaire</option>
                    <option value="carte">Carte bancaire</option>
                    <option value="especes">Espèces (paiement sur place)</option>
                </select>
            </div>

            {{-- Infos bancaires virement --}}
            @if($bien->agence && $bien->agence->hasBanque())
                <div id="infoVirement" class="hidden border border-blue-200 bg-blue-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-3 flex items-center gap-1">
                        <i class="fas fa-university"></i> Coordonnées bancaires pour le virement
                    </p>
                    <div class="space-y-2 text-sm">
                        @if($bien->agence->banque_nom)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Banque</span>
                                <span class="font-semibold text-gray-800">{{ $bien->agence->banque_nom }}</span>
                            </div>
                        @endif
                        @if($bien->agence->banque_titulaire)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Titulaire</span>
                                <span class="font-semibold text-gray-800">{{ $bien->agence->banque_titulaire }}</span>
                            </div>
                        @endif
                        @if($bien->agence->banque_iban)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">IBAN / N° compte</span>
                                <span class="font-mono font-bold text-blue-700 text-xs bg-white px-2 py-1 rounded-lg border border-blue-200 select-all">{{ $bien->agence->banque_iban }}</span>
                            </div>
                        @endif
                        @if($bien->agence->banque_swift)
                            <div class="flex justify-between">
                                <span class="text-gray-500">SWIFT / BIC</span>
                                <span class="font-mono font-semibold text-gray-800">{{ $bien->agence->banque_swift }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center pt-1 border-t border-blue-200">
                            <span class="text-gray-500">Montant à virer</span>
                            <span class="font-bold text-blue-700">{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                    <div class="mt-3 bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Après le virement, présentez votre <strong>reçu bancaire</strong> à l'agence
                        <strong>{{ $bien->agence->nom_commercial }}</strong>. L'agence confirmera votre paiement.
                    </div>
                </div>
            @endif

            {{-- Message espèces (affiché uniquement si espèces sélectionné) --}}
            <div id="infoEspeces" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-4">                <div class="flex items-start gap-3">
                    <i class="fas fa-money-bill-wave text-amber-500 mt-0.5 flex-shrink-0"></i>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold mb-1">Paiement en espèces sur place</p>
                        <p class="text-xs leading-relaxed">
                            Rendez-vous directement au siège de l'agence
                            <strong>{{ $bien->agence?->nom_commercial }}</strong>
                            @if($bien->agence?->adresse_complete)
                                — {{ $bien->agence->adresse_complete }}{{ $bien->agence->ville ? ', ' . $bien->agence->ville : '' }}
                            @elseif($bien->agence?->ville)
                                — {{ $bien->agence->ville }}
                            @endif
                            pour régler <strong>{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</strong> en espèces.
                        </p>
                        @if($bien->agence?->telephone)
                            <p class="mt-2 text-xs">
                                <i class="fas fa-phone mr-1"></i>
                                Contactez l'agence :
                                <a href="tel:{{ $bien->agence->telephone }}" class="font-semibold text-amber-700 hover:underline">
                                    {{ $bien->agence->telephone }}
                                </a>
                            </p>
                        @endif
                        @php
                            $waClean = $bien->agence?->adminPrincipal?->whatsapp
                                ? preg_replace('/[^0-9]/', '', $bien->agence->adminPrincipal->whatsapp)
                                : null;
                            $waMsg = urlencode('Bonjour, je souhaite acquérir le bien : ' . $bien->titre . ' et payer en espèces.');
                        @endphp
                        @if($waClean)
                            <a href="https://wa.me/{{ $waClean }}?text={{ $waMsg }}" target="_blank"
                                class="mt-2 inline-flex items-center gap-1.5 text-xs bg-green-50 text-green-700 font-medium px-3 py-1.5 rounded-lg hover:bg-green-100 transition">
                                <i class="fab fa-whatsapp"></i> WhatsApp agence
                            </a>
                        @endif
                        <p class="mt-2 text-xs text-amber-600 italic">
                            Une fois votre paiement effectué sur place, l'agence confirmera et le bien sera marqué comme acquis.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Badge paiement sécurisé (caché si espèces) --}}
            <div id="badgeSecurise" class="flex items-center gap-3 bg-blue-50 rounded-xl p-4">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt text-blue-500"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Paiement 100% sécurisé</p>
                    <p class="text-xs text-gray-500">Mobile Money (MTN, Moov), carte bancaire et plus</p>
                </div>
            </div>

            <button type="submit" id="btnPayer" class="btn-primary w-full">
                <i class="fas fa-lock"></i> Payer {{ number_format($montantTotal, 0, ',', ' ') }} FCFA
            </button>
            <a href="{{ route('biens.show', $bien) }}" class="btn-secondary w-full text-center block">Annuler</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleEspeces() {
        const mode = document.getElementById('modePaiement').value;
        const infoEspeces   = document.getElementById('infoEspeces');
        const infoVirement  = document.getElementById('infoVirement');
        const badgeSecurise = document.getElementById('badgeSecurise');
        const btnPayer      = document.getElementById('btnPayer');

        // Cacher tous les blocs d'abord
        if (infoEspeces)  infoEspeces.classList.add('hidden');
        if (infoVirement) infoVirement.classList.add('hidden');

        if (mode === 'especes') {
            if (infoEspeces) infoEspeces.classList.remove('hidden');
            badgeSecurise.classList.add('hidden');
            btnPayer.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i> Je vais payer sur place';
            btnPayer.className = 'w-full bg-amber-400 hover:bg-amber-500 text-white font-semibold py-3 rounded-xl transition';
        } else if (mode === 'virement') {
            if (infoVirement) infoVirement.classList.remove('hidden');
            badgeSecurise.classList.add('hidden');
            btnPayer.innerHTML = '<i class="fas fa-university mr-2"></i> Je vais effectuer le virement';
            btnPayer.className = 'w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-xl transition';
        } else {
            badgeSecurise.classList.remove('hidden');
            btnPayer.innerHTML = '<i class="fas fa-lock mr-2"></i> Payer {{ number_format($montantTotal, 0, ',', ' ') }} FCFA';
            btnPayer.className = 'btn-primary w-full';
        }
    }
</script>
@endpush
@endsection
