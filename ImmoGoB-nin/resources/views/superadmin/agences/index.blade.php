@extends('layouts.admin')

@section('title', 'Gestion des Agences')
@section('search_placeholder', 'Rechercher une agence, un admin...')

@section('content')
<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestion des Agences</h1>
        <p class="text-gray-500 text-sm">Déployez de nouvelles agences et configurez leurs accès administrateur.</p>
    </div>
    <div class="flex gap-3">
        <button onclick="window.open('{{ route('superadmin.agences.export-pdf') }}', '_blank')"
            class="flex items-center gap-2 border border-gray-200 text-gray-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-gray-50 transition">
            <i class="fas fa-file-pdf text-red-400 text-sm"></i> Exporter PDF
        </button>
        <a href="{{ route('superadmin.agences.create') }}" class="btn-primary text-sm">
            <i class="fas fa-plus"></i> Nouvelle Agence
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="stat-card">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Agences Actives</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['agences_actives'] }}</p>
        <p class="text-xs text-green-500 mt-1">+5 nouvelles cette semaine</p>
    </div>
    <div class="stat-card">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Administrateurs</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['administrateurs'] }}</p>
        <p class="text-xs text-gray-400 mt-1">99% de comptes vérifiés</p>
    </div>
    <div class="stat-card">
        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Annonces Publiées</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['annonces_publiees'] }}</p>
        <p class="text-xs text-green-500 mt-1">Record mensuel battu</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Formulaire création agence --}}
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-5">
            <div class="w-7 h-7 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-building text-cyan-500 text-xs"></i>
            </div>
            <h3 class="font-semibold text-gray-800">Ajouter une Agence</h3>
        </div>
        <p class="text-sm text-gray-500 mb-5">Complétez les informations pour créer l'entité et son admin.</p>

        <form method="POST" action="{{ route('superadmin.agences.store') }}" class="space-y-4">
            @csrf

            <p class="text-xs font-bold text-cyan-600 uppercase tracking-wider flex items-center gap-1">
                <i class="fas fa-building"></i> Informations Agence
            </p>

            <div>
                <label class="form-label">Nom Commercial</label>
                <input type="text" name="nom_commercial" value="{{ old('nom_commercial') }}" placeholder="ex: Immobilier Horizon"
                    class="form-input @error('nom_commercial') border-red-400 @enderror" required>
                @error('nom_commercial')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Secteur</label>
                    <select name="secteur" class="form-input" required>
                        <option value="Résidentiel" {{ old('secteur') === 'Résidentiel' ? 'selected' : '' }}>Résidentiel</option>
                        <option value="Commercial" {{ old('secteur') === 'Commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="Industriel" {{ old('secteur') === 'Industriel' ? 'selected' : '' }}>Industriel</option>
                        <option value="Mixte" {{ old('secteur') === 'Mixte' ? 'selected' : '' }}>Mixte</option>
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
                <input type="text" name="adresse_complete" value="{{ old('adresse_complete') }}" placeholder="Cotonou, Akpakpa"
                    class="form-input @error('adresse_complete') border-red-400 @enderror" required>
                @error('adresse_complete')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Email de l'agence</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="contact@agence.com"
                    class="form-input @error('email') border-red-400 @enderror" required>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <p class="text-xs font-bold text-cyan-600 uppercase tracking-wider flex items-center gap-1 pt-2">
                <i class="fas fa-user-tie"></i> Compte Administrateur
            </p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Nom de l'Admin</label>
                    <input type="text" name="admin_nom" value="{{ old('admin_nom') }}" placeholder="Prénom Nom"
                        class="form-input @error('admin_nom') border-red-400 @enderror" required>
                    @error('admin_nom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Prénom</label>
                    <input type="text" name="admin_prenom" value="{{ old('admin_prenom') }}" placeholder="Prénom"
                        class="form-input @error('admin_prenom') border-red-400 @enderror" required>
                    @error('admin_prenom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Email Professionnel</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin@agence.com"
                        class="form-input pl-10 @error('admin_email') border-red-400 @enderror" required>
                </div>
                @error('admin_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Téléphone</label>
                <div class="relative">
                    <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="tel" name="admin_telephone" value="{{ old('admin_telephone') }}" placeholder="+229 01 61 59 63 58"
                        class="form-input pl-10">
                </div>
            </div>

            <div>
                <label class="form-label">Mot de passe</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="password" name="admin_password" id="admin_password"
                        class="form-input pl-10 pr-10 @error('admin_password') border-red-400 @enderror" required>
                    <button type="button" onclick="togglePassword('admin_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-eye text-sm"></i>
                    </button>
                </div>
                @error('admin_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="bg-cyan-50 rounded-xl p-3 text-xs text-cyan-700">
                <i class="fas fa-info-circle mr-1"></i>
                L'administrateur recevra un email d'invitation automatique pour définir son mot de passe et activer le compte de l'agence.
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-check"></i> Confirmer et Créer l'Agence
            </button>
        </form>
    </div>

    {{-- Liste des agences --}}
    <div class="card overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">{{ $agences->total() }} Agences enregistrées au total</p>
            <div class="flex gap-2 text-xs text-gray-400">
                <span class="cursor-pointer hover:text-gray-600">Plus récents</span>
                <span>·</span>
                <span class="cursor-pointer hover:text-gray-600">Alphabétique</span>
            </div>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($agences as $agence)
                <div class="p-4 hover:bg-gray-50 transition flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($agence->logo)
                            <img src="{{ str_starts_with($agence->logo, 'http') ? $agence->logo : asset('storage/' . $agence->logo) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-building text-gray-400 text-sm"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $agence->nom_commercial }}</p>
                        <p class="text-xs text-gray-400">{{ $agence->ville }}</p>
                    </div>
                    @if($agence->adminPrincipal)
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="w-7 h-7 rounded-full bg-cyan-400 flex items-center justify-center text-white text-xs font-semibold">
                                {{ strtoupper(substr($agence->adminPrincipal->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:block">
                                <p class="text-xs font-medium text-gray-700">{{ $agence->adminPrincipal->prenom }} {{ $agence->adminPrincipal->name }}</p>
                                <p class="text-xs text-gray-400">Gérant</p>
                            </div>
                        </div>
                    @endif
                    <span class="badge-{{ $agence->statut === 'actif' ? 'disponible' : ($agence->statut === 'en_attente' ? 'reserve' : 'vendu') }} flex-shrink-0">
                        {{ ucfirst(str_replace('_', ' ', $agence->statut)) }}
                    </span>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <a href="{{ route('superadmin.agences.show', $agence) }}" class="p-1.5 text-gray-400 hover:text-cyan-500 transition">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                        <form method="POST" action="{{ route('superadmin.agences.destroy', $agence) }}" onsubmit="return confirm('Supprimer cette agence ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 transition">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-building text-3xl mb-2 block text-gray-200"></i>
                    Aucune agence pour le moment.
                </div>
            @endforelse
        </div>

        {{-- Infos bas --}}
        <div class="p-4 border-t border-gray-100 grid grid-cols-2 gap-4">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-gray-400 text-xs"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-700">Invitations en attente</p>
                    <p class="text-gray-400">3 administrateurs n'ont pas encore activé leur accès.</p>
                    <a href="#" class="text-cyan-500 hover:underline">Relancer tous</a>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-globe text-gray-400 text-xs"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-700">Flux de données</p>
                    <p class="text-gray-400">Le flux XML/API est opérationnel pour 90% des agences.</p>
                    <a href="#" class="text-cyan-500 hover:underline">Voir le rapport</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection
