@extends('layouts.admin')

@section('title', 'Mon Profil - SuperAdmin')

@section('content')
<div class="max-w-xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Mon Profil</h1>

    <div class="card p-6">
        <form method="POST" action="{{ route('superadmin.profil.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" class="form-input" required>
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

            <div>
                <label class="form-label">Numéro WhatsApp Support</label>
                <div class="relative">
                    <i class="fab fa-whatsapp absolute left-3 top-1/2 -translate-y-1/2 text-green-500 text-base"></i>
                    <input type="tel" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}"
                        placeholder="+229 01 00 00 00"
                        class="form-input pl-10">
                </div>
                <p class="text-xs text-gray-400 mt-1">Ce numéro sera utilisé par les admins d'agence pour vous contacter via WhatsApp.</p>
            </div>

            <hr class="border-gray-100">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-input" placeholder="Laisser vide pour ne pas changer">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Confirmation</label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-save"></i> Enregistrer
            </button>
        </form>
    </div>
</div>
@endsection
