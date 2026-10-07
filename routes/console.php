<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Otomatisasi generate tagihan SPP bulanan setiap tanggal 1 jam 00:01
Illuminate\Support\Facades\Schedule::command('spp:generate-monthly')
    ->monthlyOn(1, '00:01')
    ->runInBackground();

