@php
    $photo = $bien->photoPrincipale ?? $bien->photos->first();
    $isFavori = auth()->check() && auth()->user()->favoris()->where('bien_id', $bien->id)->exists();
@endphp

<div class="card overflow-hidden hover:shadow-md transition-shadow group">
    {{-- Image --}}
    <div class="relative h-48 overflow-hidden">
        @if($photo)
            <img src="{{ Storage::url($photo->chemin) }}" alt="{{ $bien->titre }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                <i class="fas fa-home text-4xl text-gray-300"></i>
            </div>
        @endif

        {{-- Badges --}}
        <div class="absolute top-3 left-3 flex gap-2">
            <span class="bg-white/90 backdrop-blur-sm text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                {{ $bien->transaction === 'location' ? 'À Louer' : 'À Vendre' }}
            </span>
            @if($bien->is_premium)
                <span class="bg-orange-400 text-white text-xs font-semibold px-2.5 py-1 rounded-full">Premium</span>
            @endif
        </div>

        @if($bien->superficie)
            <span class="absolute top-3 right-10 bg-white/90 backdrop-blur-sm text-gray-700 text-xs font-medium px-2 py-1 rounded-full">
                {{ $bien->superficie }} m²
            </span>
        @endif

        {{-- Favori --}}
        <button onclick="toggleFavori({{ $bien->id }}, this)"
            class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm hover:scale-110 transition-transform favori-btn"
            data-bien="{{ $bien->id }}">
            <i class="fas fa-heart text-sm {{ $isFavori ? 'text-red-500' : 'text-gray-300' }}"></i>
        </button>

        {{-- Prix --}}
        <div class="absolute bottom-3 left-3">
            <span class="bg-black/60 backdrop-blur-sm text-white text-sm font-bold px-3 py-1 rounded-lg">
                {{ $bien->prix_formate }}
                @if($bien->transaction === 'location') / mois @endif
            </span>
        </div>

        {{-- Statut --}}
        @if($bien->statut !== 'disponible')
            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                <span class="bg-white text-gray-800 font-bold text-sm px-4 py-2 rounded-full uppercase">
                    {{ ucfirst($bien->statut) }}
                </span>
            </div>
        @endif
    </div>

    {{-- Infos --}}
    <div class="p-4">
        <div class="flex items-start justify-between mb-1">
            <h3 class="font-semibold text-gray-800 text-sm leading-tight">{{ $bien->titre }}</h3>
            @if($bien->agence)
                <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0 ml-2 overflow-hidden">
                    @if($bien->agence->logo)
                        <img src="{{ Storage::url($bien->agence->logo) }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-building text-gray-400 text-xs"></i>
                    @endif
                </div>
            @endif
        </div>

        <p class="text-xs text-gray-400 flex items-center gap-1 mb-3">
            <i class="fas fa-map-marker-alt text-cyan-400"></i>
            {{ $bien->localisation }}, {{ $bien->ville }}
        </p>

        <div class="flex items-center gap-4 text-xs text-gray-500 mb-4 border-t border-gray-50 pt-3">
            @if($bien->chambres)
                <span class="flex items-center gap-1"><i class="fas fa-bed text-gray-400"></i> {{ $bien->chambres }} ch.</span>
            @endif
            @if($bien->salles_bain)
                <span class="flex items-center gap-1"><i class="fas fa-bath text-gray-400"></i> {{ $bien->salles_bain }} Sdb.</span>
            @endif
            @if($bien->superficie)
                <span class="flex items-center gap-1"><i class="fas fa-ruler-combined text-gray-400"></i> {{ $bien->superficie }} m²</span>
            @endif
        </div>

        <a href="{{ route('biens.show', $bien) }}"
            class="w-full bg-cyan-400 hover:bg-cyan-500 text-white text-sm font-semibold py-2.5 rounded-xl flex items-center justify-center gap-2 transition">
            Visiter l'annonce <i class="fas fa-chevron-right text-xs"></i>
        </a>
    </div>
</div>

@once
@push('scripts')
<script>
function toggleFavori(bienId, btn) {
    fetch(`/favoris/${bienId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }
        const icon = btn.querySelector('i');
        icon.className = data.added ? 'fas fa-heart text-sm text-red-500' : 'fas fa-heart text-sm text-gray-300';
    });
}
</script>
@endpush
@endonce
