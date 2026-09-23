<?php

use Illuminate\Foundation\Inspiring;
use App\Models\IdempotencyKey;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('model:prune', ['--model' => [IdempotencyKey::class]])->daily();
