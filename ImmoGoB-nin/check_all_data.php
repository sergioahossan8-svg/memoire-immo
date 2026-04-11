<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== STATISTIQUES BASE DE DONNÉES IMMOGO ===\n\n";

// Utilisateurs
$totalUsers = App\Models\User::count();
$clients = App\Models\User::where('role', 'client')->count();
$admins = App\Models\User::where('role', 'admin_agence')->count();
$superAdmins = App\Models\User::where('role', 'super_admin')->count();

echo "📊 UTILISATEURS\n";
echo "  Total: $totalUsers\n";
echo "  - Clients: $clients\n";
echo "  - Admins agence: $admins\n";
echo "  - Super admins: $superAdmins\n\n";

// Biens
$totalBiens = App\Models\Bien::count();
echo "🏠 BIENS: $totalBiens\n\n";

// Contrats
$totalContrats = App\Models\Contrat::count();
echo "📄 CONTRATS: $totalContrats\n\n";

// Favoris
$totalFavoris = App\Models\Favori::count();
echo "❤️  FAVORIS: $totalFavoris\n\n";

// Paiements
$totalPaiements = App\Models\Paiement::count();
echo "💰 PAIEMENTS: $totalPaiements\n\n";

// Liste des clients
echo "=== LISTE DES CLIENTS ===\n\n";
$clientsList = App\Models\User::where('role', 'client')->get(['id', 'name', 'prenom', 'email', 'telephone', 'ville', 'created_at']);

if ($clientsList->isEmpty()) {
    echo "Aucun client.\n";
} else {
    foreach ($clientsList as $client) {
        echo "ID: {$client->id}\n";
        echo "Nom: {$client->prenom} {$client->name}\n";
        echo "Email: {$client->email}\n";
        echo "Téléphone: " . ($client->telephone ?? 'N/A') . "\n";
        echo "Ville: " . ($client->ville ?? 'N/A') . "\n";
        echo "Inscrit le: {$client->created_at}\n";
        echo str_repeat("-", 50) . "\n";
    }
}

// Tokens actifs
$activeTokens = DB::table('personal_access_tokens')->count();
echo "\n🔑 TOKENS ACTIFS: $activeTokens\n";

if ($activeTokens > 0) {
    echo "\nDétails des tokens:\n";
    $tokens = DB::table('personal_access_tokens')
        ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
        ->select('users.email', 'personal_access_tokens.name', 'personal_access_tokens.created_at', 'personal_access_tokens.last_used_at')
        ->get();
    
    foreach ($tokens as $token) {
        echo "  - {$token->email} ({$token->name}) - Créé: {$token->created_at} - Dernière utilisation: " . ($token->last_used_at ?? 'Jamais') . "\n";
    }
}

echo "\n";
