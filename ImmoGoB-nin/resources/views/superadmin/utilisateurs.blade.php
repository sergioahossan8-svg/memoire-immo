@extends('layouts.admin')

@section('title', 'Utilisateurs')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Utilisateurs</h1>
        <p class="text-gray-500 text-sm">{{ $utilisateurs->total() }} utilisateurs au total</p>
    </div>
</div>

<div class="card overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Utilisateur</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Email</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Rôle</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Agence</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Inscrit le</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($utilisateurs as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-cyan-400 flex items-center justify-center text-white text-xs font-semibold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <p class="text-sm font-medium text-gray-800">{{ $user->prenom }} {{ $user->name }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full
                            {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-700' : ($user->role === 'admin_agence' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ str_replace('_', ' ', ucfirst($user->role)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->agence?->nom_commercial ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $utilisateurs->links() }}</div>
</div>
@endsection
