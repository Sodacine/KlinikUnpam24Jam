<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan penghapusan antrian "dilewati" yang sudah kadaluarsa (>24 jam) setiap jam
Schedule::command('queue:expire-skipped')->hourly()->description('Hapus antrian dilewati yang sudah >24 jam');
