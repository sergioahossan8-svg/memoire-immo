<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Biens avec transaction=vente ===\n";
$biens = DB::select('SELECT id, titre, transaction, statut, is_published FROM biens WHERE transaction = "vente"');
if (empty($biens)) {
    echo "Aucun bien avec transaction=vente en base.\n";
} else {
    foreach ($biens as $b) {
        echo "ID:{$b->id} | {$b->titre} | statut={$b->statut} | published={$b->is_published}\n";
    }
}

echo "\n=== Biens publiés disponibles (visibles dans l'API) ===\n";
$visibles = DB::select('SELECT id, titre, transaction, statut FROM biens WHERE is_published=1 AND statut="disponible"');
echo "Total visibles: " . count($visibles) . "\n";
foreach ($visibles as $b) {
    echo "  ID:{$b->id} | {$b->titre} | transaction={$b->transaction}\n";
}

echo "\n=== Tous les biens ===\n";
$tous = DB::select('SELECT id, titre, transaction, statut, is_published FROM biens ORDER BY id DESC LIMIT 20');
foreach ($tous as $b) {
    echo "ID:{$b->id} | {$b->titre} | trans={$b->transaction} | statut={$b->statut} | pub={$b->is_published}\n";
}
