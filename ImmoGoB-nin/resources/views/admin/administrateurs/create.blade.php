@extends('layouts.admin')

@section('title', 'Nouvel Administrateur')

@section('content')
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.administrateurs.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">Nouvel Administrateur</h1>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.administrateurs.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-400 @enderror" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" class="form-input @error('prenom') border-red-400 @enderror" required>
                    @error('prenom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Email Professionnel</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') border-red-400 @enderror" required>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" value="{{ old('telephone') }}" class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input @error('password') border-red-400 @enderror" required>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-input" required>
                </div>
            </div>

            <div class="bg-cyan-50 rounded-xl p-3 text-xs text-cyan-700">
                <i class="fas fa-info-circle mr-1"></i>
                L'administrateur recevra un email d'invitation automatique pour définir son mot de passe et activer le compte de l'agence.
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-user-plus"></i> Créer l'administrateur
            </button>
        </form>
    </div>
</div>
@endsection
