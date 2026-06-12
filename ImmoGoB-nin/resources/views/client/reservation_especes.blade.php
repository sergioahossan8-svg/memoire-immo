@extends('layouts.app')

@section('title', 'Réservation confirmée - Paiement en espèces')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="card p-8">

        {{-- En-tête succès --}}
        <div class="flex flex-col items-center text-center mb-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-check text-green-500 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Réservation enregistrée !</h1>
            <p class="text-gray-500 text-sm">
                Votre réservation pour <strong>{{ $bien->titre }}</strong> est enregistrée.<br>
                Il vous reste à régler l'acompte directement au siège de l'agence.
            </p>
        </div>

        {{-- Montant à payer --}}
        <div class="bg-cyan-50 border border-cyan-200 rounded-xl p-4 mb-6 text-center">
            <p class="text-xs text-cyan-600 font-semibold uppercase tracking-wider mb-1">Acompte à régler (10%)</p>
            <p class="text-3xl font-bold text-cyan-700">{{ number_format($montantAcompte, 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-gray-400 mt-1">À payer en espèces au siège de l'agence</p>
        </div>

        {{-- Infos agence --}}
        @php $agence = $bien->agence; @endphp
        @if($agence)
            <div class="border border-gray-200 rounded-xl p-5 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($agence->logo)
                            <img src="{{ str_starts_with($agence->logo, 'http') ? $agence->logo : asset('storage/' . $agence->logo) }}"
                                class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-building text-gray-400 text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <p class="font-bold text-gray-800">{{ $agence->nom_commercial }}</p>
                        <p class="text-xs text-gray-400">Agence immobilière</p>
                    </div>
                </div>

                <div class="space-y-3">
                    {{-- Téléphone --}}
                    @if($agence->telephone)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone text-cyan-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Téléphone</p>
                                <a href="tel:{{ $agence->telephone }}"
                                    class="text-sm font-semibold text-cyan-600 hover:text-cyan-700">
                                    {{ $agence->telephone }}
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Adresse / Siège --}}
                    @if($agence->adresse_complete)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-cyan-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Siège de l'agence</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $agence->adresse_complete }}</p>
                                @if($agence->ville)
                                    <p class="text-xs text-gray-500">{{ $agence->ville }}</p>
                                @endif
                            </div>
                        </div>
                    @elseif($agence->ville)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-cyan-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Ville</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $agence->ville }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Email --}}
                    @if($agence->email)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-cyan-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Email</p>
                                <a href="mailto:{{ $agence->email }}"
                                    class="text-sm font-semibold text-cyan-600 hover:text-cyan-700">
                                    {{ $agence->email }}
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- WhatsApp admin principal --}}
                    @php
                        $adminWa = $agence->adminPrincipal?->whatsapp;
                        $waClean = $adminWa ? preg_replace('/[^0-9]/', '', $adminWa) : null;
                        $waMsg   = urlencode('Bonjour, je souhaite régler mon acompte de réservation pour le bien : ' . $bien->titre . '. Réf contrat #' . $contrat->id);
                    @endphp
                    @if($waClean)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fab fa-whatsapp text-green-500 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">WhatsApp agence</p>
                                <a href="https://wa.me/{{ $waClean }}?text={{ $waMsg }}"
                                    target="_blank"
                                    class="text-sm font-semibold text-green-600 hover:text-green-700">
                                    {{ $adminWa }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Message d'instruction --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                <div class="text-sm text-amber-800">
                    <p class="font-semibold mb-1">Instructions</p>
                    <ul class="space-y-1 text-xs leading-relaxed list-disc list-inside">
                        <li>Contactez l'agence <strong>{{ $agence?->nom_commercial ?? '' }}</strong> par téléphone ou WhatsApp.</li>
                        <li>Mentionnez le bien <strong>« {{ $bien->titre }} »</strong> et le numéro de contrat <strong>#{{ $contrat->id }}</strong>.</li>
                        <li>Rendez-vous au siège de l'agence pour régler l'acompte de <strong>{{ number_format($montantAcompte, 0, ',', ' ') }} FCFA</strong> en espèces.</li>
                        <li>Vous avez <strong>15 jours</strong> pour effectuer ce paiement. Passé ce délai, la réservation sera annulée.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Référence contrat --}}
        <div class="bg-gray-50 rounded-xl p-3 mb-6 flex items-center justify-between text-sm">
            <span class="text-gray-500">Référence de réservation</span>
            <span class="font-bold text-gray-800">#{{ $contrat->id }}</span>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <a href="{{ route('client.historique') }}"
                class="btn-primary flex-1 text-center">
                <i class="fas fa-list mr-1"></i> Voir mes réservations
            </a>
            <a href="{{ route('home') }}" class="btn-secondary">
                Accueil
            </a>
        </div>
    </div>
</div>
@endsection
