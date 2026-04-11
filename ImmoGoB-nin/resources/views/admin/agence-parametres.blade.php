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
            <p class="text-gray-500 text-sm">Modifiez le logo et le nom de votre agence.</p>
        </div>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.agence.parametres.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Logo actuel + upload --}}
            <div>
                <label class="form-label">Logo de l'agence</label>
                <div class="flex items-center gap-4">
                    <div id="logoPreview" class="w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0 border-2 border-gray-200">
                        @if($agence->logo)
                            <img src="{{ Storage::url($agence->logo) }}" class="w-full h-full object-cover" id="logoImg">
                        @else
                            <i class="fas fa-building text-gray-300 text-3xl" id="logoIcon"></i>
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

            {{-- Configuration FedaPay --}}
            <div class="border-t border-gray-100 pt-5">
                <p class="text-xs font-bold text-cyan-600 uppercase tracking-wider mb-4 flex items-center gap-1">
                    <i class="fas fa-credit-card"></i> Configuration Paiement FedaPay
                </p>
                <p class="text-xs text-gray-500 mb-4">
                    Renseignez votre clé secrète FedaPay pour que les paiements de vos clients arrivent directement sur votre compte.
                    Créez votre compte sur <a href="https://fedapay.com" target="_blank" class="text-cyan-500 hover:underline">fedapay.com</a>.
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="form-label">Clé secrète FedaPay <span class="text-red-500">*</span></label>
                        <p class="text-xs text-orange-600 mb-2 font-medium">
                            ⚠️ Attention : saisissez la clé <strong>SECRÈTE</strong> (commence par <code class="bg-gray-100 px-1 rounded">sk_sandbox_</code> ou <code class="bg-gray-100 px-1 rounded">sk_live_</code>), PAS la clé publique.
                        </p>
                        <div class="relative">
                            <i class="fas fa-key absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="password" name="fedapay_secret_key" id="fedapayKey"
                                placeholder="sk_sandbox_... ou sk_live_..."
                                class="form-input pl-10 pr-10 @error('fedapay_secret_key') border-red-400 @enderror">
                            <button type="button" onclick="toggleFedaKey()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye text-sm" id="fedaEye"></i>
                            </button>
                        </div>
                        @if($agence->fedapay_secret_key)
                            <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                <i class="fas fa-check-circle"></i> Clé configurée — laissez vide pour ne pas changer
                            </p>
                        @else
                            <p class="text-xs text-orange-500 mt-1 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle"></i> Aucune clé configurée — les paiements utiliseront le compte ImmoGo
                            </p>
                        @endif
                        @error('fedapay_secret_key')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label">Environnement</label>
                        <select name="fedapay_env" class="form-input">
                            <option value="sandbox" {{ ($agence->fedapay_env ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>
                                Sandbox (test)
                            </option>
                            <option value="live" {{ ($agence->fedapay_env ?? '') === 'live' ? 'selected' : '' }}>
                                Live (production)
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Infos non modifiables --}}            <div class="bg-gray-50 rounded-xl p-4 space-y-2">
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
            const preview = document.getElementById('logoPreview');
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function toggleFedaKey() {
    const input = document.getElementById('fedapayKey');
    const icon  = document.getElementById('fedaEye');
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
