<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->string('banque_nom')->nullable()->after('telephone');
            $table->string('banque_titulaire')->nullable()->after('banque_nom');
            $table->string('banque_iban')->nullable()->after('banque_titulaire');
            $table->string('banque_swift')->nullable()->after('banque_iban');
        });
    }

    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->dropColumn(['banque_nom', 'banque_titulaire', 'banque_iban', 'banque_swift']);
        });
    }
};
