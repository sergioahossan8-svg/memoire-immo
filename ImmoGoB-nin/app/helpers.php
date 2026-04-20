<?php

if (!function_exists('storage_url')) {
    /**
     * Génère une URL absolue vers un fichier storage.
     * Utilise l'hôte de la requête en cours (fonctionne pour localhost ET l'IP réseau).
     */
    function storage_url(string $path): string
    {
        $relativePath = \Illuminate\Support\Facades\Storage::url($path);
        // Utiliser l'URL de base de la requête actuelle (pas l'APP_URL fixe)
        $request = request();
        if ($request && $request->getHost() !== 'localhost' && $request->getHost() !== '127.0.0.1') {
            // Requête depuis l'IP réseau (mobile) → utiliser l'hôte de la requête
            $base = $request->getScheme() . '://' . $request->getHttpHost();
            $appUrlPath = parse_url(config('app.url'), PHP_URL_PATH) ?? '';
            return $base . $appUrlPath . $relativePath;
        }
        // Requête depuis localhost (navigateur web) → utiliser asset()
        return asset(ltrim($relativePath, '/'));
    }
}
