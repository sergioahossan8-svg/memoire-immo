<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UpdateSuperAdmin extends Command
{
    protected $signature = 'superadmin:update';
    protected $description = 'Met à jour les infos du SuperAdmin';

    public function handle(): void
    {
        // Supprimer l'ancien superadmin si différent
        User::where('role', 'super_admin')
            ->where('email', '!=', 'hessoueulogegracien@gmail.com')
            ->delete();

        // Mettre à jour ou créer
        $user = User::where('email', 'hessoueulogegracien@gmail.com')->first();

        if ($user) {
            $user->update([
                'name'     => 'HESSOU',
                'prenom'   => 'Euloge Grâcien',
                'role'     => 'super_admin',
                'password' => Hash::make('Euloge55'),
            ]);
        } else {
            $user = User::create([
                'name'     => 'HESSOU',
                'prenom'   => 'Euloge Grâcien',
                'email'    => 'hessoueulogegracien@gmail.com',
                'role'     => 'super_admin',
                'password' => Hash::make('Euloge55'),
            ]);
        }

        $user->syncRoles(['super_admin']);

        $this->info('SuperAdmin mis à jour.');
        $this->line('Email    : hessoueulogegracien@gmail.com');
        $this->line('Password : Euloge55');
    }
}
