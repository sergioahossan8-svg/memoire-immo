@extends('layouts.admin')

@section('title', $agence->nom_commercial)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('superadmin.agences.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $agence->nom_commercial }}</h1>
        <p class="text-gray-500 text-sm">{{ $agence->ville }} · {{ $agence->secteur }}</p>
    </div>
    <div class="ml-auto flex gap-3">
        <a href="{{ route('superadmin.agences.edit', $agence) }}" class="btn-secondary text-sm">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <form method="POST" action="{{ route('superadmin.agences.statut', $agence) }}">
            @csrf @method('PATCH')
            <select name="statut" onchange="this.form.submit()" class="form-input text-sm py-2">
                <option value="actif" {{ $agence->statut === 'actif' ? 'selected' : '' }}>Actif</option>
                <option value="en_attente" {{ $agence->statut === 'en_attente' ? 'selected' : '' }}>En attente</option>
                <option value="suspendu" {{ $agence->statut === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
            </select>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Informations</h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-400">Email</span>
                <span class="text-gray-700">{{ $agence->email }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Téléphone</span>
                <span class="text-gray-700">{{ $agence->telephone ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Adresse</span>
                <span class="text-gray-700 text-right">{{ $agence->adresse_complete }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Biens</span>
                <span class="text-gray-700">{{ $agence->biens->count() }}</span>
            </div>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Administrateurs ({{ $agence->administrateurs->count() }})</h3>
        <div class="space-y-3">
            @foreach($agence->administrateurs as $admin)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-cyan-400 flex items-center justify-center text-white text-xs font-semibold">
                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $admin->prenom }} {{ $admin->name }}</p>
                        <p class="text-xs text-gray-400">{{ $admin->est_principal ? 'Principal' : 'Associé' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Biens récents</h3>
        <div class="space-y-3">
            @foreach($agence->biens->take(5) as $bien)
                <div class="flex items-center gap-3">
                    @php $photo = $bien->photos->first(); @endphp
                    <div class="w-10 h-8 rounded-lg overflow-hidden flex-shrink-0">
                        @if($photo)
                            <img src="{{ str_starts_with($photo->chemin, 'http') ? $photo->chemin : asset('storage/' . $photo->chemin) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-home text-gray-300 text-xs"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-800 truncate">{{ $bien->titre }}</p>
                        <p class="text-xs text-gray-400">{{ $bien->prix_formate }}</p>
                    </div>
                    <span class="badge-{{ $bien->statut }} flex-shrink-0 text-xs">{{ ucfirst($bien->statut) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
