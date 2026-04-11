<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BienApiController;
use App\Http\Controllers\Api\FavoriApiController;
use App\Http\Controllers\Api\ContratApiController;
use App\Http\Controllers\Api\PaiementApiController;
use App\Http\Controllers\Api\ProfilApiController;
use App\Http\Controllers\Api\EstimationApiController;
use App\Http\Controllers\Api\NotificationApiController;
use Illuminate\Support\Facades\Route;

// ── Auth (public) ──────────────────────────────────────────────────────────
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// ── Biens (public) ──────────────────────────────────────────────────────────
Route::get('/biens', [BienApiController::class, 'index']);
Route::get('/biens/{bien}', [BienApiController::class, 'show']);
Route::get('/types-biens', [BienApiController::class, 'types']);
Route::get('/villes', [BienApiController::class, 'villes']);

// ── Estimation (public) ─────────────────────────────────────────────────────
Route::post('/estimer', [EstimationApiController::class, 'estimer']);

// ── Authenticated (client) ──────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'api.role:client'])->group(function () {

    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);

    // Profil
    Route::get('/profil', [ProfilApiController::class, 'index']);
    Route::post('/profil', [ProfilApiController::class, 'update']);

    // Favoris
    Route::get('/favoris', [FavoriApiController::class, 'index']);
    Route::post('/favoris/{bien}', [FavoriApiController::class, 'toggle']);

    // Contrats / Historique
    Route::get('/historique', [ContratApiController::class, 'historique']);
    Route::get('/contrats/{contrat}', [ContratApiController::class, 'show']);

    // Réservation
    Route::post('/biens/{bien}/reserver', [ContratApiController::class, 'reserver']);

    // Paiements KKiapay
    // 1. Initier le paiement → retourne la clé publique KKiapay + données
    Route::post('/biens/{bien}/payer-complet', [PaiementApiController::class, 'payerComplet']);
    Route::post('/contrats/{contrat}/payer-solde', [PaiementApiController::class, 'payerSolde']);
    Route::post('/biens/{bien}/init-reservation', [PaiementApiController::class, 'initReservation']);

    // 2. Confirmer après succès du widget KKiapay (mobile envoie transaction_id)
    Route::post('/paiement/confirmer', [PaiementApiController::class, 'confirmerKkiapay']);

    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/lire', [NotificationApiController::class, 'marquerLues']);
});

// ── KKiapay callback webhook (sans auth) ───────────────────────────────────
Route::post('/paiement/callback', [PaiementApiController::class, 'callback']);
