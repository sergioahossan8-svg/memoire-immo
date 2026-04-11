@extends('layouts.app')

@section('title', 'Mes Favoris - ImmoGo')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-8">
        <div>
            <p class="text-xs font-semibold text-cyan-500 uppercase tracking-wider flex items-center gap-1 mb-1">
                <i class="fas fa-heart"></i> Collections
            </p>
            <h1 class="text-3xl font-bold text-gray-800">Mes Favoris</h1>
            <p class="text-gray-500 mt-1">Retrouvez ici toutes les propriétés que vous avez sélectionnées pour vos futurs projets immobiliers.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-center">
                <p class="text-2xl font-bold text-cyan-500">{{ $favoris->count() }}</p>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Annonces</p>
            </div>
            <a href="#" class="flex items-center gap-2 border border-gray-200 text-gray-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-gray-50 transition">
                <i class="fas fa-bell text-xs"></i> Gérer mes alertes
            </a>
        </div>
    </div>

    @if($favoris->isEmpty())
        <div class="text-center py-20">
            <i class="fas fa-heart text-6xl text-gray-200 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-400 mb-2">Aucun favori pour le moment</h3>
            <p class="text-gray-400 text-sm mb-6">Explorez nos annonces et ajoutez vos biens préférés ici.</p>
            <a href="{{ route('biens.liste') }}" class="btn-primary inline-flex">
                <i class="fas fa-search"></i> Explorer les biens
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($favoris as $favori)
                @include('components.bien-card', ['bien' => $favori->bien])
            @endforeach
        </div>
    @endif

    {{-- Besoin d'accompagnement --}}
    @if($favoris->isNotEmpty())
        <div class="mt-10 card p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-headset text-cyan-500 text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-800">Besoin d'un accompagnement ?</p>
                <p class="text-sm text-gray-500">Nos experts ImmoGo sont disponibles pour organiser vos visites groupées.</p>
            </div>
            <a href="#" class="bg-gray-800 hover:bg-gray-900 text-white font-medium px-5 py-2.5 rounded-xl text-sm transition">
                Contacter un conseiller
            </a>
        </div>
    @endif
</div>
@endsection
