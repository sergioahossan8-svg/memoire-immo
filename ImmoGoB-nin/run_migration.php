<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    // Vérifier si les colonnes existent déjà
    if (!Schema::hasColumn('biens', 'avance_mois')) {
        Schema::table('biens', function (Blueprint $table) {
            $table->unsignedTinyInteger('avance_mois')->default(1)->after('transaction');
            $table->decimal('caution_eau', 10, 2)->nullable()->after('avance_mois');
            $table->decimal('caution_electricite', 10, 2)->nullable()->after('caution_eau');
        });
        echo "✓ Colonnes ajoutées : avance_mois, caution_eau, caution_electricite\n";
    } else {
        echo "ℹ Les colonnes existent déjà.\n";
    }

    // Vérifier le résultat
    $cols = DB::select("SHOW COLUMNS FROM biens LIKE '%caution%'");
    echo "Colonnes caution : " . count($cols) . "\n";
    foreach ($cols as $c) {
        echo "  - {$c->Field} ({$c->Type})\n";
    }
    $avance = DB::select("SHOW COLUMNS FROM biens LIKE 'avance_mois'");
    if ($avance) echo "  - avance_mois ({$avance[0]->Type})\n";

} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}
