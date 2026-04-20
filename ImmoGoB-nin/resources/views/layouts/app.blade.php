<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ImmoGo - Trouvez votre chez-vous')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-50 font-inter">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-cyan-400 rounded-lg flex items-center justify-center">
                        <i class="fas fa-home text-white text-sm"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-lg">ImmoGo</span>
                </a>

                {{-- Search --}}
                <form action="{{ route('biens.liste') }}" method="GET" class="hidden md:flex flex-1 max-w-md mx-8">
                    <div class="relative w-full">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Rechercher des biens, villes, agences..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:border-cyan-400 bg-gray-50">
                    </div>
                </form>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    @auth
                        {{-- Notifications --}}
                        <a href="{{ route('client.notifications') }}" class="relative p-2 text-gray-500 hover:text-cyan-500">
                            <i class="fas fa-bell text-lg"></i>
                            @php $unread = auth()->user()->notificationsImmogo()->where('lu', false)->count(); @endphp
                            @if($unread > 0)
                                <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $unread }}</span>
                            @endif
                        </a>
                        {{-- Avatar --}}
                        <div class="relative group">
                            <button class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-cyan-400 flex items-center justify-center text-white text-sm font-semibold overflow-hidden">
                                    @if(auth()->user()->client?->avatar)
                                        <img src="{{ asset('storage/' . auth()->user()->client->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    @endif
                                </div>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                                <div class="p-3 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->prenom }} {{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                                @if(auth()->user()->isClient())
                                    <a href="{{ route('client.profil') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-user w-4"></i> Profil</a>
                                    <a href="{{ route('client.favoris') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-heart w-4"></i> Favoris</a>
                                    <a href="{{ route('client.historique') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-history w-4"></i> Historique</a>
                                @elseif(auth()->user()->isAdminAgence())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-tachometer-alt w-4"></i> Dashboard</a>
                                @elseif(auth()->user()->isSuperAdmin())
                                    <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-tachometer-alt w-4"></i> Dashboard</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt w-4"></i> Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-cyan-500 font-medium">Connexion</a>
                        <a href="{{ route('register') }}" class="bg-cyan-400 hover:bg-cyan-500 text-white text-sm font-medium px-4 py-2 rounded-full transition">S'inscrire</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Bottom nav mobile --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-50">
        <div class="flex items-center justify-around py-2">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('home') ? 'text-cyan-500' : 'text-gray-400' }}">
                <i class="fas fa-home text-lg"></i>
                <span class="text-xs">Home</span>
            </a>
            @auth
            <a href="{{ route('client.favoris') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('client.favoris') ? 'text-cyan-500' : 'text-gray-400' }}">
                <i class="fas fa-heart text-lg"></i>
                <span class="text-xs">Favoris</span>
            </a>
            <a href="{{ route('client.historique') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('client.historique') ? 'text-cyan-500' : 'text-gray-400' }}">
                <i class="fas fa-history text-lg"></i>
                <span class="text-xs">History</span>
            </a>
            <a href="{{ route('client.notifications') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('client.notifications') ? 'text-cyan-500' : 'text-gray-400' }}">
                <i class="fas fa-bell text-lg"></i>
                <span class="text-xs">Notifs</span>
            </a>
            <a href="{{ route('client.profil') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('client.profil') ? 'text-cyan-500' : 'text-gray-400' }}">
                <i class="fas fa-user text-lg"></i>
                <span class="text-xs">Profil</span>
            </a>
            @else
            <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 text-gray-400">
                <i class="fas fa-sign-in-alt text-lg"></i>
                <span class="text-xs">Connexion</span>
            </a>
            @endauth
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
