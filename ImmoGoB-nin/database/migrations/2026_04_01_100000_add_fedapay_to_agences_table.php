<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->string('fedapay_secret_key')->nullable()->after('logo');
            $table->enum('fedapay_env', ['sandbox', 'live'])->default('sandbox')->after('fedapay_secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->dropColumn(['fedapay_secret_key', 'fedapay_env']);
        });
    }
};
