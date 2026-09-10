<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Data minimum agar aplikasi siap dipakai: role & permission, akun pengurus,
     * dan master data. Konten contoh dipisah ke DemoContentSeeder supaya tidak
     * ikut terbawa saat seeding di production.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            MasterDataSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoContentSeeder::class);
        }
    }
}
