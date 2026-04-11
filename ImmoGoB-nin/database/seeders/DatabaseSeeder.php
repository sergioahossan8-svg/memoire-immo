<?php

namespace Database\Seeders;

use App\Models\TypeBien;
use App\Models\User;
use App\Models\SuperAdmin as SuperAdminModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les rôles Spatie
        $roles = ['super_admin', 'admin_agence', 'client'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Créer le SuperAdmin
        $superAdmin = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'hessoueulogegracien@gmail.com'],
            [
                'name'      => 'ImmoGo',
                'prenom'    => 'Super Admin',
                'email'     => 'hessoueulogegracien@gmail.com',
                'telephone' => '+22901000000',
                'whatsapp'  => '+22901000000',
                'role'      => 'super_admin',
                'password'  => Hash::make('Euloge55'),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Types de biens
        $types = ['Appartement', 'Maison', 'Villa', 'Parcelle', 'Loft', 'Studio', 'Duplex', 'Bureau'];
        foreach ($types as $type) {
            TypeBien::firstOrCreate(['libelle' => $type]);
        }
    }
}
