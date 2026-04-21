<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expiration automatique des réservations (délai : 15 jours)
// Vérification toutes les heures suffit largement pour un délai de 15 jours
Schedule::command('reservations:expirer')->hourly();
