<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            if (Schema::hasColumn('paiements', 'fedapay_transaction_id')) {
                $table->renameColumn('fedapay_transaction_id', 'kkiapay_transaction_id');
            }
            if (Schema::hasColumn('paiements', 'fedapay_token')) {
                $table->dropColumn('fedapay_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            if (Schema::hasColumn('paiements', 'kkiapay_transaction_id')) {
                $table->renameColumn('kkiapay_transaction_id', 'fedapay_transaction_id');
            }
            if (!Schema::hasColumn('paiements', 'fedapay_token')) {
                $table->string('fedapay_token')->nullable()->after('fedapay_transaction_id');
            }
        });
    }
};
