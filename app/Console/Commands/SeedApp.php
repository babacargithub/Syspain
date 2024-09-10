<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SeedApp extends Command
{
    protected $signature = 'app:seed';
    protected $description = 'Seed the Laravel app with dummy data';

    public function handle()
    {
        \Artisan::call('db:seed DevDatabaseSeeder');

    }



}
