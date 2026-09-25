<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * php artisan db:seed                      → catálogos + datos iniciales
     * php artisan db:seed --class=DemoSeeder   → además, 90 días de datos de ejemplo
     */
    public function run(): void
    {
        $this->call([CatalogosSeeder::class, BaseSeeder::class]);
    }
}
