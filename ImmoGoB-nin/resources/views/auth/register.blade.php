<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - ImmoGo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md">
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
            <h1 class="text-2xl font-bold text-gray-800 text-center mb-1">Créer un compte</h1>
            <p class="text-sm text-gray-500 text-center mb-6">Rejoignez des milliers d'utilisateurs et commencez votre recherche immobilière dès aujourd'hui.</p>

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="form-label">Nom complet</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Dupont"
                            class="form-input pl-10 @error('name') border-red-400 @enderror" required>
                    </div>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Prénom</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Jean"
                            class="form-input pl-10 @error('prenom') border-red-400 @enderror" required>
                    </div>
                    @error('prenom')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">E-mail</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="jean.dupont@exemple.com"
                            class="form-input pl-10 @error('email') border-red-400 @enderror" required>
                    </div>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Téléphone</label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="tel" name="telephone" value="{{ old('telephone') }}" placeholder="+229 01 23 45 67"
                            class="form-input pl-10">
                    </div>
                </div>

                <div>
                    <label class="form-label">Ville</label>
                    <div class="relative">
                        <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="ville" value="{{ old('ville') }}" placeholder="ex: Cotonou"
                            class="form-input pl-10 @error('ville') border-red-400 @enderror" required>
                    </div>
                    @error('ville')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Adresse complète</label>
                    <div class="relative">
                        <i class="fas fa-home absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="adresse" value="{{ old('adresse') }}" placeholder="ex: Akpakpa, Rue 123"
                            class="form-input pl-10">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Mot de passe</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="password" name="password" id="password" placeholder="••••••••"
                                class="form-input pl-10 pr-10 @error('password') border-red-400 @enderror" required>
                            <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Confirmation</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••"
                                class="form-input pl-10 pr-10" required>
                            <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-2">
                    <input type="checkbox" name="terms" id="terms" class="w-4 h-4 mt-0.5 accent-cyan-400" required>
                    <label for="terms" class="text-sm text-gray-600">
                        J'accepte les <a href="#" class="text-cyan-500 hover:underline">Conditions d'utilisation</a> et la <a href="#" class="text-cyan-500 hover:underline">Politique de confidentialité</a> d'ImmoGo.
                    </label>
                </div>
                @error('terms')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                <button type="submit" class="btn-primary w-full">
                    S'inscrire
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Déjà un compte ? <a href="{{ route('login') }}" class="text-cyan-500 font-medium hover:underline">Connectez-vous</a>
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
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
