@extends('layouts.admin')

@section('title', 'Modifier - ' . $bien->titre)

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.biens.index') }}" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Modifier le bien</h1>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.biens.update', $bien) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Nom du bien</label>
                <input type="text" name="titre" value="{{ old('titre', $bien->titre) }}" class="form-input" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type de bien</label>
                    <select name="type_bien_id" class="form-input" required>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ $bien->type_bien_id == $type->id ? 'selected' : '' }}>{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Type de contrat</label>
                    <select name="transaction" class="form-input" required>
                        <option value="location" {{ $bien->transaction === 'location' ? 'selected' : '' }}>À Louer</option>
                        <option value="vente" {{ $bien->transaction === 'vente' ? 'selected' : '' }}>À Vendre</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville', $bien->ville) }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Prix (FCFA)</label>
                    <input type="number" name="prix" value="{{ old('prix', $bien->prix) }}" class="form-input" required>
                </div>
            </div>

            <div>
                <label class="form-label">Adresse Complète</label>
                <input type="text" name="localisation" value="{{ old('localisation', $bien->localisation) }}" class="form-input" required>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Chambres</label>
                    <input type="number" name="chambres" value="{{ old('chambres', $bien->chambres) }}" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Salles de bain</label>
                    <input type="number" name="salles_bain" value="{{ old('salles_bain', $bien->salles_bain) }}" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Superficie (m²)</label>
                    <input type="number" name="superficie" value="{{ old('superficie', $bien->superficie) }}" class="form-input" min="0" step="0.1">
                </div>
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-input resize-none">{{ old('description', $bien->description) }}</textarea>
            </div>

            {{-- Photos existantes --}}
            @if($bien->photos->count() > 0)
                <div>
                    <label class="form-label">Photos actuelles</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach($bien->photos as $photo)
                            <img src="{{ str_starts_with($photo->chemin, 'http') ? $photo->chemin : asset('storage/' . $photo->chemin) }}" class="w-20 h-16 object-cover rounded-xl">
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="form-label">Ajouter de nouvelles photos</label>
                <input type="file" name="photos[]" multiple accept="image/*" class="form-input">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <a href="{{ route('admin.biens.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
