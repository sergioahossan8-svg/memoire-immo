@extends('layouts.app')

@section('title', 'Mon Historique - ImmoGo')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Mon Historique</h1>

    @forelse($contrats as $contrat)
        @php $photo = $contrat->bien->photos->first(); @endphp
        <div class="card p-5 mb-4 flex gap-4">
            <div class="w-24 h-20 rounded-xl overflow-hidden flex-shrink-0">
                @if($photo)
                    <img src="{{ Storage::url($photo->chemin) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-home text-gray-300 text-2xl"></i>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $contrat->bien->titre }}</h3>
                        <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                            <i class="fas fa-map-marker-alt text-cyan-400"></i>
                            {{ $contrat->bien->localisation }}, {{ $contrat->bien->ville }}
                        </p>
                    </div>
                    <span class="badge-{{ $contrat->statut_contrat === 'actif' ? 'disponible' : ($contrat->statut_contrat === 'en_attente' ? 'reserve' : 'vendu') }}">
                        {{ ucfirst(str_replace('_', ' ', $contrat->statut_contrat)) }}
                    </span>
                </div>
                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                    <span><i class="fas fa-tag mr-1 text-cyan-400"></i>{{ ucfirst($contrat->type_contrat) }}</span>
                    <span><i class="fas fa-money-bill mr-1 text-cyan-400"></i>{{ number_format($contrat->getMontantTotal(), 0, ',', ' ') }} FCFA</span>
                    <span><i class="fas fa-calendar mr-1 text-cyan-400"></i>{{ $contrat->date_contrat->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center gap-3 mt-3">
                    @if($contrat->statut_contrat === 'en_attente' && $contrat->getSoldeRestant() > 0)
                        <a href="{{ route('client.payer.solde', $contrat) }}"
                            class="text-xs bg-cyan-400 hover:bg-cyan-500 text-white font-medium px-3 py-1.5 rounded-lg transition">
                            Payer le solde ({{ number_format($contrat->getSoldeRestant(), 0, ',', ' ') }} FCFA)
                        </a>
                    @endif
                    <a href="{{ route('biens.show', $contrat->bien) }}" class="text-xs text-cyan-500 hover:underline">
                        Voir l'annonce
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-16">
            <i class="fas fa-history text-5xl text-gray-200 mb-4"></i>
            <p class="text-gray-400">Aucune transaction pour le moment.</p>
            <a href="{{ route('biens.liste') }}" class="text-cyan-500 text-sm mt-2 inline-block hover:underline">Explorer les biens</a>
        </div>
    @endforelse
</div>
@endsection
