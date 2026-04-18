<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Trouver tous les users avec role=client sans entrée dans la table clients
        $orphans = DB::table('users')
            ->where('role', 'client')
            ->whereNotIn('id', DB::table('clients')->pluck('user_id'))
            ->get();

        foreach ($orphans as $user) {
            DB::table('clients')->insert([
                'user_id'    => $user->id,
                'ville'      => 'Non renseignée',
                'adresse'    => null,
                'avatar'     => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Ne pas supprimer en rollback : risque de perte de données
    }
};
