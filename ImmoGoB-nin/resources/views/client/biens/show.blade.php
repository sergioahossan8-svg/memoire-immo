@extends('layouts.app')

@section('title', $bien->titre . ' - ImmoGo')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Colonne principale --}}
        <div class="lg:col-span-2">

            {{-- Galerie --}}
            @php $photos = $bien->photos; @endphp

            @if($photos->count() > 0)
                {{-- Photo principale avec zoom --}}
                <div class="relative rounded-2xl overflow-hidden bg-gray-100 mb-2" style="height: 480px;">
                    <img src="{{ Storage::url($photos->first()->chemin) }}"
                        alt="{{ $bien->titre }}"
                        id="mainPhoto"
                        class="w-full h-full object-contain transition-transform duration-300"
                        style="cursor: zoom-in; background: #f3f4f6;">

                    {{-- Badges --}}
                    <div class="absolute top-4 left-4 flex gap-2 pointer-events-none">
                        <span class="bg-white/90 backdrop-blur-sm text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                            {{ $bien->transaction === 'location' ? 'À Louer' : 'À Vendre' }}
                        </span>
                        @if($bien->is_premium)
                            <span class="bg-orange-400 text-white text-xs font-semibold px-3 py-1.5 rounded-full">Premium</span>
                        @endif
                    </div>

                    {{-- Statut --}}
                    @if($bien->statut !== 'disponible')
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center pointer-events-none">
                            <span class="bg-white text-gray-800 font-bold text-sm px-5 py-2 rounded-full uppercase tracking-wider">
                                {{ ucfirst($bien->statut) }}
                            </span>
                        </div>
                    @endif

                    {{-- Compteur --}}
                    @if($photos->count() > 1)
                        <div class="absolute bottom-4 right-4 bg-black/50 text-white text-xs px-3 py-1.5 rounded-full backdrop-blur-sm pointer-events-none">
                            <i class="fas fa-images mr-1"></i>
                            <span id="photoIndex">1</span> / {{ $photos->count() }}
                        </div>
                    @endif

                    {{-- Flèches DANS la zone image --}}
                    @if($photos->count() > 1)
                        <button onclick="prevPhoto(event)"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition z-10">
                            <i class="fas fa-chevron-left text-gray-700"></i>
                        </button>
                        <button onclick="nextPhoto(event)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition z-10">
                            <i class="fas fa-chevron-right text-gray-700"></i>
                        </button>
                    @endif
                </div>

                {{-- Miniatures --}}
                @if($photos->count() > 1)
                    <div class="flex gap-2 overflow-x-auto pb-1">
                        @foreach($photos as $i => $photo)
                            <button onclick="setPhoto({{ $i }})"
                                id="thumb-{{ $i }}"
                                class="flex-shrink-0 w-20 h-16 rounded-xl overflow-hidden border-2 transition {{ $i === 0 ? 'border-cyan-400' : 'border-transparent hover:border-gray-300' }}">
                                <img src="{{ Storage::url($photo->chemin) }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif

            @else
                <div class="rounded-2xl bg-gray-100 flex items-center justify-center mb-2" style="height: 360px;">
                    <i class="fas fa-home text-6xl text-gray-300"></i>
                </div>
            @endif

            {{-- Titre & Prix --}}
            <div class="mt-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $bien->titre }}</h1>
                        <p class="text-gray-500 flex items-center gap-1 mt-1 text-sm">
                            <i class="fas fa-map-marker-alt text-cyan-400"></i>
                            {{ $bien->localisation }}, {{ $bien->ville }}
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-2xl font-bold text-gray-800">{{ $bien->prix_formate }}</p>
                        @if($bien->transaction === 'location')
                            <p class="text-xs text-gray-400">/ mois</p>
                        @endif
                    </div>
                </div>

                {{-- Caractéristiques --}}
                <div class="flex flex-wrap items-center gap-4 mt-4 p-4 bg-gray-50 rounded-xl">
                    @if($bien->chambres)
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <i class="fas fa-bed text-cyan-400"></i> {{ $bien->chambres }} ch.
                        </div>
                    @endif
                    @if($bien->salles_bain)
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <i class="fas fa-bath text-cyan-400"></i> {{ $bien->salles_bain }} sdb
                        </div>
                    @endif
                    @if($bien->superficie)
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <i class="fas fa-ruler-combined text-cyan-400"></i> {{ $bien->superficie }} m²
                        </div>
                    @endif
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-tag text-cyan-400"></i> {{ $bien->typeBien->libelle ?? 'N/A' }}
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-exchange-alt text-cyan-400"></i> {{ ucfirst($bien->transaction) }}
                    </div>
                </div>

                {{-- Description --}}
                @if($bien->description)
                    <div class="mt-6">
                        <h3 class="font-semibold text-gray-800 mb-2">Description</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $bien->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Agence --}}
            @if($bien->agence)
                <div class="card p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($bien->agence->logo)
                                <img src="{{ Storage::url($bien->agence->logo) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-building text-gray-400 text-xl"></i>
                            @endif
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ $bien->agence->nom_commercial }}</p>
                            <p class="text-xs text-gray-400">{{ $bien->agence->ville }}</p>
                        </div>
                    </div>
                    @if($bien->agence->telephone)
                        <a href="tel:{{ $bien->agence->telephone }}"
                            class="w-full border border-cyan-400 text-cyan-500 font-medium py-2.5 rounded-xl text-sm flex items-center justify-center gap-2 hover:bg-cyan-50 transition">
                            <i class="fas fa-phone"></i> {{ $bien->agence->telephone }}
                        </a>
                    @endif
                </div>
            @endif

            {{-- Actions paiement --}}
            @if($bien->statut === 'disponible')
                <div class="card p-5 space-y-3">
                    <h3 class="font-semibold text-gray-800">Acquérir ce bien</h3>

                    <div class="bg-cyan-50 rounded-xl p-3 text-xs text-cyan-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Acompte de réservation (10%) :
                        <strong>{{ number_format($bien->prix * 0.10, 0, ',', ' ') }} FCFA</strong>
                    </div>

                    @auth
                        <a href="{{ route('client.reserver', $bien) }}"
                            class="w-full bg-cyan-400 hover:bg-cyan-500 text-white font-semibold py-3 rounded-xl text-sm flex items-center justify-center gap-2 transition">
                            <i class="fas fa-calendar-check"></i> Réserver (acompte 10%)
                        </a>
                        <a href="{{ route('client.payer.complet', $bien) }}"
                            class="w-full bg-gray-800 hover:bg-gray-900 text-white font-semibold py-3 rounded-xl text-sm flex items-center justify-center gap-2 transition">
                            <i class="fas fa-credit-card"></i> Paiement complet
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="w-full bg-cyan-400 hover:bg-cyan-500 text-white font-semibold py-3 rounded-xl text-sm flex items-center justify-center gap-2 transition">
                            <i class="fas fa-sign-in-alt"></i> Connectez-vous pour réserver
                        </a>
                    @endauth
                </div>
            @else
                <div class="card p-5 text-center py-8">
                    <i class="fas fa-times-circle text-3xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500 text-sm">Ce bien n'est plus disponible.</p>
                </div>
            @endif

            {{-- Accompagnement --}}
            <div class="card p-4 flex items-start gap-3">
                <div class="w-9 h-9 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-headset text-cyan-500 text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">Besoin d'un accompagnement ?</p>
                    <p class="text-xs text-gray-400 mt-0.5">Nos experts sont disponibles pour organiser vos visites.</p>
                </div>
                @php
                    $adminWa = $bien->agence?->adminPrincipal?->whatsapp;
                    $waClean = $adminWa ? preg_replace('/[^0-9]/', '', $adminWa) : null;
                    $waBien  = urlencode('Bonjour, je suis intéressé par le bien : ' . $bien->titre . ' (' . $bien->prix_formate . ')');
                @endphp
                @if($waClean)
                    <a href="https://wa.me/{{ $waClean }}?text={{ $waBien }}"
                        target="_blank"
                        class="text-xs bg-green-50 hover:bg-green-100 text-green-600 font-medium px-3 py-2 rounded-lg whitespace-nowrap transition flex-shrink-0 flex items-center gap-1">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                @else
                    <a href="#" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-3 py-2 rounded-lg whitespace-nowrap transition flex-shrink-0">
                        Contacter
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Biens similaires --}}
    @if($biensSimiliaires->count() > 0)
        <div class="mt-12">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Biens similaires</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($biensSimiliaires as $b)
                    @include('components.bien-card', ['bien' => $b])
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Tableau des URLs complètes des photos
    const photos = @json($photos->map(fn($p) => Storage::url($p->chemin))->values());
    let currentIndex = 0;
    let isZoomed = false;

    const mainPhoto = document.getElementById('mainPhoto');

    function setPhoto(index) {
        // Réinitialiser le zoom avant de changer
        if (isZoomed) toggleZoom();

        currentIndex = index;
        mainPhoto.src = photos[index];
        document.getElementById('photoIndex').textContent = index + 1;

        // Mettre à jour les miniatures
        document.querySelectorAll('[id^="thumb-"]').forEach((el, i) => {
            el.classList.remove('border-cyan-400');
            el.classList.add('border-transparent');
            if (i === index) {
                el.classList.add('border-cyan-400');
                el.classList.remove('border-transparent');
            }
        });
    }

    function nextPhoto(e) {
        if (e) e.stopPropagation();
        setPhoto((currentIndex + 1) % photos.length);
    }

    function prevPhoto(e) {
        if (e) e.stopPropagation();
        setPhoto((currentIndex - 1 + photos.length) % photos.length);
    }

    // Zoom au clic sur la photo
    function toggleZoom() {
        isZoomed = !isZoomed;
        if (isZoomed) {
            mainPhoto.style.transform = 'scale(2)';
            mainPhoto.style.cursor = 'zoom-out';
            mainPhoto.style.objectFit = 'contain';
        } else {
            mainPhoto.style.transform = 'scale(1)';
            mainPhoto.style.cursor = 'zoom-in';
        }
    }

    mainPhoto?.addEventListener('click', function(e) {
        // Ne pas zoomer si on clique sur les flèches
        toggleZoom();
    });

    // Curseur zoom-in par défaut
    mainPhoto?.addEventListener('mouseenter', function() {
        if (!isZoomed) this.style.cursor = 'zoom-in';
    });

    // Navigation clavier
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowRight') nextPhoto();
        if (e.key === 'ArrowLeft') prevPhoto();
        if (e.key === 'Escape' && isZoomed) toggleZoom();
    });
</script>
@endpush
@endsection
