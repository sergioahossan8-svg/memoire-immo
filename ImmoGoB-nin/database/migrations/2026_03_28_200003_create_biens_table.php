<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            $table->foreignId('type_bien_id')->constrained('type_biens');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->decimal('prix', 15, 2);
            $table->float('superficie')->nullable();
            $table->string('localisation');
            $table->string('ville');
            $table->integer('chambres')->nullable();
            $table->integer('salles_bain')->nullable();
            $table->enum('transaction', ['location', 'vente'])->default('location');
            $table->enum('statut', ['disponible', 'reserve', 'vendu', 'loue', 'indisponible'])->default('disponible');
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biens');
    }
};
