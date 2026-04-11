<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Bien;
use App\Models\TypeBien;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BiensTestSeeder extends Seeder
{
    public function run(): void
    {
        // Créer une agence de test
        $agence = Agence::firstOrCreate(
            ['email' => 'contact@immogo-cotonou.bj'],
            [
                'nom_commercial' => 'ImmoGo Agence Cotonou',
                'ville' => 'Cotonou',
                'secteur' => 'Résidentiel',
                'adresse_complete' => 'Akpakpa, Rue des Palmiers',
                'telephone' => '+22901234567',
                'email' => 'contact@immogo-cotonou.bj',
                'statut' => 'actif',
            ]
        );

        // Créer un admin d'agence
        $admin = User::firstOrCreate(
            ['email' => 'admin@immogo.bj'],
            [
                'name' => 'Admin',
                'prenom' => 'Agence',
                'email' => 'admin@immogo.bj',
                'telephone' => '+22901234567',
                'role' => 'admin_agence',
                'agence_id' => $agence->id,
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole('admin_agence');

        // Récupérer les types de biens
        $appartement = TypeBien::where('libelle', 'Appartement')->first();
        $maison = TypeBien::where('libelle', 'Maison')->first();
        $villa = TypeBien::where('libelle', 'Villa')->first();
        $studio = TypeBien::where('libelle', 'Studio')->first();

        // Créer des biens de test
        $biens = [
            [
                'agence_id' => $agence->id,
                'type_bien_id' => $appartement?->id ?? 1,
                'titre' => 'Appartement moderne 3 pièces à Akpakpa',
                'description' => 'Magnifique appartement de 3 pièces situé dans un quartier calme d\'Akpakpa. Proche des commodités (écoles, marchés, transports). Cuisine équipée, salle de bain moderne, balcon avec vue dégagée.',
                'prix' => 150000,
                'superficie' => 85,
                'localisation' => 'Akpakpa, Rue des Palmiers',
                'ville' => 'Cotonou',
                'chambres' => 2,
                'salles_bain' => 1,
                'transaction' => 'location',
                'statut' => 'disponible',
                'is_premium' => true,
                'is_published' => true,
            ],
            [
                'agence_id' => $agence->id,
                'type_bien_id' => $villa?->id ?? 3,
                'titre' => 'Villa de luxe 5 pièces à Fidjrossè',
                'description' => 'Superbe villa de standing avec piscine, jardin arboré et garage 2 voitures. Quartier résidentiel sécurisé, proche de la plage. Finitions haut de gamme.',
                'prix' => 85000000,
                'superficie' => 350,
                'localisation' => 'Fidjrossè, Cité Houéyiho',
                'ville' => 'Cotonou',
                'chambres' => 4,
                'salles_bain' => 3,
                'transaction' => 'vente',
                'statut' => 'disponible',
                'is_premium' => true,
                'is_published' => true,
            ],
            [
                'agence_id' => $agence->id,
                'type_bien_id' => $studio?->id ?? 6,
                'titre' => 'Studio meublé à Cadjèhoun',
                'description' => 'Studio tout équipé, idéal pour étudiant ou jeune professionnel. Cuisine américaine, salle d\'eau, climatisation. Charges comprises.',
                'prix' => 80000,
                'superficie' => 25,
                'localisation' => 'Cadjèhoun, près de l\'aéroport',
                'ville' => 'Cotonou',
                'chambres' => 1,
                'salles_bain' => 1,
                'transaction' => 'location',
                'statut' => 'disponible',
                'is_premium' => false,
                'is_published' => true,
            ],
            [
                'agence_id' => $agence->id,
                'type_bien_id' => $maison?->id ?? 2,
                'titre' => 'Maison familiale 4 pièces à Calavi',
                'description' => 'Belle maison familiale avec cour spacieuse. Quartier calme et sécurisé. Proche des écoles et du marché. Idéal pour une famille.',
                'prix' => 35000000,
                'superficie' => 180,
                'localisation' => 'Calavi, Godomey',
                'ville' => 'Calavi',
                'chambres' => 3,
                'salles_bain' => 2,
                'transaction' => 'vente',
                'statut' => 'disponible',
                'is_premium' => false,
                'is_published' => true,
            ],
            [
                'agence_id' => $agence->id,
                'type_bien_id' => $appartement?->id ?? 1,
                'titre' => 'Appartement 2 pièces à Haie Vive',
                'description' => 'Appartement lumineux au 2ème étage. Salon spacieux, cuisine séparée, balcon. Parking disponible. Proche du centre-ville.',
                'prix' => 120000,
                'superficie' => 65,
                'localisation' => 'Haie Vive, Avenue Steinmetz',
                'ville' => 'Cotonou',
                'chambres' => 1,
                'salles_bain' => 1,
                'transaction' => 'location',
                'statut' => 'disponible',
                'is_premium' => false,
                'is_published' => true,
            ],
        ];

        foreach ($biens as $bienData) {
            Bien::firstOrCreate(
                ['titre' => $bienData['titre']],
                $bienData
            );
        }

        $this->command->info('✅ ' . count($biens) . ' biens de test créés avec succès !');
    }
}
