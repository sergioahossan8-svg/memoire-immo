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
                    <img src="{{ Storage::url($photo->chemin) }}" class="w-full h-full object-cover">
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
                    <span class="ml-auto text-xs text-gray-400 italic">Défini par l'agence</span>
                </div>
                {{-- Champ caché pour soumettre la valeur --}}
                <input type="hidden" name="type_contrat" value="{{ $bien->transaction }}">
            </div>

            <div>
                <label class="form-label">Date limite de paiement du solde</label>
                <input type="date" name="date_limite" class="form-input"
                    min="{{ now()->addDay()->format('Y-m-d') }}"
                    value="{{ now()->addDays(30)->format('Y-m-d') }}"
                    required>
                @error('date_limite')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Mode de paiement de l'acompte</label>
                <select name="mode_paiement" class="form-input" required>
                    <option value="mobile_money">Mobile Money (MTN, Moov)</option>
                    <option value="virement">Virement bancaire</option>
                    <option value="especes">Espèces</option>
                    <option value="carte">Carte bancaire</option>
                </select>
            </div>

            {{-- FedaPay badge --}}
            <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-4">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt text-blue-500"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Paiement sécurisé via FedaPay</p>
                    <p class="text-xs text-gray-500">Vous serez redirigé vers FedaPay pour effectuer le paiement.</p>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-calendar-check"></i> Confirmer la réservation
            </button>
            <a href="{{ route('biens.show', $bien) }}" class="btn-secondary w-full text-center block">Annuler</a>
        </form>
    </div>
</div>
@endsection
