<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 必要に応じて他のSeederを呼び出す
        $this->call([
            UserSeeder::class,
        ]);
    }
}
