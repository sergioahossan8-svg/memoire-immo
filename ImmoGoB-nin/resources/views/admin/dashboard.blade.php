@extends('layouts.admin')

@section('title', 'Dashboard - ' . $agence->nom_commercial)
@section('search_placeholder', 'Rechercher un bien, un client...')

@section('content')
<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestion des Biens</h1>
        <p class="text-gray-500 text-sm">Déployez de nouvelles annonces et gérez vos biens.</p>
    </div>
    <div class="flex gap-3">
        <button onclick="window.open('{{ route('admin.biens.export-pdf') }}', '_blank')"
            class="flex items-center gap-2 border border-gray-200 text-gray-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-gray-50 transition">
            <i class="fas fa-file-pdf text-red-400 text-sm"></i> Exporter PDF
        </button>
        <a href="{{ route('admin.biens.create') }}" class="btn-primary text-sm">
            <i class="fas fa-plus"></i> Nouveau bien
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Biens Actifs</p>
            <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-home text-cyan-500 text-sm"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['biens_actifs'] }}</p>
        <p class="text-xs text-green-500 mt-1">+5 nouvelles cette semaine</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">En Vente</p>
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-tag text-blue-500 text-sm"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['biens_en_vente'] }}</p>
        <p class="text-xs text-gray-400 mt-1">99% de comptes vérifiés</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">En Location</p>
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-key text-green-500 text-sm"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['biens_en_location'] }}</p>
        <p class="text-xs text-green-500 mt-1">Record mensuel battu</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Réservations</p>
            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar text-orange-500 text-sm"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['reservations'] }}</p>
        <p class="text-xs text-orange-500 mt-1">En attente</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Biens récents --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Biens récents</h3>
            <a href="{{ route('admin.biens.index') }}" class="text-xs text-cyan-500 hover:underline">Voir tout</a>
        </div>
        @forelse($biensRecents as $bien)
            <div class="flex items-center gap-3 py-3 border-b border-gray-50 last:border-0">
                @php $photo = $bien->photos->first(); @endphp
                <div class="w-12 h-10 rounded-lg overflow-hidden flex-shrink-0">
                    @if($photo)
                        <img src="{{ str_starts_with($photo->chemin, 'http') ? $photo->chemin : asset('storage/' . $photo->chemin) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-home text-gray-300 text-xs"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $bien->titre }}</p>
                    <p class="text-xs text-gray-400">{{ $bien->ville }}</p>
                </div>
                <span class="badge-{{ $bien->statut }} flex-shrink-0">{{ ucfirst($bien->statut) }}</span>
            </div>
        @empty
            <p class="text-gray-400 text-sm text-center py-4">Aucun bien pour le moment.</p>
        @endforelse
    </div>

    {{-- Réservations récentes --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Réservations récentes</h3>
            <a href="{{ route('admin.reservations') }}" class="text-xs text-cyan-500 hover:underline">Voir tout</a>
        </div>
        @forelse($reservationsRecentes as $contrat)
            <div class="flex items-center gap-3 py-3 border-b border-gray-50 last:border-0">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-semibold text-gray-600">{{ strtoupper(substr($contrat->client->name, 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $contrat->client->prenom }} {{ $contrat->client->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $contrat->bien->titre }}</p>
                </div>
                <span class="text-xs font-semibold text-gray-600">{{ number_format($contrat->getMontantTotal(), 0, ',', ' ') }} FCFA</span>
            </div>
        @empty
            <p class="text-gray-400 text-sm text-center py-4">Aucune réservation.</p>
        @endforelse
    </div>
</div>
@endsection
