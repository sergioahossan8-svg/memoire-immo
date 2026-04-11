<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ImmoGo Admin')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-50 font-inter">

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col shadow-sm flex-shrink-0">
        {{-- Logo --}}
        <div class="p-6 border-b border-gray-100">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <div class="w-9 h-9 bg-cyan-400 rounded-xl flex items-center justify-center">
                    <i class="fas fa-home text-white"></i>
                </div>
                <span class="font-bold text-gray-800 text-xl">ImmoGo</span>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 p-4 space-y-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-3">Navigation</p>

            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('superadmin.dashboard') }}" class="sidebar-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5"></i> Tableau de bord
                </a>
                <a href="{{ route('superadmin.agences.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.agences*') ? 'active' : '' }}">
                    <i class="fas fa-building w-5"></i> Agences Partenaires
                    @php $pending = \App\Models\Agence::where('statut','en_attente')->count(); @endphp
                    @if($pending > 0)<span class="ml-auto bg-cyan-400 text-white text-xs px-2 py-0.5 rounded-full">{{ $pending }}</span>@endif
                </a>
                <a href="{{ route('superadmin.agences.index') }}" class="sidebar-link {{ request()->routeIs('superadmin.annonces*') ? 'active' : '' }}">
                    <i class="fas fa-list w-5"></i> Annonces Immo
                </a>
                <a href="{{ route('superadmin.utilisateurs') }}" class="sidebar-link {{ request()->routeIs('superadmin.utilisateurs*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5"></i> Utilisateurs
                </a>
                <a href="{{ route('superadmin.profil') }}" class="sidebar-link {{ request()->routeIs('superadmin.profil*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog w-5"></i> Mon Profil
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5"></i> Tableau de bord
                </a>
                <a href="{{ route('admin.biens.index') }}" class="sidebar-link {{ request()->routeIs('admin.biens*') ? 'active' : '' }}">
                    <i class="fas fa-home w-5"></i> Gestion des Biens
                </a>
                <a href="{{ route('admin.clients') }}" class="sidebar-link {{ request()->routeIs('admin.clients*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5"></i> Clients
                </a>
                <a href="{{ route('admin.reservations') }}" class="sidebar-link {{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check w-5"></i> Historiques
                </a>
            @endif

            @if(!auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.administrateurs.index') }}" class="sidebar-link {{ request()->routeIs('admin.administrateurs*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog w-5"></i> Paramètres Système
                </a>
                @if(auth()->user()->est_principal)
                <a href="{{ route('admin.agence.parametres') }}" class="sidebar-link {{ request()->routeIs('admin.agence.parametres*') ? 'active' : '' }}">
                    <i class="fas fa-building w-5"></i> Mon Agence
                </a>
                @endif
            @endif
        </nav>

        {{-- Support — uniquement pour les admins d'agence --}}
        @if(!auth()->user()->isSuperAdmin())
        <div class="p-4">
            <div class="bg-orange-50 rounded-xl p-4">
                <p class="text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">Support Dédié</p>
                <p class="text-xs text-gray-500 mb-3">Une question technique ? Nos experts sont là 24/7.</p>
                @php
                    $superAdmin = \App\Models\User::where('role', 'super_admin')->first();
                    $waNumber   = $superAdmin?->whatsapp
                        ? preg_replace('/[^0-9]/', '', $superAdmin->whatsapp)
                        : env('SUPERADMIN_WHATSAPP', '22900000000');
                    $agenceName = auth()->user()->agence?->nom_commercial ?? 'Admin';
                    $waMsg = urlencode("Bonjour, je suis {$agenceName} et j'ai besoin d'aide sur ImmoGo.");
                @endphp
                <a href="https://wa.me/{{ $waNumber }}?text={{ $waMsg }}"
                    target="_blank"
                    class="flex items-center gap-2 text-xs text-green-600 font-semibold hover:underline">
                    <i class="fab fa-whatsapp text-green-500 text-base"></i>
                    Contacter l'aide
                </a>
            </div>
        </div>
        @endif
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" placeholder="@yield('search_placeholder', 'Rechercher...')"
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:border-cyan-400 bg-gray-50">
                </div>
            </div>
            <div class="flex items-center gap-4 ml-4">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->prenom }} {{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400 uppercase tracking-wider">
                        {{ auth()->user()->isSuperAdmin() ? 'Super Administrateur' : 'Administrateur Agence' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full bg-cyan-400 flex items-center justify-center text-white font-semibold overflow-hidden">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash --}}
        @if(session('success'))
            <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2 text-sm">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2 text-sm">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
