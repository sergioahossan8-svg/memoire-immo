@extends('layouts.admin')

@section('title', 'Gestion des Biens')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestion des Biens</h1>
        <p class="text-gray-500 text-sm">{{ $biens->total() }} biens enregistrés au total</p>
    </div>
    <div class="flex gap-3">
        <button onclick="window.open('{{ route('admin.biens.export-pdf') }}', '_blank')"
            class="flex items-center gap-2 border border-gray-200 text-gray-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-gray-50 transition">
            <i class="fas fa-file-pdf text-red-400 text-sm"></i> Exporter PDF
        </button>
        <a href="{{ route('admin.biens.create') }}" class="btn-primary text-sm">
            <i class="fas fa-plus"></i> Nouveau bien
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-4 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('info'))
    <div class="bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded-xl text-sm mb-4 flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="font-semibold mb-0.5">Action requise</p>
            <p>{{ session('info') }}</p>
            <p class="mt-1 text-xs text-amber-600">👉 Cherchez votre bien dans la liste ci-dessous et cliquez sur le toggle dans la colonne <strong>"Publié"</strong>.</p>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="card overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Bien</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Prix</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Statut</th>
                <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Publié</th>
                <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($biens as $bien)
                <tr class="hover:bg-gray-50 transition"
                    data-bien-id="{{ $bien->id }}"
                    data-bien-titre="{{ $bien->titre }}"
                    data-bien-statut="{{ $bien->statut }}"
                    data-bien-transaction="{{ $bien->transaction }}"
                    data-especes-url="{{ route('admin.biens.payer-especes', $bien) }}"
                    data-virement-url="{{ route('admin.biens.confirmer-virement', $bien) }}"
                    oncontextmenu="showContextMenu(event, this)"
                    style="cursor: context-menu;">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @php $photo = $bien->photos->first(); @endphp
                            <div class="w-12 h-10 rounded-xl overflow-hidden flex-shrink-0">
                                @if($photo)
                                    <img src="{{ str_starts_with($photo->chemin, 'http') ? $photo->chemin : asset('storage/' . $photo->chemin) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-home text-gray-300 text-xs"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $bien->titre }}</p>
                                <p class="text-xs text-gray-400">{{ $bien->typeBien->libelle ?? 'N/A' }} · {{ $bien->ville }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $bien->prix_formate }}</td>

                    {{-- Statut avec gestion des règles métier --}}
                    <td class="px-6 py-4">
                        @if($bien->statut === 'vendu')
                            {{-- Vendu = définitif, aucune modification possible --}}
                            <div class="flex items-center gap-2">
                                <span class="badge-vendu">Vendu</span>
                                <span class="text-xs text-gray-400 italic">Définitif</span>
                            </div>

                        @elseif($bien->statut === 'reserve')
                            {{-- Réservé : l'admin peut → Libérer, Vendu, Loué --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="badge-reserve">Réservé</span>
                                {{-- Libérer --}}
                                <form method="POST" action="{{ route('admin.biens.statut', $bien) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="libere">
                                    <button type="submit"
                                        onclick="return confirm('Libérer ce bien ? Le contrat en cours sera annulé.')"
                                        class="text-xs bg-green-50 hover:bg-green-100 text-green-700 font-medium px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                                        <i class="fas fa-unlock text-xs"></i> Libérer
                                    </button>
                                </form>
                                {{-- Marquer vendu --}}
                                <form method="POST" action="{{ route('admin.biens.statut', $bien) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="vendu">
                                    <button type="submit"
                                        onclick="return confirm('Marquer ce bien comme VENDU ? Cette action est irréversible.')"
                                        class="text-xs bg-red-50 hover:bg-red-100 text-red-600 font-medium px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                                         Vendu
                                    </button>
                                </form>
                                {{-- Marquer loué --}}
                                <form method="POST" action="{{ route('admin.biens.statut', $bien) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="loue">
                                    <button type="submit"
                                        onclick="return confirm('Marquer ce bien comme Loué ?')"
                                        class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 font-medium px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                                        </i> Loué
                                    </button>
                                </form>
                            </div>

                        @elseif($bien->statut === 'loue')
                            {{-- Loué : l'admin peut → Libérer (remettre en disponible) --}}
                            <div class="flex items-center gap-2">
                                <span class="badge-loue">Loué</span>
                                <form method="POST" action="{{ route('admin.biens.statut', $bien) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="libere">
                                    <button type="submit"
                                        onclick="return confirm('Libérer ce bien loué ? Il redeviendra disponible.')"
                                        class="text-xs bg-green-50 hover:bg-green-100 text-green-700 font-medium px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                                        Libérer
                                    </button>
                                </form>
                            </div>

                        @else
                            {{-- Disponible ou indisponible = sélecteur simple --}}
                            <form method="POST" action="{{ route('admin.biens.statut', $bien) }}" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <select name="statut" onchange="this.form.submit()"
                                    class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-cyan-400 bg-white">
                                    <option value="disponible" {{ $bien->statut === 'disponible' ? 'selected' : '' }}>Disponible</option>
                                    <option value="indisponible" {{ $bien->statut === 'indisponible' ? 'selected' : '' }}>Indisponible</option>
                                </select>
                            </form>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <form method="POST" action="{{ route('admin.biens.publier', $bien) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                title="{{ $bien->is_published ? 'Cliquer pour dépublier' : 'Cliquer pour publier' }}"
                                class="flex items-center gap-2 group">
                                <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $bien->is_published ? 'bg-cyan-400' : 'bg-gray-200' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $bien->is_published ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </div>
                                <span class="text-xs font-medium {{ $bien->is_published ? 'text-cyan-600' : 'text-red-400' }}">
                                    {{ $bien->is_published ? 'Publié' : 'Non publié' }}
                                </span>
                            </button>
                        </form>
                    </td>

                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.biens.edit', $bien) }}"
                                class="p-2 text-gray-400 hover:text-cyan-500 transition rounded-lg hover:bg-cyan-50"
                                title="Modifier">
                                <i class="fas fa-pen text-sm"></i>
                            </a>
                            @if($bien->statut !== 'vendu')
                                <form method="POST" action="{{ route('admin.biens.destroy', $bien) }}"
                                    onsubmit="return confirm('Supprimer ce bien définitivement ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition rounded-lg hover:bg-red-50" title="Supprimer">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-home text-4xl mb-3 block"></i>
                        Aucun bien pour le moment.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $biens->links() }}
    </div>
</div>

{{-- Légende --}}
<div class="mt-4 flex items-center gap-6 text-xs text-gray-400">
    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span> Disponible — visible sur le site</span>
    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span> Réservé — libérable par l'admin</span>
    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span> Loué — libérable par l'admin</span>
    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span> Vendu — définitif, non modifiable</span>
    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> Clic droit → confirmer paiement espèces</span>
</div>

{{-- Menu contextuel clic droit --}}
<div id="contextMenu"
    class="hidden fixed z-50 bg-white border border-gray-200 rounded-xl shadow-xl py-1 min-w-[220px]"
    style="top:0;left:0;">
    <div class="px-4 py-2 border-b border-gray-100">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions rapides</p>
        <p id="ctxBienTitre" class="text-sm font-medium text-gray-800 truncate mt-0.5"></p>
    </div>
    <button id="ctxBtnEspeces"
        onclick="confirmerPaiementSurPlace('especes')"
        class="w-full text-left px-4 py-2.5 text-sm flex items-center gap-2.5 hover:bg-amber-50 text-amber-700 font-medium transition">
        <i class="fas fa-money-bill-wave text-amber-500 w-4"></i>
        Payé en espèces ✓
    </button>
    <button id="ctxBtnVirement"
        onclick="confirmerPaiementSurPlace('virement')"
        class="w-full text-left px-4 py-2.5 text-sm flex items-center gap-2.5 hover:bg-blue-50 text-blue-700 font-medium transition">
        <i class="fas fa-university text-blue-500 w-4"></i>
        Virement bancaire confirmé ✓
    </button>
    <div class="border-t border-gray-100 mt-1 pt-1">
        <button onclick="closeContextMenu()"
            class="w-full text-left px-4 py-2 text-xs text-gray-400 hover:text-gray-600 transition flex items-center gap-2">
            <i class="fas fa-times w-4"></i> Fermer
        </button>
    </div>
</div>

{{-- Overlay transparent pour fermer le menu --}}
<div id="ctxOverlay" class="hidden fixed inset-0 z-40" onclick="closeContextMenu()"></div>

@push('scripts')
<script>
    let currentRow = null;
    const menu = document.getElementById('contextMenu');
    const overlay = document.getElementById('ctxOverlay');

    function showContextMenu(e, row) {
        e.preventDefault();
        currentRow = row;

        const statut = row.dataset.bienStatut;
        const titre  = row.dataset.bienTitre;
        const transaction = row.dataset.bienTransaction;

        document.getElementById('ctxBienTitre').textContent = titre;

        // Afficher "Payé en espèces" seulement si le bien est disponible ou en attente
        const btnEspeces = document.getElementById('ctxBtnEspeces');
        if (statut === 'vendu') {
            btnEspeces.classList.add('hidden');
        } else {
            btnEspeces.classList.remove('hidden');
            const label = transaction === 'location' ? 'Payé en espèces (location)' : 'Payé en espèces (vente)';
            btnEspeces.querySelector('span, text') ;
            btnEspeces.childNodes[btnEspeces.childNodes.length - 1].textContent = ' ' + label + ' ✓';
        }

        // Positionner le menu
        const x = Math.min(e.clientX, window.innerWidth  - 240);
        const y = Math.min(e.clientY, window.innerHeight - 120);
        menu.style.top  = y + 'px';
        menu.style.left = x + 'px';
        menu.classList.remove('hidden');
        overlay.classList.remove('hidden');
    }

    function closeContextMenu() {
        menu.classList.add('hidden');
        overlay.classList.add('hidden');
        currentRow = null;
    }

    function confirmerPaiementSurPlace(mode) {
        if (!currentRow) return;
        const titre = currentRow.dataset.bienTitre;
        const transaction = currentRow.dataset.bienTransaction;
        const action = transaction === 'location' ? 'loué' : 'vendu';
        const url = mode === 'virement'
            ? currentRow.dataset.virementUrl
            : currentRow.dataset.especesUrl;
        const modeLabel = mode === 'virement' ? 'virement bancaire' : 'espèces';

        closeContextMenu();

        if (!confirm(`Confirmer que le client a payé par ${modeLabel} pour "${titre}" ?\n\nLe bien sera marqué comme ${action} et disparaîtra du site.`)) return;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const flash = document.createElement('div');
                flash.className = 'bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-4 flex items-center gap-2';
                flash.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                document.querySelector('.card').before(flash);
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(() => alert('Une erreur est survenue. Veuillez recharger la page.'));
    }

    // Garder l'ancienne fonction pour compatibilité
    function confirmerEspeces() { confirmerPaiementSurPlace('especes'); }

    // Fermer le menu si on presse Echap
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeContextMenu();
    });
</script>
@endpush
@endsection
