<?php

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Buat user dengan role tertentu, lengkap dengan permission sesuai matriks PRD.
 */
function userWithRole(UserRole $role, array $attributes = []): User
{
    seedRoles();

    $user = User::factory()->create($attributes);
    $user->syncRoles($role->value);

    return $user->fresh();
}

/**
 * Jalankan RoleSeeder sekali per test agar tabel role & permission terisi.
 */
function seedRoles(): void
{
    if (Role::query()->exists()) {
        return;
    }

    test()->seed(RoleSeeder::class);
}

/**
 * Master data (pengaturan masjid, kategori, fasilitas, FAQ) untuk test yang
 * menyentuh halaman publik.
 */
function seedMasterData(): void
{
    test()->seed(MasterDataSeeder::class);
}
