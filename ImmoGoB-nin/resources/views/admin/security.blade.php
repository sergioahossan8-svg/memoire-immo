@extends('layouts.admin')

@section('title', 'Sécurité & Logs')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Sécurité & Logs</h1>
        <p class="text-gray-500 text-sm">Historique des actions effectuées par les administrateurs de votre agence.</p>
    </div>
</div>

<div class="card overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Action</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Administrateur</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Description</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">IP</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($logs as $log)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        @php
                            $colors = [
                                'login'        => 'bg-green-100 text-green-700',
                                'logout'       => 'bg-gray-100 text-gray-600',
                                'bien_created' => 'bg-cyan-100 text-cyan-700',
                                'bien_updated' => 'bg-blue-100 text-blue-700',
                                'bien_deleted' => 'bg-red-100 text-red-700',
                                'bien_statut'  => 'bg-orange-100 text-orange-700',
                            ];
                            $color = $colors[$log->action] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $color }}">
                            {{ str_replace('_', ' ', $log->action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-800">
                            {{ $log->user?->prenom }} {{ $log->user?->name }}
                        </p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $log->description }}</td>
                    <td class="px-6 py-4 text-xs text-gray-400 font-mono">{{ $log->ip_address }}</td>
                    <td class="px-6 py-4 text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-shield-alt text-4xl mb-3 block text-gray-200"></i>
                        Aucune activité enregistrée.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
</div>
@endsection
