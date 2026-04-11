@extends('layouts.admin')

@section('title', $user->prenom . ' ' . $user->name)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.clients') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-800">{{ $user->prenom }} {{ $user->name }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card p-5">
        <div class="text-center mb-4">
            <div class="w-16 h-16 rounded-full bg-cyan-400 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-3">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <p class="font-semibold text-gray-800">{{ $user->prenom }} {{ $user->name }}</p>
            <p class="text-sm text-gray-400">{{ $user->email }}</p>
            <p class="text-sm text-gray-400">{{ $user->telephone }}</p>
        </div>
        <div class="text-center">
            <span class="badge-disponible">{{ $contrats->count() }} contrat(s)</span>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <h3 class="font-semibold text-gray-800">Historique des contrats</h3>
        @forelse($contrats as $contrat)
            <div class="card p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-medium text-gray-800 text-sm">{{ $contrat->bien->titre }}</p>
                    <span class="badge-{{ $contrat->statut_contrat === 'actif' ? 'disponible' : 'reserve' }}">
                        {{ ucfirst(str_replace('_', ' ', $contrat->statut_contrat)) }}
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span>{{ ucfirst($contrat->type_contrat) }}</span>
                    <span>{{ number_format($contrat->getMontantTotal(), 0, ',', ' ') }} FCFA</span>
                    <span>Payé: {{ number_format($contrat->getMontantPaye(), 0, ',', ' ') }} FCFA</span>
                    <span>Solde: {{ number_format($contrat->getSoldeRestant(), 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm">Aucun contrat.</p>
        @endforelse
    </div>
</div>
@endsection
