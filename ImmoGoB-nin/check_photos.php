<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PHOTOS DES BIENS ===\n\n";

$photos = DB::table('bien_photos')->get();

echo "Total photos: " . $photos->count() . "\n\n";

if ($photos->isEmpty()) {
    echo "Aucune photo trouvée.\n";
} else {
    foreach ($photos as $photo) {
        echo "ID: {$photo->id}\n";
        echo "Bien ID: {$photo->bien_id}\n";
        echo "Principale: " . ($photo->is_principale ? 'Oui' : 'Non') . "\n";
        echo "Chemin: {$photo->chemin}\n";
        echo str_repeat("-", 80) . "\n";
    }
}

echo "\n=== BIENS AVEC LEURS PHOTOS ===\n\n";

$biens = App\Models\Bien::with('photos')->where('is_published', true)->get();

foreach ($biens as $bien) {
    echo "Bien #{$bien->id}: {$bien->titre}\n";
    echo "  Photos: " . $bien->photos->count() . "\n";
    if ($bien->photos->isNotEmpty()) {
        foreach ($bien->photos as $photo) {
            echo "    - " . ($photo->is_principale ? '[PRINCIPALE] ' : '') . $photo->chemin . "\n";
        }
    }
    echo "\n";
}
