<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agence_id')->nullable()->constrained('agences')->nullOnDelete();
            $table->boolean('est_principal')->default(false); // admin principal de l'agence
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['agence_id']);
            $table->dropColumn(['agence_id', 'est_principal']);
        });
    }
};
