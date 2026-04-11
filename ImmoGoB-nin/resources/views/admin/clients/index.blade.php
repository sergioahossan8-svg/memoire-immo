@extends('layouts.admin')

@section('title', 'Clients')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Clients</h1>
        <p class="text-gray-500 text-sm">{{ $clients->total() }} clients enregistrés au total</p>
    </div>
</div>

<div class="card overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Client</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Contact</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Contrats</th>
                <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($clients as $client)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-cyan-400 flex items-center justify-center text-white text-sm font-semibold">
                                {{ strtoupper(substr($client->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $client->prenom }} {{ $client->name }}</p>
                                <p class="text-xs text-gray-400">Client</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600">{{ $client->email }}</p>
                        <p class="text-xs text-gray-400">{{ $client->telephone }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="badge-disponible">{{ $client->contrats_count }} contrat(s)</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.clients.show', $client) }}" class="p-2 text-gray-400 hover:text-cyan-500 transition">
                            <i class="fas fa-arrow-right text-sm"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">Aucun client pour le moment.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $clients->links() }}</div>
</div>
@endsection
