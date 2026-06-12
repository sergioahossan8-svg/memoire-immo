@extends('layouts.app')

@section('title', 'Réserver - ' . $bien->titre)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="card p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Réserver ce bien</h1>
        <p class="text-gray-500 text-sm mb-6">Payez 10% d'acompte pour confirmer votre réservation.</p>

        {{-- Récap bien --}}
        @php $photo = $bien->photos->first(); @endphp
        <div class="bg-gray-50 rounded-xl p-4 mb-6 flex gap-4">
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
                <p class="text-cyan-500 font-bold mt-1">{{ $bien->prix_formate }}</p>
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
                        <span class="font-semibold text-gray-800">{{ $bien->prix_formate }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600"><i class="fas fa-calendar-alt text-cyan-400 mr-1"></i> Avance ({{ $bien->avance_mois ?? 1 }} mois)</span>
                        <span class="font-semibold text-gray-800">{{ number_format($bien->prix * ($bien->avance_mois ?? 1), 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if($bien->caution_eau > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600"><i class="fas fa-tint text-blue-400 mr-1"></i> Caution eau</span>
                            <span class="font-semibold text-gray-800">{{ number_format($bien->caution_eau, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if($bien->caution_electricite > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600"><i class="fas fa-bolt text-yellow-400 mr-1"></i> Caution électricité</span>
                            <span class="font-semibold text-gray-800">{{ number_format($bien->caution_electricite, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div class="border-t border-cyan-200 pt-2 flex justify-between items-center font-bold text-cyan-800">
                        <span><i class="fas fa-calculator mr-1"></i> Total à régler</span>
                        <span class="text-base">{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-cyan-50 rounded-xl p-4 mb-6">
            <p class="text-sm text-cyan-700 font-medium">
                <i class="fas fa-info-circle mr-1"></i>
                Montant de l'acompte (10%) : <strong>{{ number_format($acompte, 0, ',', ' ') }} FCFA</strong>
            </p>
        </div>

        @if($errors->has('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.reserver.post', $bien) }}" class="space-y-4">
            @csrf

            {{-- Type de contrat : défini par l'admin, non modifiable --}}
            <div>
                <label class="form-label">Type de contrat</label>
                <div class="form-input bg-gray-50 flex items-center gap-2 cursor-not-allowed">
                    <i class="fas fa-{{ $bien->transaction === 'location' ? 'key' : 'home' }} text-cyan-400 text-sm"></i>
                    <span class="text-gray-700 font-medium">
                        {{ $bien->transaction === 'location' ? 'Location' : 'Vente' }}
                    </span>
                </div>
                {{-- Champ caché pour soumettre la valeur --}}
                <input type="hidden" name="type_contrat" value="{{ $bien->transaction }}">
            </div>

            {{-- Date limite fixe = now + 15 jours (non modifiable par le client) --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Délai de paiement : 15 jours</p>
                        <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                            Vous avez 15 jours pour solder votre réservation. Passé ce délai, votre réservation sera annulée,
                            le bien redeviendra disponible et votre argent vous sera restitué.
                        </p>
                    </div>
                </div>
            </div>
            {{-- Champ caché : date limite = aujourd'hui + 15 jours --}}
            <input type="hidden" name="date_limite" value="{{ now()->addDays(15)->format('Y-m-d') }}">

            <div>
                <label class="form-label">Mode de paiement de l'acompte</label>
                <select name="mode_paiement" id="modePaiementReserv" class="form-input" required onchange="toggleVirementInfo()">
                    <option value="mobile_money">Mobile Money (MTN, Moov)</option>
                    <option value="virement">Virement bancaire</option>
                    <option value="carte">Carte bancaire</option>
                </select>
            </div>

            {{-- Infos bancaires pour virement (affichées dynamiquement) --}}
            @php $agence = $bien->agence; @endphp
            @if($agence && $agence->hasBanque())
                <div id="virementInfo" class="hidden border border-blue-200 bg-blue-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-3 flex items-center gap-1">
                        <i class="fas fa-university"></i> Coordonnées bancaires pour le virement
                    </p>
                    <div class="space-y-2 text-sm">
                        @if($agence->banque_nom)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Banque</span>
                                <span class="font-semibold text-gray-800">{{ $agence->banque_nom }}</span>
                            </div>
                        @endif
                        @if($agence->banque_titulaire)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Titulaire</span>
                                <span class="font-semibold text-gray-800">{{ $agence->banque_titulaire }}</span>
                            </div>
                        @endif
                        @if($agence->banque_iban)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">IBAN / N° compte</span>
                                <span class="font-mono font-bold text-blue-700 text-xs bg-white px-2 py-1 rounded-lg border border-blue-200 select-all">{{ $agence->banque_iban }}</span>
                            </div>
                        @endif
                        @if($agence->banque_swift)
                            <div class="flex justify-between">
                                <span class="text-gray-500">SWIFT / BIC</span>
                                <span class="font-mono font-semibold text-gray-800">{{ $agence->banque_swift }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center pt-1 border-t border-blue-200">
                            <span class="text-gray-500">Montant à virer</span>
                            <span class="font-bold text-blue-700">{{ number_format($acompte, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                    <div class="mt-3 bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Après avoir effectué le virement, présentez-vous à l'agence
                        <strong>{{ $agence->nom_commercial }}</strong> avec votre reçu bancaire.
                        L'agence confirmera votre réservation.
                    </div>
                </div>
            @endif

            {{-- Badge paiement sécurisé --}}
            <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-4">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt text-blue-500"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Paiement 100% sécurisé</p>
                    <p class="text-xs text-gray-500">Vos données sont protégées.</p>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-calendar-check"></i> Confirmer la réservation
            </button>
            <a href="{{ route('biens.show', $bien) }}" class="btn-secondary w-full text-center block">Annuler</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleVirementInfo() {
    const mode = document.getElementById('modePaiementReserv').value;
    const info = document.getElementById('virementInfo');
    if (info) {
        info.classList.toggle('hidden', mode !== 'virement');
    }
}
</script>
@endpush
@endsection
