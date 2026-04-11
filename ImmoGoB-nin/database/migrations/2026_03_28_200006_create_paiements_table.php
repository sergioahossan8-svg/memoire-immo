<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrat_id')->constrained('contrats')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users');
            $table->decimal('montant', 15, 2);
            $table->datetime('date_paiement');
            $table->enum('type_paiement', ['acompte', 'solde', 'complet']); // acompte=10%, solde=reste, complet=100%
            $table->enum('mode_paiement', ['mobile_money', 'virement', 'especes', 'carte'])->default('mobile_money');
            $table->string('reference')->unique();
            $table->enum('statut', ['en_attente', 'confirme', 'echoue'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
