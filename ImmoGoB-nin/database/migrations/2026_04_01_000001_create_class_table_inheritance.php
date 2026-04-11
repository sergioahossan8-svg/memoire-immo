<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table clients
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->timestamps();
        });

        // Table admin_agences
        Schema::create('admin_agences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('agence_id')->constrained('agences')->cascadeOnDelete();
            $table->boolean('est_principal')->default(false);
            $table->timestamps();
        });

        // Table super_admins
        Schema::create('super_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('whatsapp')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admins');
        Schema::dropIfExists('admin_agences');
        Schema::dropIfExists('clients');
    }
};
