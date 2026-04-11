@extends('layouts.app')

@section('title', 'Estimer mon bien - ImmoGo')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="text-center mb-8">
        <span class="bg-orange-100 text-orange-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Nouveau</span>
        <h1 class="text-3xl font-bold text-gray-800 mt-3 mb-2">Estimation gratuite de votre bien</h1>
        <p class="text-gray-500">Obtenez une fourchette de prix en 2 minutes basée sur les biens similaires au Bénin.</p>
    </div>

    {{-- Formulaire --}}
    <div class="card p-8 mb-8">
        <form method="POST" action="{{ route('estimation.post') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type de bien</label>
                    <select name="type_bien_id" class="form-input" required>
                        <option value="">Sélectionner...</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}"
                                {{ (old('type_bien_id', $data['type_bien_id'] ?? '') == $type->id) ? 'selected' : '' }}>
                                {{ $type->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Type de contrat</label>
                    <select name="transaction" class="form-input" required>
                        <option value="location" {{ (old('transaction', $data['transaction'] ?? '') === 'location') ? 'selected' : '' }}>Location</option>
                        <option value="vente" {{ (old('transaction', $data['transaction'] ?? '') === 'vente') ? 'selected' : '' }}>Vente</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville', $data['ville'] ?? '') }}"
                        placeholder="ex: Cotonou, Parakou..." class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Superficie (m²)</label>
                    <input type="number" name="superficie" value="{{ old('superficie', $data['superficie'] ?? '') }}"
                        placeholder="ex: 80" class="form-input" min="1" required>
                </div>
            </div>

            <div>
                <label class="form-label">Nombre de chambres (optionnel)</label>
                <input type="number" name="chambres" value="{{ old('chambres', $data['chambres'] ?? '') }}"
                    placeholder="ex: 3" class="form-input" min="0">
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-calculator"></i> Estimer maintenant
            </button>
        </form>
    </div>

    {{-- Résultat --}}
    @isset($estimation)
        <div class="card p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-orange-500"></i>
                </div>
                <div>
                    <h2 class="font-bold text-gray-800">Résultat de l'estimation</h2>
                    <p class="text-xs text-gray-400">
                        Basé sur {{ $estimation['nb_biens'] }} bien(s) similaire(s) à {{ $estimation['ville'] }}
                    </p>
                </div>
            </div>

            {{-- Fourchette principale --}}
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-2xl p-6 mb-6 text-center">
                <p class="text-sm text-gray-500 mb-1">Fourchette estimée</p>
                <p class="text-3xl font-bold text-gray-800">
                    {{ number_format($estimation['min'], 0, ',', ' ') }}
                    <span class="text-gray-400 text-xl">—</span>
                    {{ number_format($estimation['max'], 0, ',', ' ') }} FCFA
                </p>
                @if($estimation['transaction'] === 'location')
                    <p class="text-xs text-gray-400 mt-1">/ mois</p>
                @endif
            </div>

            {{-- Détails --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-400 mb-1">Prix estimé</p>
                    <p class="font-bold text-gray-800 text-sm">{{ number_format($estimation['moyen'], 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-400 mb-1">Prix au m²</p>
                    <p class="font-bold text-gray-800 text-sm">{{ number_format($estimation['prix_m2'], 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-400 mb-1">Superficie</p>
                    <p class="font-bold text-gray-800 text-sm">{{ $estimation['superficie'] }} m²</p>
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-4 text-xs text-blue-700">
                <i class="fas fa-info-circle mr-1"></i>
                Cette estimation est indicative et basée sur les annonces disponibles sur ImmoGo.
                Pour une évaluation précise, contactez une agence partenaire.
            </div>

            <div class="flex gap-3 mt-5">
                <a href="{{ route('biens.liste', ['ville' => $estimation['ville'], 'type_bien_id' => $data['type_bien_id']]) }}"
                    class="btn-primary flex-1 text-sm">
                    <i class="fas fa-search"></i> Voir les biens similaires
                </a>
                <a href="{{ route('estimation') }}" class="btn-secondary text-sm">
                    Nouvelle estimation
                </a>
            </div>
        </div>

    @elseif(isset($data))
        {{-- Formulaire soumis mais pas assez de données --}}
        <div class="card p-8 text-center">
            <i class="fas fa-database text-4xl text-gray-200 mb-4"></i>
            <h3 class="font-semibold text-gray-700 mb-2">Données insuffisantes</h3>
            <p class="text-gray-500 text-sm mb-4">
                Aucun bien similaire n'a été trouvé à <strong>{{ $data['ville'] }}</strong> pour effectuer une estimation fiable.
                Essayez avec une autre ville ou un autre type de bien.
            </p>
            <a href="{{ route('estimation') }}" class="btn-secondary inline-flex">Réessayer</a>
        </div>
    @endisset

    {{-- Comment ça marche --}}
    @if(!isset($data))
        <div class="grid grid-cols-3 gap-4 mt-8">
            <div class="text-center">
                <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-edit text-orange-500"></i>
                </div>
                <p class="text-sm font-semibold text-gray-700">1. Décrivez votre bien</p>
                <p class="text-xs text-gray-400 mt-1">Type, superficie, ville et nombre de chambres</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-chart-bar text-orange-500"></i>
                </div>
                <p class="text-sm font-semibold text-gray-700">2. Analyse du marché</p>
                <p class="text-xs text-gray-400 mt-1">Comparaison avec les biens similaires sur ImmoGo</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-orange-500"></i>
                </div>
                <p class="text-sm font-semibold text-gray-700">3. Résultat instantané</p>
                <p class="text-xs text-gray-400 mt-1">Fourchette de prix et prix au m²</p>
            </div>
        </div>
    @endif
</div>
@endsection
