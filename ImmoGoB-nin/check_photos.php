<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNOSTIC IMAGES ===\n";
echo "Biens: " . App\Models\Bien::count() . "\n";
echo "BienPhotos: " . App\Models\BienPhoto::count() . "\n";
echo "APP_URL: " . config('app.url') . "\n\n";

$bien = App\Models\Bien::with(['photos', 'photoPrincipale'])->first();
if ($bien) {
    echo "Premier bien: " . $bien->titre . "\n";
    echo "Photos associées: " . $bien->photos->count() . "\n";
    
    $photo = $bien->photoPrincipale ?? $bien->photos->first();
    if ($photo) {
        echo "Chemin photo: " . $photo->chemin . "\n";
        echo "Storage::url(): " . Illuminate\Support\Facades\Storage::url($photo->chemin) . "\n";
        echo "asset(Storage::url()): " . asset(Illuminate\Support\Facades\Storage::url($photo->chemin)) . "\n";
        $physicalPath = storage_path('app/public/' . $photo->chemin);
        echo "Fichier physique: " . $physicalPath . "\n";
        echo "Fichier existe: " . (file_exists($physicalPath) ? 'OUI' : 'NON') . "\n";
    } else {
        echo "Aucune photo associée en base!\n";
    }
} else {
    echo "Aucun bien trouvé!\n";
}

echo "\n=== FICHIERS PHYSIQUES ===\n";
$dir = storage_path('app/public/biens');
if (is_dir($dir)) {
    $files = scandir($dir);
    echo count($files) - 2 . " fichiers dans storage/app/public/biens\n";
    foreach (array_slice($files, 2, 3) as $f) {
        echo "  - " . $f . "\n";
    }
}

echo "\n=== SYMLINK ===\n";
$symlink = public_path('storage');
echo "public/storage existe: " . (file_exists($symlink) ? 'OUI' : 'NON') . "\n";
echo "public/storage est un lien: " . (is_link($symlink) ? 'OUI' : 'NON') . "\n";
if (is_link($symlink)) {
    echo "Cible du lien: " . readlink($symlink) . "\n";
}
