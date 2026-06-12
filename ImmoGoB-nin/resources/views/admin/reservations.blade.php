@extends('layouts.admin')

@section('title', 'Réservations')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Réservations & Contrats</h1>
        <p class="text-gray-500 text-sm">{{ $reservations->total() }} contrats au total</p>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-4 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="card overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Bien</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Client</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Type</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Montant</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Payé</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Mode</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Statut</th>
                <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($reservations as $contrat)
                @php
                    $modePaiement = $contrat->paiements->first()?->mode_paiement ?? null;
                    // Contrat en espèces = pas encore de paiement confirmé
                    $estEspeces = $contrat->statut_contrat === 'en_attente'
                        && $contrat->getMontantPaye() == 0;
                @endphp
                <tr class="hover:bg-gray-50 transition {{ $estEspeces ? 'bg-amber-50/40' : '' }}">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800">{{ $contrat->bien->titre }}</p>
                        <p class="text-xs text-gray-400">{{ $contrat->bien->ville }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-700">{{ $contrat->client?->prenom }} {{ $contrat->client?->name }}</p>
                        <p class="text-xs text-gray-400">{{ $contrat->client?->telephone }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-full">
                            {{ ucfirst($contrat->type_contrat) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                        {{ number_format($contrat->getMontantTotal(), 0, ',', ' ') }} FCFA
                    </td>
                    <td class="px-6 py-4 text-sm text-green-600 font-medium">
                        {{ number_format($contrat->getMontantPaye(), 0, ',', ' ') }} FCFA
                    </td>
                    <td class="px-6 py-4">
                        @if($modePaiement === 'especes' || $estEspeces)
                            <span class="text-xs font-medium text-amber-700 bg-amber-100 px-2 py-1 rounded-full flex items-center gap-1 w-fit">
                                <i class="fas fa-money-bill-wave text-xs"></i> Espèces
                            </span>
                        @elseif($modePaiement)
                            <span class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $modePaiement)) }}</span>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($contrat->statut_contrat === 'actif')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Actif
                            </span>
                        @elseif($contrat->statut_contrat === 'en_attente')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span> En attente
                            </span>
                        @elseif($contrat->statut_contrat === 'termine')
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span> Terminé
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 border border-red-200 px-2 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span> Annulé
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        {{-- Bouton "Confirmer paiement espèces" uniquement si en attente sans paiement --}}
                        @if($estEspeces)
                            <form method="POST"
                                action="{{ route('admin.contrats.confirmer-especes', $contrat) }}"
                                onsubmit="return confirm('Confirmer que {{ $contrat->client?->prenom }} {{ $contrat->client?->name }} a bien payé l\'acompte en espèces ?')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 text-xs bg-amber-400 hover:bg-amber-500 text-white font-semibold px-3 py-2 rounded-lg transition whitespace-nowrap">
                                    <i class="fas fa-check text-xs"></i>
                                    Payé en espèces
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-file-contract text-4xl mb-3 block"></i>
                        Aucune réservation.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $reservations->links() }}</div>
</div>

{{-- Légende --}}
<div class="mt-4 flex items-center gap-6 text-xs text-gray-400">
    <span class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
        En attente espèces — cliquez "Payé en espèces" une fois le client passé payer
    </span>
    <span class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>
        Actif — paiement confirmé, bien réservé
    </span>
</div>
@endsection
