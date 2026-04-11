@extends('layouts.admin')

@section('title', 'Administrateurs')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Administrateurs</h1>
        <p class="text-gray-500 text-sm">{{ $agence->nom_commercial }}</p>
    </div>
    @if(auth()->user()->est_principal)
        <a href="{{ route('admin.administrateurs.create') }}" class="btn-primary text-sm">
            <i class="fas fa-plus"></i> Nouvel administrateur
        </a>
    @endif
</div>

{{-- Changer mot de passe --}}
<div class="card p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">Changer mon mot de passe</h3>
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

<div class="card overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Administrateur</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Contact</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Rôle</th>
                @if(auth()->user()->est_principal)
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($admins as $admin)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-cyan-400 flex items-center justify-center text-white text-sm font-semibold">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $admin->prenom }} {{ $admin->name }}</p>
                                @if($admin->est_principal)
                                    <span class="text-xs text-cyan-500 font-medium">Admin Principal</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600">{{ $admin->email }}</p>
                        <p class="text-xs text-gray-400">{{ $admin->telephone }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="badge-{{ $admin->est_principal ? 'disponible' : 'reserve' }}">
                            {{ $admin->est_principal ? 'Principal' : 'Associé' }}
                        </span>
                    </td>
                    @if(auth()->user()->est_principal)
                        <td class="px-6 py-4 text-right">
                            @if(!$admin->est_principal)
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.administrateurs.edit', $admin) }}" class="p-2 text-gray-400 hover:text-cyan-500 transition">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.administrateurs.destroy', $admin) }}" onsubmit="return confirm('Supprimer cet administrateur ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
