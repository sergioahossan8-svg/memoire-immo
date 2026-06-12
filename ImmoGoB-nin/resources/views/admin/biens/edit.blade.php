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
                    <select name="transaction" id="transactionSelect" class="form-input" required onchange="toggleConditions()">
                        <option value="location" {{ $bien->transaction === 'location' ? 'selected' : '' }}>À Louer</option>
                        <option value="vente" {{ $bien->transaction === 'vente' ? 'selected' : '' }}>À Vendre</option>
                    </select>
                </div>
            </div>

            {{-- SECTION CONDITIONS DE LOCATION (visible si transaction = location) --}}
            <div id="conditionsLocation" class="border border-cyan-200 bg-cyan-50 rounded-xl p-4 space-y-4" style="{{ $bien->transaction !== 'location' ? 'display:none' : '' }}">
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 bg-cyan-100 rounded flex items-center justify-center">
                        <i class="fas fa-file-contract text-cyan-500 text-xs"></i>
                    </div>
                    <p class="text-xs font-bold text-cyan-600 uppercase tracking-wider">Conditions de location</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">
                            Avance à payer <span class="text-red-400">*</span>
                            <span class="text-gray-400 font-normal">(nombre de mois)</span>
                        </label>
                        <select name="avance_mois" class="form-input @error('avance_mois') border-red-400 @enderror">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ old('avance_mois', $bien->avance_mois ?? 1) == $i ? 'selected' : '' }}>
                                    {{ $i }} mois
                                </option>
                            @endfor
                        </select>
                        @error('avance_mois')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">
                            Prix du loyer <span class="text-gray-400 font-normal">(FCFA / mois)</span>
                        </label>
                        <div class="form-input bg-gray-100 text-gray-500 text-sm flex items-center gap-2 cursor-not-allowed">
                            <i class="fas fa-info-circle text-cyan-400 text-xs"></i>
                            Défini dans "Prix" ci-dessous
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">
                            Caution eau
                            <span class="text-gray-400 font-normal text-xs">(optionnel)</span>
                        </label>
                        <input type="number" name="caution_eau" value="{{ old('caution_eau', $bien->caution_eau) }}"
                            placeholder="ex: 5000"
                            class="form-input @error('caution_eau') border-red-400 @enderror" min="0" step="100">
                        @error('caution_eau')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">
                            Caution électricité
                            <span class="text-gray-400 font-normal text-xs">(optionnel)</span>
                        </label>
                        <input type="number" name="caution_electricite" value="{{ old('caution_electricite', $bien->caution_electricite) }}"
                            placeholder="ex: 10000"
                            class="form-input @error('caution_electricite') border-red-400 @enderror" min="0" step="100">
                        @error('caution_electricite')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Récap montant total --}}
                <div class="bg-white rounded-xl p-3 border border-cyan-200">
                    <p class="text-xs font-semibold text-gray-600 mb-1">
                        <i class="fas fa-calculator text-cyan-400 mr-1"></i>
                        Montant total à payer par le client :
                    </p>
                    <p class="text-sm font-bold text-cyan-700" id="recapMontant">
                        (Prix × avance) + cautions
                    </p>
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

@push('scripts')
<script>
    function toggleConditions() {
        const select = document.getElementById('transactionSelect');
        const section = document.getElementById('conditionsLocation');
        const avanceSelect = document.querySelector('[name="avance_mois"]');

        if (select.value === 'location') {
            section.style.display = '';
            avanceSelect.required = true;
        } else {
            section.style.display = 'none';
            avanceSelect.required = false;
        }
        updateRecap();
    }

    function updateRecap() {
        const prix = parseFloat(document.querySelector('[name="prix"]')?.value || 0);
        const avance = parseInt(document.querySelector('[name="avance_mois"]')?.value || 1);
        const eau = parseFloat(document.querySelector('[name="caution_eau"]')?.value || 0);
        const elec = parseFloat(document.querySelector('[name="caution_electricite"]')?.value || 0);
        const total = (prix * avance) + eau + elec;
        const recap = document.getElementById('recapMontant');
        if (recap && prix > 0) {
            recap.textContent = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA'
                + ' = (' + new Intl.NumberFormat('fr-FR').format(prix) + ' × ' + avance + ' mois)'
                + (eau > 0 ? ' + ' + new Intl.NumberFormat('fr-FR').format(eau) + ' eau' : '')
                + (elec > 0 ? ' + ' + new Intl.NumberFormat('fr-FR').format(elec) + ' élec.' : '');
        } else if (recap) {
            recap.textContent = '(Prix × avance) + cautions';
        }
    }

    // Mise à jour dynamique du récap
    document.addEventListener('DOMContentLoaded', function () {
        toggleConditions();
        document.querySelector('[name="prix"]')?.addEventListener('input', updateRecap);
        document.querySelector('[name="avance_mois"]')?.addEventListener('change', updateRecap);
        document.querySelector('[name="caution_eau"]')?.addEventListener('input', updateRecap);
        document.querySelector('[name="caution_electricite"]')?.addEventListener('input', updateRecap);
    });
</script>
@endpush
@endsection
