<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Client\BienController as ClientBienController;
use App\Http\Controllers\Client\FavoriController;
use App\Http\Controllers\Client\ContratController;
use App\Http\Controllers\Client\PaiementController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\BienController as AdminBienController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\AdministrateurController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\AgenceController;
use App\Http\Controllers\SuperAdmin\UtilisateurController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ClientBienController::class, 'index'])->name('home');
Route::get('/biens', [ClientBienController::class, 'liste'])->name('biens.liste');
Route::get('/biens/{bien}', [ClientBienController::class, 'show'])->name('biens.show');
Route::post('/changer-lieu', [ClientBienController::class, 'changerLieu'])->name('changer.lieu');

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Espace Client (authentifié) ─────────────────────────────────────────────
Route::middleware(['auth', 'role.check:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/favoris', [FavoriController::class, 'index'])->name('favoris');
    Route::post('/favoris/{bien}', [FavoriController::class, 'toggle'])->name('favoris.toggle');
    Route::get('/historique', [ContratController::class, 'historique'])->name('historique');
    Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
    Route::get('/profil', [ProfileController::class, 'index'])->name('profil');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // Réservation & Paiement
    Route::get('/biens/{bien}/reserver', [ContratController::class, 'showReservation'])->name('reserver');
    Route::post('/biens/{bien}/reserver', [ContratController::class, 'reserver'])->name('reserver.post');
    Route::get('/biens/{bien}/payer-reservation', [PaiementController::class, 'initReservation'])->name('payer.reservation');
    Route::get('/contrats/{contrat}/payer-solde', [PaiementController::class, 'showSolde'])->name('payer.solde');
    Route::post('/contrats/{contrat}/payer-solde', [PaiementController::class, 'payerSolde'])->name('payer.solde.post');
    Route::get('/biens/{bien}/payer-complet', [PaiementController::class, 'showComplet'])->name('payer.complet');
    Route::post('/biens/{bien}/payer-complet', [PaiementController::class, 'payerComplet'])->name('payer.complet.post');
});

// Favoris toggle accessible aussi aux visiteurs (redirige vers login si non connecté)
Route::post('/favoris/{bien}/toggle', [FavoriController::class, 'toggle'])->name('favoris.toggle.guest');

// ── KKiapay — page de paiement et confirmation ────────────────────────────────
Route::get('/paiement/kkiapay', [PaiementController::class, 'showKkiapay'])->name('paiement.kkiapay')->middleware('auth');
Route::post('/paiement/kkiapay/confirmer', [PaiementController::class, 'confirmerKkiapay'])->name('paiement.kkiapay.confirmer')->middleware('auth');
Route::post('/paiement/callback', [PaiementController::class, 'callback'])->name('paiement.callback');
Route::get('/paiement/retour', [PaiementController::class, 'retour'])->name('paiement.retour');

// ─── Espace Admin Agence ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role.check:admin_agence'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Biens — export PDF AVANT le resource pour éviter le conflit de route
    Route::get('/biens/export-pdf', [\App\Http\Controllers\Admin\ExportController::class, 'biensPdf'])->name('biens.export-pdf');
    Route::resource('biens', AdminBienController::class);
    Route::patch('/biens/{bien}/statut', [AdminBienController::class, 'updateStatut'])->name('biens.statut');
    Route::patch('/biens/{bien}/publier', [AdminBienController::class, 'publier'])->name('biens.publier');

    // Clients & Contrats
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::get('/clients/{user}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/reservations', [ClientController::class, 'reservations'])->name('reservations');

    // Administrateurs
    Route::resource('administrateurs', AdministrateurController::class)->except(['show']);
    Route::patch('/mot-de-passe', [AdministrateurController::class, 'updatePassword'])->name('password.update');

    // Paramètres agence (logo + nom) — admin principal uniquement
    Route::get('/agence/parametres', [\App\Http\Controllers\Admin\AgenceParametresController::class, 'index'])->name('agence.parametres');
    Route::post('/agence/parametres', [\App\Http\Controllers\Admin\AgenceParametresController::class, 'update'])->name('agence.parametres.update');

    // Sécurité & Logs
    Route::get('/securite', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('securite');
});

// ─── Espace Super Admin ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role.check:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/profil', [\App\Http\Controllers\SuperAdmin\ProfilController::class, 'index'])->name('profil');
    Route::put('/profil', [\App\Http\Controllers\SuperAdmin\ProfilController::class, 'update'])->name('profil.update');

    // Agences — export PDF AVANT le resource
    Route::get('/agences/export-pdf', [\App\Http\Controllers\SuperAdmin\ExportController::class, 'agencesPdf'])->name('agences.export-pdf');
    Route::resource('agences', AgenceController::class);
    Route::patch('/agences/{agence}/statut', [AgenceController::class, 'updateStatut'])->name('agences.statut');

    // Utilisateurs
    Route::get('/utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs');

    // Sécurité & Logs
    Route::get('/securite', [\App\Http\Controllers\SuperAdmin\SecurityController::class, 'index'])->name('securite');
});
