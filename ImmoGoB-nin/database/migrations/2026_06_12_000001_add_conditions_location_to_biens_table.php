<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biens', function (Blueprint $table) {
            // Nombre de mois d'avance exigé (obligatoire pour location)
            $table->unsignedTinyInteger('avance_mois')->default(1)->after('transaction');
            // Caution eau (optionnelle)
            $table->decimal('caution_eau', 10, 2)->nullable()->after('avance_mois');
            // Caution électricité (optionnelle)
            $table->decimal('caution_electricite', 10, 2)->nullable()->after('caution_eau');
        });
    }

    public function down(): void
    {
        Schema::table('biens', function (Blueprint $table) {
            $table->dropColumn(['avance_mois', 'caution_eau', 'caution_electricite']);
        });
    }
};
