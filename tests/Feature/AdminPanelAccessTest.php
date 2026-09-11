<?php

use App\Enums\UserRole;
use App\Models\User;

it('menolak tamu yang belum login', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('menolak user tanpa role sama sekali', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

it('menolak user yang akunnya dinonaktifkan', function (): void {
    $user = userWithRole(UserRole::Admin, ['is_active' => false]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('mengizinkan superadmin membuka dashboard', function (): void {
    $this->actingAs(userWithRole(UserRole::Superadmin))
        ->get('/admin')
        ->assertSuccessful();
});

it('membuka semua halaman daftar resource untuk superadmin', function (string $path): void {
    $this->actingAs(userWithRole(UserRole::Superadmin))
        ->get($path)
        ->assertSuccessful();
})->with([
    '/admin/announcements',
    '/admin/studies',
    '/admin/events',
    '/admin/articles',
    '/admin/article-categories',
    '/admin/gallery-albums',
    '/admin/library-materials',
    '/admin/faqs',
    '/admin/finance-transactions',
    '/admin/finance-categories',
    '/admin/testimonials',
    '/admin/suggestions',
    '/admin/qurban-registrations',
    '/admin/zakat-registrations',
    '/admin/facilities',
    '/admin/facility-bookings',
    '/admin/board-members',
    '/admin/prayer-schedules',
    '/admin/users',
    '/admin/activity-logs',
    '/admin/pengaturan',
    '/admin/kalender',
]);

it('membuka daftar pengguna yang berisi banyak akun', function (): void {
    // Kolom Role dan aksi tiap baris membaca relasi roles. Lazy loading dilarang
    // di luar produksi, jadi relasi yang lupa di-eager load membuat halaman ini
    // error 500 — tapi hanya bila tabelnya berisi lebih dari satu baris, sehingga
    // test di atas yang hanya berisi akun superadmin tidak pernah menangkapnya.
    $superadmin = userWithRole(UserRole::Superadmin);
    userWithRole(UserRole::Admin, ['name' => 'Akun Admin Uji']);
    userWithRole(UserRole::Bendahara, ['name' => 'Akun Bendahara Uji']);

    $this->actingAs($superadmin)
        ->get('/admin/users')
        ->assertSuccessful()
        ->assertSee('Akun Admin Uji')
        ->assertSee('Akun Bendahara Uji');
});

it('menghalangi Bendahara membuka manajemen user', function (): void {
    $this->actingAs(userWithRole(UserRole::Bendahara))
        ->get('/admin/users')
        ->assertForbidden();
});

it('menghalangi Sekretaris membuka manajemen user', function (): void {
    $this->actingAs(userWithRole(UserRole::Sekretaris))
        ->get('/admin/users')
        ->assertForbidden();
});

it('mengizinkan Bendahara mengelola keuangan', function (): void {
    $bendahara = userWithRole(UserRole::Bendahara);

    $this->actingAs($bendahara)->get('/admin/finance-transactions')->assertSuccessful();
    $this->actingAs($bendahara)->get('/admin/finance-transactions/create')->assertSuccessful();
});

it('melarang Sekretaris membuat transaksi keuangan', function (): void {
    $this->actingAs(userWithRole(UserRole::Sekretaris))
        ->get('/admin/finance-transactions/create')
        ->assertForbidden();
});

it('melarang Ketua DKM membuat pengumuman baru', function (): void {
    $this->actingAs(userWithRole(UserRole::KetuaDkm))
        ->get('/admin/announcements/create')
        ->assertForbidden();
});

it('mengizinkan Sekretaris membuat pengumuman baru', function (): void {
    $this->actingAs(userWithRole(UserRole::Sekretaris))
        ->get('/admin/announcements/create')
        ->assertSuccessful();
});

it('menutup Log Aktivitas dari Sekretaris dan Bendahara', function (UserRole $role): void {
    $this->actingAs(userWithRole($role))
        ->get('/admin/activity-logs')
        ->assertForbidden();
})->with([
    UserRole::Sekretaris,
    UserRole::Bendahara,
]);
