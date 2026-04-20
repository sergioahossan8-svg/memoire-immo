@extends('layouts.admin')

@section('title', 'Administrateurs')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Administrateurs</h1>
        <p class="text-gray-500 text-sm">{{ $agence->nom_commercial }}</p>
    </div>
    @if(auth()->user()->adminAgence?->est_principal)
        <a href="{{ route('admin.administrateurs.create') }}" class="btn-primary text-sm">
            <i class="fas fa-plus"></i> Nouvel administrateur
        </a>
    @endif
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif

{{-- Bouton modifier mon profil pour les associés --}}
@if(!auth()->user()->adminAgence?->est_principal)
<div class="bg-cyan-50 border border-cyan-100 rounded-xl px-4 py-3 mb-4 flex items-center justify-between">
    <span class="text-sm text-cyan-700">
        <i class="fas fa-info-circle mr-1"></i>
        Vous êtes administrateur associé. Vous pouvez modifier votre propre profil.
    </span>
    <a href="{{ route('admin.administrateurs.edit', auth()->user()) }}" class="btn-primary text-xs">
        <i class="fas fa-user-edit"></i> Modifier mon profil
    </a>
</div>
@endif

{{-- Changer mot de passe (accessible à tous les admins) --}}
<div class="card p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">
        <i class="fas fa-lock text-cyan-500 mr-2"></i>Changer mon mot de passe
    </h3>
    <form method="POST" action="{{ route('admin.password.update') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf @method('PATCH')
        <div>
            <label class="form-label">Mot de passe actuel</label>
            <input type="password" name="current_password" class="form-input @error('current_password') border-red-400 @enderror" required>
            @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" name="password" class="form-input @error('password') border-red-400 @enderror" required>
            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Confirmation</label>
            <input type="password" name="password_confirmation" class="form-input" required>
        </div>
        <div class="md:col-span-3">
            <button type="submit" class="btn-primary text-sm">
                <i class="fas fa-lock"></i> Mettre à jour le mot de passe
            </button>
        </div>
    </form>
</div>

{{-- Liste des administrateurs (visible par tous, actions réservées au principal) --}}
<div class="card overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Administrateur</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Contact</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Rôle</th>
                @if(auth()->user()->adminAgence?->est_principal)
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($admins as $admin)
                @php $adminUser = $admin->user; @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-cyan-400 flex items-center justify-center text-white text-sm font-semibold">
                                {{ strtoupper(substr($adminUser?->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $adminUser?->prenom }} {{ $adminUser?->name }}</p>
                                @if($admin->est_principal)
                                    <span class="text-xs text-cyan-500 font-medium">Admin Principal</span>
                                @else
                                    <span class="text-xs text-gray-400">Associé</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600">{{ $adminUser?->email }}</p>
                        <p class="text-xs text-gray-400">{{ $adminUser?->telephone }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="badge-{{ $admin->est_principal ? 'disponible' : 'reserve' }}">
                            {{ $admin->est_principal ? 'Principal' : 'Associé' }}
                        </span>
                    </td>
                    @if(auth()->user()->adminAgence?->est_principal)
                        <td class="px-6 py-4 text-right">
                            @if(!$admin->est_principal)
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.administrateurs.edit', $adminUser) }}" class="p-2 text-gray-400 hover:text-cyan-500 transition" title="Modifier">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.administrateurs.destroy', $adminUser) }}" onsubmit="return confirm('Supprimer cet administrateur associé ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition" title="Supprimer">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
