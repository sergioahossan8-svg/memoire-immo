<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table) {
            $table->id();
            $table->string('nom_commercial');
            $table->enum('secteur', ['Résidentiel', 'Commercial', 'Industriel', 'Mixte'])->default('Résidentiel');
            $table->string('ville');
            $table->string('adresse_complete');
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('logo')->nullable();
            $table->enum('statut', ['actif', 'en_attente', 'suspendu'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
