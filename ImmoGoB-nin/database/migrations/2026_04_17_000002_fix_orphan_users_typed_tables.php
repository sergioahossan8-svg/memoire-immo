<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration de correction : crée les lignes manquantes dans les tables
 * spécialisées (clients, admin_agences, super_admins) pour les utilisateurs
 * existants qui n'ont pas de ligne dans leur table respective (orphelins CTI).
 */
return new class extends Migration
{
    public function up(): void
    {
        $users  = DB::table('users')->get();
        $agence = DB::table('agences')->first(); // première agence disponible

        foreach ($users as $u) {
            switch ($u->role) {
                case 'client':
                    $exists = DB::table('clients')->where('user_id', $u->id)->exists();
                    if (!$exists) {
                        DB::table('clients')->insert([
                            'user_id'    => $u->id,
                            'adresse'    => null,
                            'ville'      => null,
                            'avatar'     => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    break;

                case 'admin_agence':
                    $exists = DB::table('admin_agences')->where('user_id', $u->id)->exists();
                    if (!$exists && $agence) {
                        DB::table('admin_agences')->insert([
                            'user_id'       => $u->id,
                            'agence_id'     => $agence->id,
                            'est_principal' => false,
                            'whatsapp'      => null,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                    break;

                case 'super_admin':
                    $exists = DB::table('super_admins')->where('user_id', $u->id)->exists();
                    if (!$exists) {
                        DB::table('super_admins')->insert([
                            'user_id'    => $u->id,
                            'whatsapp'   => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    break;
            }
        }
    }

    public function down(): void
    {
        // Pas de rollback destructif — les données sont précieuses
    }
};
