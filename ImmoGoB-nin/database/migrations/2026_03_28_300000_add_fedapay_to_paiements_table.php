<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->string('fedapay_transaction_id')->nullable()->after('reference');
            $table->string('fedapay_token')->nullable()->after('fedapay_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropColumn(['fedapay_transaction_id', 'fedapay_token']);
        });
    }
};
