@extends('layouts.app')

@section('title', 'Mon Profil - ImmoGo')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Mon Profil</h1>

    <div class="card p-8">
        <form method="POST" action="{{ route('client.profil.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')

            {{-- Avatar --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 rounded-full bg-cyan-400 flex items-center justify-center text-white text-2xl font-bold overflow-hidden">
                    @if($user->client?->avatar)
                        <img src="{{ asset('storage/' . $user->client->avatar) }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 cursor-pointer hover:text-cyan-500">
                        <i class="fas fa-camera mr-1"></i> Changer la photo
                        <input type="file" name="avatar" class="hidden" accept="image/*">
                    </label>
                    <p class="text-xs text-gray-400">JPG, PNG max 2MB</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input @error('name') border-red-400 @enderror" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" class="form-input @error('prenom') border-red-400 @enderror" required>
                    @error('prenom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Email</label>
                <input type="email" value="{{ $user->email }}" class="form-input bg-gray-50" disabled>
            </div>

            <div>
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" value="{{ old('telephone', $user->telephone) }}" class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville', $user->ville) }}"
                        placeholder="ex: Cotonou" class="form-input">
                </div>
                <div>
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $user->adresse) }}"
                        placeholder="ex: Akpakpa, Rue 123" class="form-input">
                </div>
            </div>

            <hr class="border-gray-100">
            <p class="text-sm font-semibold text-gray-700">Changer le mot de passe (optionnel)</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-input @error('password') border-red-400 @enderror" placeholder="Laisser vide pour ne pas changer">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Confirmer le mot de passe">
                </div>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </form>
    </div>
</div>
@endsection
