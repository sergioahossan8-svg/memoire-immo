@extends('layouts.admin')

@section('title', 'Ajouter un bien')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.biens.index') }}" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Ajouter un bien</h1>
            <p class="text-gray-500 text-sm">Complétez les informations pour créer vos biens</p>
        </div>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.biens.store') }}" enctype="multipart/form-data" class="space-y-5" id="bienForm">
            @csrf

            <div class="flex items-center gap-2 mb-2">
                <div class="w-6 h-6 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-home text-cyan-500 text-xs"></i>
                </div>
                <p class="text-xs font-bold text-cyan-600 uppercase tracking-wider">Informations du bien</p>
            </div>

            <div>
                <label class="form-label">Nom du bien</label>
                <input type="text" name="titre" value="{{ old('titre') }}"
                    placeholder="ex: Appartement Lumineux - Le Ma"
                    class="form-input @error('titre') border-red-400 @enderror" required>
                @error('titre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type de bien</label>
                    <select name="type_bien_id" class="form-input @error('type_bien_id') border-red-400 @enderror" required>
                        <option value="">Sélectionner...</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ old('type_bien_id') == $type->id ? 'selected' : '' }}>{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                    @error('type_bien_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Type de contrat</label>
                    <select name="transaction" class="form-input" required>
                        <option value="location" {{ old('transaction') === 'location' ? 'selected' : '' }}>À Louer</option>
                        <option value="vente" {{ old('transaction') === 'vente' ? 'selected' : '' }}>À Vendre</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" value="{{ old('ville') }}" placeholder="Cotonou, Calavi..."
                        class="form-input @error('ville') border-red-400 @enderror" required>
                    @error('ville')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Prix (FCFA)</label>
                    <input type="number" name="prix" value="{{ old('prix') }}" placeholder="25000"
                        class="form-input @error('prix') border-red-400 @enderror" required>
                    @error('prix')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Adresse Complète</label>
                <input type="text" name="localisation" value="{{ old('localisation') }}" placeholder="Cotonou, Akpakpa"
                    class="form-input @error('localisation') border-red-400 @enderror" required>
                @error('localisation')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Chambres</label>
                    <input type="number" name="chambres" value="{{ old('chambres') }}" placeholder="2" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Salles de bain</label>
                    <input type="number" name="salles_bain" value="{{ old('salles_bain') }}" placeholder="1" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Superficie (m²)</label>
                    <input type="number" name="superficie" value="{{ old('superficie') }}" placeholder="65" class="form-input" min="0" step="0.1">
                </div>
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" placeholder="Décrivez le bien..." class="form-input resize-none">{{ old('description') }}</textarea>
            </div>

            {{-- Upload photos --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="form-label mb-0">Photos du bien</label>
                    <span class="text-xs text-gray-400" id="photoCount">0 / 10 photos</span>
                </div>

                {{-- Zone de drop --}}
                <div id="dropZone"
                    class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-cyan-400 transition cursor-pointer"
                    onclick="document.getElementById('photosInput').click()"
                    ondragover="event.preventDefault(); this.classList.add('border-cyan-400','bg-cyan-50')"
                    ondragleave="this.classList.remove('border-cyan-400','bg-cyan-50')"
                    ondrop="handleDrop(event)">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm font-medium text-gray-500">Glissez vos photos ici ou <span class="text-cyan-500">cliquez pour parcourir</span></p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG — max 5MB par image — jusqu'à 10 photos</p>
                    <input type="file" id="photosInput" name="photos[]" multiple accept="image/*" class="hidden" onchange="handleFiles(this.files)">
                </div>

                @error('photos')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                @error('photos.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                {{-- Prévisualisation --}}
                <div id="photoPreview" class="grid grid-cols-5 gap-2 mt-3"></div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1" id="submitBtn">
                    <i class="fas fa-check"></i> Confirmer et Créer le bien
                </button>
                <a href="{{ route('admin.biens.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let selectedFiles = [];

    function handleFiles(files) {
        const remaining = 10 - selectedFiles.length;
        const toAdd = Array.from(files).slice(0, remaining);

        toAdd.forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`"${file.name}" dépasse 5MB et a été ignoré.`);
                return;
            }
            selectedFiles.push(file);
        });

        updatePreview();
        updateInput();
    }

    function handleDrop(e) {
        e.preventDefault();
        document.getElementById('dropZone').classList.remove('border-cyan-400', 'bg-cyan-50');
        handleFiles(e.dataTransfer.files);
    }

    function removePhoto(index) {
        selectedFiles.splice(index, 1);
        updatePreview();
        updateInput();
    }

    function updatePreview() {
        const preview = document.getElementById('photoPreview');
        const count = document.getElementById('photoCount');
        count.textContent = `${selectedFiles.length} / 10 photos`;
        count.className = selectedFiles.length >= 10 ? 'text-xs text-orange-500 font-medium' : 'text-xs text-gray-400';

        preview.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-20 object-cover rounded-xl">
                    ${index === 0 ? '<span class="absolute bottom-1 left-1 bg-cyan-400 text-white text-xs px-1.5 py-0.5 rounded-md">Principale</span>' : ''}
                    <button type="button" onclick="removePhoto(${index})"
                        class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });

        // Désactiver la zone si 10 photos
        const dropZone = document.getElementById('dropZone');
        if (selectedFiles.length >= 10) {
            dropZone.classList.add('opacity-50', 'pointer-events-none');
        } else {
            dropZone.classList.remove('opacity-50', 'pointer-events-none');
        }
    }

    function updateInput() {
        // Recréer le DataTransfer pour mettre à jour l'input file
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        document.getElementById('photosInput').files = dt.files;
    }
</script>
@endpush
@endsection
