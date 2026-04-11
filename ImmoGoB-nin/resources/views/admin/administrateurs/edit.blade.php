@extends('layouts.admin')

@section('title', 'Modifier Administrateur')

@section('content')
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.administrateurs.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">Modifier l'administrateur</h1>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.administrateurs.update', $administrateur) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" value="{{ old('name', $administrateur->name) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom', $administrateur->prenom) }}" class="form-input" required>
                </div>
            </div>

            <div>
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" value="{{ old('telephone', $administrateur->telephone) }}" class="form-input">
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-save"></i> Enregistrer
            </button>
        </form>
    </div>
</div>
@endsection
