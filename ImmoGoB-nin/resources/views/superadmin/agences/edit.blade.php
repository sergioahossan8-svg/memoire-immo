@extends('layouts.admin')

@section('title', 'Modifier - ' . $agence->nom_commercial)

@section('content')
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('superadmin.agences.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-bold text-gray-800">Modifier l'agence</h1>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('superadmin.agences.update', $agence) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Nom Commercial</label>
                <input type="text" name="nom_commercial" value="{{ old('nom_commercial', $agence->nom_commercial) }}" class="form-input" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Secteur</label>
                    <select name="secteur" class="form-input" required>
                        @foreach(['Résidentiel', 'Commercial', 'Industriel', 'Mixte'] as $s)
                            <option value="{{ $s }}" {{ $agence->secteur === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville', $agence->ville) }}" class="form-input" required>
                </div>
            </div>

            <div>
                <label class="form-label">Adresse Complète</label>
                <input type="text" name="adresse_complete" value="{{ old('adresse_complete', $agence->adresse_complete) }}" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone" value="{{ old('telephone', $agence->telephone) }}" class="form-input">
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </form>
    </div>
</div>
@endsection
