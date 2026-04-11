@extends('layouts.admin')

@section('title', 'Paramètres de l\'agence')

@section('content')
<div class="max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Paramètres de l'agence</h1>
            <p class="text-gray-500 text-sm">Modifiez le logo, le nom et la configuration de paiement.</p>
        </div>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.agence.parametres.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Logo --}}
            <div>
                <label class="form-label">Logo de l'agence</label>
                <div class="flex items-center gap-4">
                    <div id="logoPreview" class="w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0 border-2 border-gray-200">
                        @if($agence->logo)
                            <img src="{{ Storage::url($agence->logo) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-building text-gray-300 text-3xl"></i>
                        @endif
                    </div>
                    <div>
                        <label class="cursor-pointer inline-flex items-center gap-2 bg-cyan-50 hover:bg-cyan-100 text-cyan-600 font-medium text-sm px-4 py-2 rounded-xl transition">
                            <i class="fas fa-upload"></i> Changer le logo
                            <input type="file" name="logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                        </label>
                        <p class="text-xs text-gray-400 mt-1.5">JPG, PNG — max 2MB</p>
                    </div>
                </div>
                @error('logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Nom commercial --}}
            <div>
                <label class="form-label">Nom Commercial</label>
                <input type="text" name="nom_commercial"
                    value="{{ old('nom_commercial', $agence->nom_commercial) }}"
                    class="form-input @error('nom_commercial') border-red-400 @enderror" required>
                @error('nom_commercial')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Téléphone --}}
            <div>
                <label class="form-label">Téléphone</label>
                <input type="tel" name="telephone"
                    value="{{ old('telephone', $agence->telephone) }}"
                    placeholder="+229 01 00 00 00" class="form-input">
            </div>

            {{-- Configuration KKiapay --}}
            <div class="border-t border-gray-100 pt-5">
                <p class="text-xs font-bold text-cyan-600 uppercase tracking-wider mb-4 flex items-center gap-1">
                    <i class="fas fa-credit-card"></i> Configuration Paiement KKiapay
                </p>
                <p class="text-xs text-gray-500 mb-4">
                    Renseignez vos 3 clés KKiapay pour que les paiements arrivent directement sur votre compte.
                    Créez votre compte sur <a href="https://kkiapay.me" target="_blank" class="text-cyan-500 hover:underline">kkiapay.me</a>.
                </p>

                @if($agence->hasKkiapay())
                    <div class="bg-green-50 rounded-xl p-3 mb-4 flex items-center gap-2 text-sm text-green-700">
                        <i class="fas fa-check-circle"></i>
                        KKiapay configuré — les paiements arrivent sur votre compte.
                    </div>
                @else
                    <div class="bg-orange-50 rounded-xl p-3 mb-4 flex items-center gap-2 text-sm text-orange-600">
                        <i class="fas fa-exclamation-triangle"></i>
                        KKiapay non configuré — les paiements utiliseront le compte ImmoGo par défaut.
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="form-label">Clé Publique <span class="text-xs text-gray-400">(Public API Key)</span></label>
                        <input type="text" name="kkiapay_public_key"
                            value="{{ old('kkiapay_public_key', $agence->kkiapay_public_key) }}"
                            placeholder="ex: 5dedd47034f711f1a2c61d4f994a8525"
                            class="form-input @error('kkiapay_public_key') border-red-400 @enderror">
                        @error('kkiapay_public_key')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Clé Privée <span class="text-xs text-gray-400">(Private API Key — commence par tpk_)</span></label>
                        <div class="relative">
                            <input type="password" name="kkiapay_private_key" id="privateKey"
                                placeholder="tpk_..."
                                class="form-input pr-10 @error('kkiapay_private_key') border-red-400 @enderror">
                            <button type="button" onclick="toggleKey('privateKey')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        @if($agence->kkiapay_private_key)
                            <p class="text-xs text-green-600 mt-1"><i class="fas fa-check-circle"></i> Configurée — laissez vide pour ne pas changer</p>
                        @endif
                        @error('kkiapay_private_key')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Secret <span class="text-xs text-gray-400">(Secret Key — commence par tsk_)</span></label>
                        <div class="relative">
                            <input type="password" name="kkiapay_secret" id="secretKey"
                                placeholder="tsk_..."
                                class="form-input pr-10 @error('kkiapay_secret') border-red-400 @enderror">
                            <button type="button" onclick="toggleKey('secretKey')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        @if($agence->kkiapay_secret)
                            <p class="text-xs text-green-600 mt-1"><i class="fas fa-check-circle"></i> Configuré — laissez vide pour ne pas changer</p>
                        @endif
                        @error('kkiapay_secret')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Environnement</label>
                        <select name="kkiapay_sandbox" class="form-input">
                            <option value="1" {{ ($agence->kkiapay_sandbox ?? true) ? 'selected' : '' }}>Sandbox (test)</option>
                            <option value="0" {{ !($agence->kkiapay_sandbox ?? true) ? 'selected' : '' }}>Production (live)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Infos non modifiables --}}
            <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Informations non modifiables</p>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Email</span>
                    <span class="text-gray-700">{{ $agence->email }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Ville</span>
                    <span class="text-gray-700">{{ $agence->ville }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Secteur</span>
                    <span class="text-gray-700">{{ $agence->secteur }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">Pour modifier ces informations, contactez le SuperAdmin.</p>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('logoPreview').innerHTML =
                `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function toggleKey(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection
