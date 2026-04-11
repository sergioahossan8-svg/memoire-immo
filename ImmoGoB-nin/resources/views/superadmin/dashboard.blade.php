@extends('layouts.admin')

@section('title', 'Dashboard SuperAdmin')
@section('search_placeholder', 'Rechercher une agence, un admin...')

@section('content')
<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Tableau de bord</h1>
        <p class="text-gray-500 text-sm">Vue d'ensemble de la plateforme ImmoGo.</p>
    </div>
    <a href="{{ route('superadmin.agences.create') }}" class="btn-primary text-sm">
        <i class="fas fa-plus"></i> Nouvelle Agence
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Agences Actives</p>
            <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-building text-cyan-500 text-sm"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['agences_actives'] }}</p>
        <p class="text-xs text-green-500 mt-1">+5 nouvelles cette semaine</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Administrateurs</p>
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-tie text-blue-500 text-sm"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['administrateurs'] }}</p>
        <p class="text-xs text-gray-400 mt-1">99% de comptes vérifiés</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Annonces Publiées</p>
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-list text-green-500 text-sm"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['annonces_publiees'] }}</p>
        <p class="text-xs text-green-500 mt-1">Record mensuel battu</p>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Clients</p>
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-purple-500 text-sm"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['clients'] }}</p>
        <p class="text-xs text-gray-400 mt-1">Taux de conversion global</p>
    </div>
</div>

{{-- Agences récentes --}}
<div class="card p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800">Agences récentes</h3>
        <a href="{{ route('superadmin.agences.index') }}" class="text-xs text-cyan-500 hover:underline">Voir tout</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="text-left text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
                <th class="pb-3">Agence</th>
                <th class="pb-3">Administrateur</th>
                <th class="pb-3">Statut</th>
                <th class="pb-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($agencesRecentes as $agence)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($agence->logo)
                                    <img src="{{ Storage::url($agence->logo) }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-building text-gray-400 text-sm"></i>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $agence->nom_commercial }}</p>
                                <p class="text-xs text-gray-400">{{ $agence->ville }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        @if($agence->adminPrincipal)
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-cyan-400 flex items-center justify-center text-white text-xs font-semibold">
                                    {{ strtoupper(substr($agence->adminPrincipal->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm text-gray-700">{{ $agence->adminPrincipal->prenom }} {{ $agence->adminPrincipal->name }}</p>
                                    <p class="text-xs text-gray-400">Gérant</p>
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">Aucun admin</span>
                        @endif
                    </td>
                    <td class="py-3">
                        <span class="badge-{{ $agence->statut === 'actif' ? 'disponible' : ($agence->statut === 'en_attente' ? 'reserve' : 'vendu') }}">
                            {{ ucfirst(str_replace('_', ' ', $agence->statut)) }}
                        </span>
                    </td>
                    <td class="py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('superadmin.agences.show', $agence) }}" class="p-2 text-gray-400 hover:text-cyan-500 transition">
                                <i class="fas fa-arrow-right text-sm"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
