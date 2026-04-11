<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ImmoGo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 mb-2">
                <div class="w-10 h-10 bg-cyan-400 rounded-xl flex items-center justify-center">
                    <i class="fas fa-home text-white text-lg"></i>
                </div>
                <span class="font-bold text-gray-800 text-2xl">ImmoGo</span>
            </div>
            <p class="text-xs text-gray-400 uppercase tracking-widest">Trouvez votre chez-vous</p>
        </div>

        <div class="card p-8">
            <h1 class="text-2xl font-bold text-gray-800 text-center mb-1">Bon retour parmi nous</h1>
            <p class="text-sm text-gray-500 text-center mb-6">Connectez-vous pour gérer vos recherches<br>et vos propriétés favorites.</p>

            <h2 class="text-sm font-semibold text-gray-700 text-center mb-1">Se connecter</h2>
            <p class="text-xs text-gray-400 text-center mb-6">Choisissez votre méthode de connexion préférée</p>

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="form-label">E-mail ou téléphone</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="nom@exemple.com"
                            class="form-input pl-10 @error('email') border-red-400 @enderror" required>
                    </div>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="form-label mb-0">Mot de passe</label>
                        <a href="#" class="text-xs text-cyan-500 hover:underline">Mot de passe oublié ?</a>
                    </div>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••"
                            class="form-input pl-10 pr-10" required>
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 accent-cyan-400">
                    <label for="remember" class="text-sm text-gray-600">Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn-primary w-full">
                    Connexion <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Pas encore de compte ? <a href="{{ route('register') }}" class="text-cyan-500 font-medium hover:underline">Inscrivez-vous</a>
            </p>
            <p class="text-center text-xs text-gray-400 mt-2">
                <i class="fas fa-lock text-xs"></i> Connexion sécurisée par ImmoGo SSL
            </p>
        </div>

        <div class="flex items-center justify-center gap-8 mt-8 text-center">
            <div class="flex flex-col items-center gap-1">
                <div class="w-8 h-8 bg-cyan-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-cyan-500 text-xs"></i>
                </div>
                <span class="text-xs text-gray-500">Annonces vérifiées</span>
            </div>
            <div class="flex flex-col items-center gap-1">
                <div class="w-8 h-8 bg-cyan-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-star text-cyan-500 text-xs"></i>
                </div>
                <span class="text-xs text-gray-500">Accès gratuit</span>
            </div>
            <div class="flex flex-col items-center gap-1">
                <div class="w-8 h-8 bg-cyan-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-headset text-cyan-500 text-xs"></i>
                </div>
                <span class="text-xs text-gray-500">Support 24/7</span>
            </div>
        </div>

        <div class="flex items-center justify-center gap-6 mt-6 text-xs text-gray-400">
            <a href="#" class="hover:text-gray-600">Aide</a>
            <a href="#" class="hover:text-gray-600">Confidentialité</a>
            <a href="#" class="hover:text-gray-600">Conditions</a>
        </div>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
