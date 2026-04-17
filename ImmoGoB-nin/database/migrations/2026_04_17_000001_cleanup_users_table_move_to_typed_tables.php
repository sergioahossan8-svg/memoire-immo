<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration de nettoyage : supprime de la table `users` les colonnes
 * qui ont été déplacées dans les tables spécialisées selon le diagramme de classe :
 *
 *  - avatar   → table `clients`  (colonne avatar)
 *  - adresse  → table `clients`  (déjà présent via create_class_table_inheritance)
 *  - ville    → table `clients`  (déjà présent via create_class_table_inheritance)
 *  - whatsapp → table `admin_agences` et `super_admins` (déjà présents)
 *  - agence_id    → table `admin_agences` (déjà présent)
 *  - est_principal → table `admin_agences` (déjà présent)
 *
 * On ajoute aussi `avatar` dans la table `clients` (manquant dans la migration initiale).
 * On s'assure que `whatsapp` est bien dans `admin_agences`.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter avatar dans clients (si manquant)
        if (!Schema::hasColumn('clients', 'avatar')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('ville');
            });
        }

        // 2. Ajouter whatsapp dans admin_agences (si manquant)
        if (!Schema::hasColumn('admin_agences', 'whatsapp')) {
            Schema::table('admin_agences', function (Blueprint $table) {
                $table->string('whatsapp')->nullable()->after('est_principal');
            });
        }

        // 3. Migrer avatar de users → clients
        \Illuminate\Support\Facades\DB::statement('
            UPDATE clients c
            JOIN users u ON u.id = c.user_id
            SET c.avatar = u.avatar
            WHERE u.avatar IS NOT NULL AND c.avatar IS NULL
        ');

        // 4. Migrer whatsapp de users → admin_agences
        \Illuminate\Support\Facades\DB::statement('
            UPDATE admin_agences a
            JOIN users u ON u.id = a.user_id
            SET a.whatsapp = u.whatsapp
            WHERE u.whatsapp IS NOT NULL AND a.whatsapp IS NULL
        ');

        // 5. Supprimer les colonnes de `users` qui sont maintenant dans les tables spécialisées
        Schema::table('users', function (Blueprint $table) {
            // Supprimer la FK agence_id avant de dropper
            if (Schema::hasColumn('users', 'agence_id')) {
                // Essayer de drop la foreign key (ignore si inexistante)
                try {
                    $table->dropForeign(['agence_id']);
                } catch (\Exception $e) {
                    // Ignoré
                }
                $table->dropColumn('agence_id');
            }
            if (Schema::hasColumn('users', 'est_principal')) {
                $table->dropColumn('est_principal');
            }
            if (Schema::hasColumn('users', 'whatsapp')) {
                $table->dropColumn('whatsapp');
            }
            if (Schema::hasColumn('users', 'adresse')) {
                $table->dropColumn('adresse');
            }
            if (Schema::hasColumn('users', 'ville')) {
                $table->dropColumn('ville');
            }
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
        });
    }

    public function down(): void
    {
        // Remettre les colonnes dans users (rollback)
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('whatsapp')->nullable();
            $table->foreignId('agence_id')->nullable()->constrained('agences')->nullOnDelete();
            $table->boolean('est_principal')->default(false);
        });
    }
};
