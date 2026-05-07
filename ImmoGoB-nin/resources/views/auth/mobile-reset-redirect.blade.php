<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation - ImmoGo</title>
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

        <div class="card p-8 text-center">
            <div class="w-16 h-16 bg-cyan-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-key text-cyan-500 text-2xl"></i>
            </div>

            <h1 class="text-xl font-bold text-gray-800 mb-2">Réinitialiser votre mot de passe</h1>

            {{-- Message affiché pendant la tentative de deep link --}}
            <div id="msg-mobile" class="text-sm text-gray-500 mb-6">
                <p>Ouverture de l'application ImmoGo...</p>
                <div class="mt-3 flex justify-center">
                    <div class="w-5 h-5 border-2 border-cyan-400 border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>

            {{-- Fallback affiché si l'app ne s'ouvre pas (PC ou app non installée) --}}
            <div id="msg-fallback" class="hidden">
                <p class="text-sm text-gray-500 mb-6">
                    L'application ImmoGo n'a pas pu s'ouvrir.<br>
                    Vous pouvez réinitialiser votre mot de passe directement ici.
                </p>
                <a href="{{ route('password.reset', $token) }}?email={{ urlencode($email) }}"
                   class="btn-primary w-full inline-block text-center">
                    <i class="fas fa-lock mr-2"></i> Réinitialiser sur le web
                </a>
            </div>

            {{-- Bouton toujours visible pour ouvrir l'app manuellement --}}
            <div id="btn-app" class="hidden mt-4">
                <button onclick="openApp()" class="btn-secondary w-full">
                    <i class="fas fa-mobile-alt mr-2"></i> Ouvrir dans l'application
                </button>
            </div>

            <p class="text-xs text-gray-400 mt-6">
                <i class="fas fa-clock mr-1"></i> Ce lien expire dans 60 minutes.
            </p>
        </div>
    </div>

    <script>
        const TOKEN = "{{ $token }}";
        const EMAIL = "{{ urlencode($email) }}";
        const deepLink = `immogo://reset-password?token=${TOKEN}&email=${EMAIL}`;

        function openApp() {
            window.location.href = deepLink;
        }

        // Tenter d'ouvrir le deep link automatiquement
        function tryDeepLink() {
            const start = Date.now();
            window.location.href = deepLink;

            // Après 2.5s, si on est toujours là → l'app n'est pas installée → afficher fallback
            setTimeout(function () {
                const elapsed = Date.now() - start;
                // Si la page est toujours active (pas redirigée vers l'app)
                if (elapsed < 4000) {
                    document.getElementById('msg-mobile').classList.add('hidden');
                    document.getElementById('msg-fallback').classList.remove('hidden');
                    document.getElementById('btn-app').classList.remove('hidden');
                }
            }, 2500);
        }

        // Lancer après un court délai pour que la page soit bien chargée
        setTimeout(tryDeepLink, 500);
    </script>
</body>
</html>
