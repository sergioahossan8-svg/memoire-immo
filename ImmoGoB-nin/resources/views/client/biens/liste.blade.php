@extends('layouts.app')

@section('title', 'Tous les biens - ImmoGo')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- Filtres --}}
    <form method="GET" action="{{ route('biens.liste') }}" class="card p-4 mb-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <select name="transaction" class="form-input">
                <option value="">Type de transaction</option>
                <option value="location" {{ request('transaction') === 'location' ? 'selected' : '' }}>À Louer</option>
                <option value="vente" {{ request('transaction') === 'vente' ? 'selected' : '' }}>À Vendre</option>
            </select>
            <select name="type_bien_id" class="form-input">
                <option value="">Type de bien</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ request('type_bien_id') == $type->id ? 'selected' : '' }}>{{ $type->libelle }}</option>
                @endforeach
            </select>
            <input type="text" name="ville" value="{{ request('ville') }}" placeholder="Ville" class="form-input">
            <input type="number" name="prix_min" value="{{ request('prix_min') }}" placeholder="Prix min (FCFA)" class="form-input">
            <input type="number" name="prix_max" value="{{ request('prix_max') }}" placeholder="Prix max (FCFA)" class="form-input">
        </div>
        <div class="flex gap-3 mt-3">
            <button type="submit" class="btn-primary px-6 py-2.5 text-sm">
                <i class="fas fa-search"></i> Filtrer
            </button>
            <a href="{{ route('biens.liste') }}" class="btn-secondary text-sm">Réinitialiser</a>
        </div>
    </form>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-800">
            {{ $biens->total() }} bien(s) trouvé(s)
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($biens as $bien)
            @include('components.bien-card', ['bien' => $bien])
        @empty
            <div class="col-span-4 text-center py-16">
                <i class="fas fa-home text-5xl text-gray-200 mb-4"></i>
                <p class="text-gray-400 text-lg">Aucun bien ne correspond à votre recherche.</p>
                <a href="{{ route('biens.liste') }}" class="text-cyan-500 text-sm mt-2 inline-block hover:underline">Voir tous les biens</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $biens->links() }}
    </div>
</div>
@endsection
