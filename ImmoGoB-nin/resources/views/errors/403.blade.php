@extends('layouts.app')

@section('title', 'Accès non autorisé')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-shield-alt text-amber-500 text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-3">Action non disponible</h1>
        <p class="text-gray-500 mb-2">
            En tant qu'administrateur, vous ne pouvez pas effectuer de réservation ou de paiement.
        </p>
        <p class="text-gray-400 text-sm mb-8">
            Cette fonctionnalité est réservée aux clients de la plateforme.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ url()->previous() }}" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
            <a href="{{ route('home') }}" class="btn-primary">
                <i class="fas fa-home mr-2"></i> Accueil
            </a>
        </div>
    </div>
</div>
@endsection
