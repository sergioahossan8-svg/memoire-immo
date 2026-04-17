<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            // Utilisation de KKiapay (remplace FedaPay)
            if (!Schema::hasColumn('paiements', 'kkiapay_transaction_id')) {
                $table->string('kkiapay_transaction_id')->nullable()->after('reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            if (Schema::hasColumn('paiements', 'kkiapay_transaction_id')) {
                $table->dropColumn('kkiapay_transaction_id');
            }
        });
    }
};
