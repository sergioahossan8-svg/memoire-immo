<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - ImmoGo</title>
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
            <div class="text-center mb-6">
                <div class="w-14 h-14 bg-cyan-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-key text-cyan-500 text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Nouveau mot de passe</h1>
                <p class="text-sm text-gray-500">Choisissez un mot de passe sécurisé d'au moins 8 caractères.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- Email (affiché en lecture seule) --}}
                <div>
                    <label class="form-label">Adresse e-mail</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="email" value="{{ $email }}"
                            class="form-input pl-10 bg-gray-50 text-gray-500 cursor-not-allowed"
                            readonly>
                    </div>
                </div>

                {{-- Nouveau mot de passe --}}
                <div>
                    <label class="form-label">Nouveau mot de passe</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="password" id="password"
                            placeholder="••••••••"
                            class="form-input pl-10 pr-10 @error('password') border-red-400 @enderror"
                            required autofocus>
                        <button type="button" onclick="togglePassword('password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmation --}}
                <div>
                    <label class="form-label">Confirmer le mot de passe</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            placeholder="••••••••"
                            class="form-input pl-10 pr-10"
                            required>
                        <button type="button" onclick="togglePassword('password_confirmation')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                @error('email')
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-start gap-2">
                        <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <button type="submit" class="btn-primary w-full">
                    <i class="fas fa-check"></i> Réinitialiser le mot de passe
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                <a href="{{ route('login') }}" class="text-cyan-500 font-medium hover:underline">
                    <i class="fas fa-arrow-left text-xs mr-1"></i> Retour à la connexion
                </a>
            </p>
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
