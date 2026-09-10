<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Akun awal sesuai PRD bagian 8.1.
 *
 * Password di luar environment lokal/testing dibuat acak dan hanya ditampilkan
 * sekali di output seeder, supaya tidak ada kredensial hardcoded yang terbawa
 * ke production.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        /** @var list<array{name: string, email: string, role: UserRole}> $accounts */
        $accounts = [
            ['name' => 'Iqbal Fahrozi', 'email' => 'superadmin@masjidannur.test', 'role' => UserRole::Superadmin],
            ['name' => 'Admin Masjid', 'email' => 'admin@masjidannur.test', 'role' => UserRole::Admin],
            ['name' => 'Ahmad Fauzi', 'email' => 'ketua@masjidannur.test', 'role' => UserRole::KetuaDkm],
            ['name' => 'Budi Santoso', 'email' => 'sekretaris@masjidannur.test', 'role' => UserRole::Sekretaris],
            ['name' => 'Citra Dewi', 'email' => 'bendahara@masjidannur.test', 'role' => UserRole::Bendahara],
        ];

        $isLocal = app()->environment(['local', 'testing']);
        $generated = [];

        foreach ($accounts as $account) {
            $password = $isLocal ? 'password' : Str::password(16);

            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            $user->syncRoles($account['role']->value);

            if ($user->wasRecentlyCreated && ! $isLocal) {
                $generated[$account['email']] = $password;
            }
        }

        foreach ($generated as $email => $password) {
            $this->command?->warn("Password untuk {$email}: {$password} (catat sekarang, tidak akan ditampilkan lagi)");
        }

        if ($isLocal) {
            $this->command?->info('Akun dummy dibuat dengan password: password (khusus environment lokal).');
        }
    }
}
