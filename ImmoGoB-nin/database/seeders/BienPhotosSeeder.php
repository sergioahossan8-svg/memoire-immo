<?php

namespace Database\Seeders;

use App\Models\Bien;
use App\Models\BienPhoto;
use Illuminate\Database\Seeder;

class BienPhotosSeeder extends Seeder
{
    public function run(): void
    {
        // Photos pour chaque bien (utilisant des URLs Unsplash pour les tests)
        $photosParBien = [
            // Appartement moderne 3 pièces à Akpakpa
            2 => [
                'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800',
                'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
            ],
            // Villa de luxe 5 pièces à Fidjrossè
            3 => [
                'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800',
                'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800',
            ],
            // Studio meublé à Cadjèhoun
            4 => [
                'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800',
                'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?w=800',
            ],
            // Maison familiale 4 pièces à Calavi
            5 => [
                'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800',
                'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800',
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
            ],
            // Appartement 2 pièces à Haie Vive
            6 => [
                'https://images.unsplash.com/photo-1502672023488-70e25813eb80?w=800',
                'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800',
            ],
        ];

        foreach ($photosParBien as $bienId => $photos) {
            $bien = Bien::find($bienId);
            if (!$bien) continue;

            // Supprimer les anciennes photos
            BienPhoto::where('bien_id', $bienId)->delete();

            // Ajouter les nouvelles photos
            foreach ($photos as $index => $photoUrl) {
                BienPhoto::create([
                    'bien_id' => $bienId,
                    'chemin' => $photoUrl,
                    'is_principale' => $index === 0, // La première est principale
                ]);
            }
        }

        $totalPhotos = BienPhoto::count();
        $this->command->info("✅ {$totalPhotos} photos ajoutées aux biens !");
    }
}
