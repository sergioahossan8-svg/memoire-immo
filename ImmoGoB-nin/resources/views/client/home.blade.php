@extends('layouts.app')

@section('title', 'ImmoGo - Trouvez votre chez-vous')

@section('content')
{{-- Hero --}}
<section class="bg-gradient-to-r from-cyan-50 to-blue-50 py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">
            Trouvez votre <span class="text-cyan-400 italic">prochain chez-vous</span> au Bénin.
        </h1>
        <p class="text-gray-500 mb-2">Explorez des milliers d'annonces vérifiées par nos experts immobiliers pour une expérience de location sereine.</p>

        {{-- Nouvelle phrase --}}
        <div class="flex items-center gap-2 mb-6">
            <div class="flex items-center gap-1.5 bg-white/80 backdrop-blur-sm border border-cyan-100 rounded-full px-4 py-2 shadow-sm">
                <i class="fas fa-map-marker-alt text-cyan-400 text-sm"></i>
                <span class="text-sm text-gray-600 font-medium">Des centaines de biens immobiliers disponibles partout au Bénin,</span>
                <span class="text-sm text-cyan-500 font-semibold">trouvez celui qui vous correspond.</span>
            </div>
        </div>

        {{-- Localisation active --}}
        <div class="flex items-center gap-2 bg-white rounded-xl px-4 py-3 shadow-sm border border-gray-100 max-w-sm cursor-pointer"
            onclick="document.getElementById('modalLieu').classList.remove('hidden')">
            <i class="fas fa-map-marker-alt text-cyan-400"></i>
            <div>
                <p class="text-xs text-gray-400">Current Location</p>
                <p class="text-sm font-medium text-gray-700">{{ $ville }}, Bénin</p>
            </div>
            <span class="ml-auto text-xs text-cyan-500 font-medium hover:underline">Change &gt;</span>
        </div>
    </div>
</section>

{{-- Modal changer de lieu --}}
<div id="modalLieu" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4"
    onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">Changer de localisation</h3>
            <button onclick="document.getElementById('modalLieu').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-4">Choisissez une ville pour voir les biens disponibles près de vous.</p>
        <form method="POST" action="{{ route('changer.lieu') }}">
            @csrf
            <div class="relative mb-4">
                <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-cyan-400 text-sm"></i>
                <input type="text" name="ville" value="{{ $ville }}"
                    placeholder="ex: Cotonou, Parakou, Abomey..."
                    class="form-input pl-10" required autofocus>
            </div>
            {{-- Villes rapides --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach(['Cotonou', 'Porto-Novo', 'Parakou', 'Abomey', 'Bohicon', 'Calavi'] as $v)
                    <button type="button"
                        onclick="document.querySelector('#modalLieu input[name=ville]').value='{{ $v }}'"
                        class="text-xs bg-gray-100 hover:bg-cyan-50 hover:text-cyan-600 text-gray-600 px-3 py-1.5 rounded-full transition">
                        {{ $v }}
                    </button>
                @endforeach
            </div>
            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-check"></i> Confirmer
            </button>
        </form>
    </div>
</div>

{{-- À louer en ce moment --}}
<section class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">À louer en ce moment</h2>
            <p class="text-sm text-gray-500">Les dernières pépites immobilières près de <strong>{{ $ville }}</strong>.</p>
        </div>
        <a href="{{ route('biens.liste', ['transaction' => 'location']) }}" class="text-sm text-cyan-500 font-medium hover:underline flex items-center gap-1">
            Voir tout <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($biensRecents as $bien)
            @include('components.bien-card', ['bien' => $bien])
        @empty
            <div class="col-span-3 text-center py-10">
                <i class="fas fa-home text-4xl text-gray-200 mb-3"></i>
                <p class="text-gray-400">Aucun bien disponible pour le moment.</p>
            </div>
        @endforelse
    </div>
</section>

{{-- Nos recommandations --}}
<section class="max-w-7xl mx-auto px-4 pb-16">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Nos recommandations</h2>
            <p class="text-sm text-gray-500">Basé sur vos recherches récentes et vos quartiers favoris.</p>
        </div>
        <a href="{{ route('biens.liste') }}" class="text-sm text-cyan-500 font-medium hover:underline flex items-center gap-1">
            Voir tout <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($biensRecommandes as $bien)
            @include('components.bien-card', ['bien' => $bien])
        @empty
            <div class="col-span-3 text-center py-10">
                <i class="fas fa-star text-4xl text-gray-200 mb-3"></i>
                <p class="text-gray-400">Aucune recommandation disponible.</p>
            </div>
        @endforelse
    </div>
</section>

{{-- Features --}}
<section class="bg-white border-t border-gray-100 py-10">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-bolt text-cyan-500"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-1">Visites Instantanées</h4>
                <p class="text-sm text-gray-500">Réservez votre créneau de visite en direct avec l'agent immobilier depuis l'application.</p>
            </div>
        </div>
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-star text-cyan-500"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-1">Annonces Vérifiées</h4>
                <p class="text-sm text-gray-500">Chaque annonce est soumise à un contrôle strict de conformité et de qualité photo.</p>
            </div>
        </div>
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-tie text-cyan-500"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-1">Accompagnement Dédié</h4>
                <p class="text-sm text-gray-500">Une équipe d'experts à votre écoute pour constituer votre dossier locatif parfait.</p>
            </div>
        </div>
    </div>
</section>

{{-- FAB --}}
<a href="{{ route('biens.liste') }}" class="fixed bottom-20 md:bottom-6 right-6 w-12 h-12 bg-cyan-400 hover:bg-cyan-500 text-white rounded-full shadow-lg flex items-center justify-center transition z-40">
    <i class="fas fa-search"></i>
</a>
@endsection
