<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('biens');
            $table->foreignId('client_id')->constrained('users');
            $table->enum('type_contrat', ['location', 'vente']);
            $table->enum('statut_contrat', ['en_attente', 'actif', 'termine', 'annule'])->default('en_attente');
            $table->date('date_contrat');
            // Pour location
            $table->decimal('montant_total_location', 15, 2)->nullable();
            $table->datetime('date_reserv_location')->nullable();
            $table->datetime('date_limite_solde_location')->nullable();
            // Pour vente
            $table->decimal('montant_total_vente', 15, 2)->nullable();
            $table->datetime('date_reserv_vente')->nullable();
            $table->datetime('date_limite_solde_vente')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
