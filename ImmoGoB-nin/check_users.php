<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== UTILISATEURS DANS LA BASE DE DONNÉES ===\n\n";

$users = App\Models\User::all(['id', 'name', 'prenom', 'email', 'role']);

if ($users->isEmpty()) {
    echo "Aucun utilisateur trouvé.\n";
} else {
    echo "Total: " . $users->count() . " utilisateur(s)\n\n";
    echo str_pad("ID", 5) . " | " . str_pad("Prénom", 15) . " | " . str_pad("Nom", 15) . " | " . str_pad("Email", 35) . " | " . str_pad("Rôle", 15) . "\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($users as $user) {
        echo str_pad($user->id, 5) . " | " 
           . str_pad($user->prenom, 15) . " | " 
           . str_pad($user->name, 15) . " | " 
           . str_pad($user->email, 35) . " | " 
           . str_pad($user->role, 15) . "\n";
    }
}

echo "\n";
