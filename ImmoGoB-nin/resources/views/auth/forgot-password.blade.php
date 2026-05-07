<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - ImmoGo</title>
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
                    <i class="fas fa-lock text-cyan-500 text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Mot de passe oublié ?</h1>
                <p class="text-sm text-gray-500">Saisissez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
            </div>

            {{-- Message de succès --}}
            @if(session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-start gap-2">
                    <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="form-label">Adresse e-mail</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="nom@exemple.com"
                            class="form-input pl-10 @error('email') border-red-400 @enderror"
                            required autofocus>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full">
                    <i class="fas fa-paper-plane"></i> Envoyer le lien
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                <a href="{{ route('login') }}" class="text-cyan-500 font-medium hover:underline">
                    <i class="fas fa-arrow-left text-xs mr-1"></i> Retour à la connexion
                </a>
            </p>
        </div>
    </div>

</body>
</html>
