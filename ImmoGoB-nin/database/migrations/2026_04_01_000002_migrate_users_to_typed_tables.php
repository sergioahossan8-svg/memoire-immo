<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrer les clients
        $clients = DB::table('users')->where('role', 'client')->get();
        foreach ($clients as $user) {
            DB::table('clients')->insertOrIgnore([
                'user_id'    => $user->id,
                'adresse'    => $user->adresse ?? null,
                'ville'      => $user->ville ?? null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        // Migrer les admins d'agence
        $admins = DB::table('users')->where('role', 'admin_agence')->get();
        foreach ($admins as $user) {
            if ($user->agence_id) {
                DB::table('admin_agences')->insertOrIgnore([
                    'user_id'       => $user->id,
                    'agence_id'     => $user->agence_id,
                    'est_principal' => $user->est_principal ?? false,
                    'created_at'    => $user->created_at,
                    'updated_at'    => $user->updated_at,
                ]);
            }
        }

        // Migrer les super admins
        $superAdmins = DB::table('users')->where('role', 'super_admin')->get();
        foreach ($superAdmins as $user) {
            DB::table('super_admins')->insertOrIgnore([
                'user_id'    => $user->id,
                'whatsapp'   => $user->whatsapp ?? null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('clients')->truncate();
        DB::table('admin_agences')->truncate();
        DB::table('super_admins')->truncate();
    }
};
