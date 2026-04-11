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
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @php $photo = $bien->photos->first(); @endphp
                            <div class="w-12 h-10 rounded-xl overflow-hidden flex-shrink-0">
                                @if($photo)
                                    <img src="{{ Storage::url($photo->chemin) }}" class="w-full h-full object-cover">
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

                    {{-- Statut avec gestion des règles --}}
                    <td class="px-6 py-4">
                        @if($bien->statut === 'vendu')
                            {{-- Vendu = définitif, pas de modification --}}
                            <div class="flex items-center gap-2">
                                <span class="badge-vendu">Vendu</span>
                                <span class="text-xs text-gray-400 italic">Définitif</span>
                            </div>

                        @elseif(in_array($bien->statut, ['loue', 'reserve']))
                            {{-- Loué ou réservé = peut remettre en disponible --}}
                            <div class="flex items-center gap-2">
                                <span class="badge-{{ $bien->statut }}">{{ ucfirst($bien->statut) }}</span>
                                <form method="POST" action="{{ route('admin.biens.statut', $bien) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="disponible">
                                    <button type="submit"
                                        onclick="return confirm('Remettre ce bien en disponible ? Les contrats en cours seront annulés.')"
                                        class="text-xs bg-green-50 hover:bg-green-100 text-green-600 font-medium px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                                        <i class="fas fa-redo text-xs"></i>
                                        Libérer
                                    </button>
                                </form>
                            </div>

                        @else
                            {{-- Disponible ou indisponible = sélecteur complet --}}
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
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $bien->is_published ? 'bg-cyan-400' : 'bg-gray-200' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $bien->is_published ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </td>

                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.biens.edit', $bien) }}"
                                class="p-2 text-gray-400 hover:text-cyan-500 transition"
                                title="Modifier">
                                <i class="fas fa-edit text-sm"></i>
                            </a>
                            @if($bien->statut !== 'vendu')
                                <form method="POST" action="{{ route('admin.biens.destroy', $bien) }}"
                                    onsubmit="return confirm('Supprimer ce bien définitivement ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition" title="Supprimer">
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
                        <i class="fas fa-home text-4xl mb-3 block text-gray-200"></i>
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
</div>
@endsection
