<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TOUS les biens publiés ===\n";
$biens = DB::select('SELECT id, titre, transaction, statut, is_published, agence_id FROM biens WHERE is_published = 1 ORDER BY id DESC');
foreach ($biens as $b) {
    $visible = ($b->statut === 'disponible') ? '✓ VISIBLE API' : '✗ CACHÉ (statut=' . $b->statut . ')';
    echo "ID:{$b->id} | trans={$b->transaction} | {$visible} | {$b->titre}\n";
}

echo "\n=== Biens NON publiés (is_published=0) ===\n";
$nonPub = DB::select('SELECT id, titre, transaction, statut FROM biens WHERE is_published = 0 ORDER BY id DESC');
foreach ($nonPub as $b) {
    echo "ID:{$b->id} | trans={$b->transaction} | statut={$b->statut} | {$b->titre}\n";
}

echo "\n=== Ce que l'API retourne exactement (publiés + disponibles) ===\n";
$api = DB::select('SELECT id, titre, transaction, statut FROM biens WHERE is_published = 1 AND statut = "disponible" ORDER BY id DESC');
echo "Total : " . count($api) . " biens\n";
foreach ($api as $b) {
    echo "  ✓ ID:{$b->id} | [{$b->transaction}] | {$b->titre}\n";
}

echo "\n=== Biens publiés mais INVISIBLES (statut != disponible) ===\n";
$pb = DB::select('SELECT id, titre, transaction, statut FROM biens WHERE is_published = 1 AND statut != "disponible"');
foreach ($pb as $b) {
    echo "  ✗ ID:{$b->id} | [{$b->transaction}] | statut={$b->statut} | {$b->titre}\n";
}
