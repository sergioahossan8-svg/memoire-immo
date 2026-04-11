<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            if (Schema::hasColumn('agences', 'fedapay_secret_key')) {
                $table->dropColumn('fedapay_secret_key');
            }
            if (Schema::hasColumn('agences', 'fedapay_env')) {
                $table->dropColumn('fedapay_env');
            }
            if (!Schema::hasColumn('agences', 'kkiapay_public_key')) {
                $table->string('kkiapay_public_key')->nullable()->after('logo');
                $table->string('kkiapay_private_key')->nullable()->after('kkiapay_public_key');
                $table->string('kkiapay_secret')->nullable()->after('kkiapay_private_key');
                $table->boolean('kkiapay_sandbox')->default(true)->after('kkiapay_secret');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->dropColumn(['kkiapay_public_key', 'kkiapay_private_key', 'kkiapay_secret', 'kkiapay_sandbox']);
        });
    }
};
