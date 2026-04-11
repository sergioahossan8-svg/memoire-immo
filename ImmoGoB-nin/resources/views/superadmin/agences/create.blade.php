@extends('layouts.admin')

@section('title', 'Nouvelle Agence')
@section('search_placeholder', 'Rechercher une agence, un admin...')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('superadmin.agences.index') }}" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Nouvelle Agence</h1>
            <p class="text-gray-500 text-sm">Complétez les informations pour créer l'entité et son admin.</p>
        </div>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('superadmin.agences.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <p class="text-xs font-bold text-cyan-600 uppercase tracking-wider flex items-center gap-1">
                <i class="fas fa-building"></i> Informations Agence
            </p>

            <div>
                <label class="form-label">Nom Commercial</label>
                <input type="text" name="nom_commercial" value="{{ old('nom_commercial') }}"
                    placeholder="ex: Immobilier Horizon"
                    class="form-input @error('nom_commercial') border-red-400 @enderror" required>
                @error('nom_commercial')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Secteur</label>
                    <select name="secteur" class="form-input" required>
                        @foreach(['Résidentiel', 'Commercial', 'Industriel', 'Mixte'] as $s)
                            <option value="{{ $s }}" {{ old('secteur') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Ville / Siège</label>
                    <input type="text" name="ville" value="{{ old('ville') }}" placeholder="Cotonou, Calavi..."
                        class="form-input @error('ville') border-red-400 @enderror" required>
                    @error('ville')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Adresse Complète</label>
                <input type="text" name="adresse_complete" value="{{ old('adresse_complete') }}"
                    placeholder="Cotonou, Akpakpa"
                    class="form-input @error('adresse_complete') border-red-400 @enderror" required>
                @error('adresse_complete')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Email de l'agence</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="contact@agence.com"
                    class="form-input @error('email') border-red-400 @enderror" required>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Logo de l'agence</label>
                <div class="flex items-center gap-4">
                    <div id="logoPreview" class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                        <i class="fas fa-building text-gray-300 text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <label class="cursor-pointer flex items-center gap-2 text-sm text-cyan-500 hover:text-cyan-600 font-medium">
                            <i class="fas fa-upload"></i> Choisir un logo
                            <input type="file" name="logo" id="logoInput" accept="image/*" class="hidden"
                                onchange="previewLogo(this)">
                        </label>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG — max 2MB</p>
                    </div>
                </div>
                @error('logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Téléphone (optionnel)</label>
                <input type="tel" name="telephone" value="{{ old('telephone') }}" placeholder="+229 01 00 00 00"
                    class="form-input">
            </div>

            <hr class="border-gray-100 my-2">

            <p class="text-xs font-bold text-cyan-600 uppercase tracking-wider flex items-center gap-1">
                <i class="fas fa-user-tie"></i> Compte Administrateur Principal
            </p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="admin_nom" value="{{ old('admin_nom') }}" placeholder="Dupont"
                        class="form-input @error('admin_nom') border-red-400 @enderror" required>
                    @error('admin_nom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Prénom</label>
                    <input type="text" name="admin_prenom" value="{{ old('admin_prenom') }}" placeholder="Jean"
                        class="form-input @error('admin_prenom') border-red-400 @enderror" required>
                    @error('admin_prenom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Email Professionnel</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                        placeholder="admin@agence.com"
                        class="form-input pl-10 @error('admin_email') border-red-400 @enderror" required>
                </div>
                @error('admin_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Téléphone</label>
                <div class="relative">
                    <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="tel" name="admin_telephone" value="{{ old('admin_telephone') }}"
                        placeholder="+229 01 61 59 63 58" class="form-input pl-10">
                </div>
            </div>

            <div>
                <label class="form-label">Numéro WhatsApp</label>
                <div class="relative">
                    <i class="fab fa-whatsapp absolute left-3 top-1/2 -translate-y-1/2 text-green-500 text-sm"></i>
                    <input type="tel" name="admin_whatsapp" value="{{ old('admin_whatsapp') }}"
                        placeholder="+229 01 61 59 63 58"
                        class="form-input pl-10 @error('admin_whatsapp') border-red-400 @enderror">
                </div>
                <p class="text-xs text-gray-400 mt-1">Ce numéro sera utilisé pour le support WhatsApp de l'agence.</p>
                @error('admin_whatsapp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Mot de passe</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="password" name="admin_password" id="admin_password"
                        class="form-input pl-10 pr-10 @error('admin_password') border-red-400 @enderror" required>
                    <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                    </button>
                </div>
                @error('admin_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="bg-cyan-50 rounded-xl p-3 text-xs text-cyan-700">
                <i class="fas fa-info-circle mr-1"></i>
                L'administrateur recevra un email d'invitation automatique pour définir son mot de passe et activer le compte de l'agence.
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-check"></i> Confirmer et Créer l'Agence
                </button>
                <a href="{{ route('superadmin.agences.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('logoPreview');
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function togglePwd() {
    const input = document.getElementById('admin_password');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash text-sm';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye text-sm';
    }
}
</script>
@endpush
@endsection
