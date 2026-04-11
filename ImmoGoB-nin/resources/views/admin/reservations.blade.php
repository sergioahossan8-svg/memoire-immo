@extends('layouts.admin')

@section('title', 'Réservations')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Réservations & Contrats</h1>
        <p class="text-gray-500 text-sm">{{ $reservations->total() }} contrats au total</p>
    </div>
</div>

<div class="card overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Bien</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Client</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Type</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Montant</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Payé</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($reservations as $contrat)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800">{{ $contrat->bien->titre }}</p>
                        <p class="text-xs text-gray-400">{{ $contrat->bien->ville }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-700">{{ $contrat->client?->prenom }} {{ $contrat->client?->name }}</p>
                        <p class="text-xs text-gray-400">{{ $contrat->client?->telephone }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-full">{{ ucfirst($contrat->type_contrat) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ number_format($contrat->getMontantTotal(), 0, ',', ' ') }} FCFA</td>
                    <td class="px-6 py-4 text-sm text-green-600 font-medium">{{ number_format($contrat->getMontantPaye(), 0, ',', ' ') }} FCFA</td>
                    <td class="px-6 py-4">
                        <span class="badge-{{ $contrat->statut_contrat === 'actif' ? 'disponible' : ($contrat->statut_contrat === 'en_attente' ? 'reserve' : 'vendu') }}">
                            {{ ucfirst(str_replace('_', ' ', $contrat->statut_contrat)) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">Aucune réservation.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $reservations->links() }}</div>
</div>
@endsection
