<?php

use App\Enums\UserRole;
use App\Support\PermissionMatrix;

it('memberi Sekretaris hak membuat draft pengumuman tanpa hak approve', function (): void {
    $permissions = PermissionMatrix::permissionsFor(UserRole::Sekretaris->value);

    expect($permissions)
        ->toContain('create:announcement')
        ->toContain('update:announcement')
        ->not->toContain('approve:announcement')
        ->not->toContain('delete:announcement');
});

it('memberi Ketua DKM hak approve tanpa hak mengubah konten', function (): void {
    $permissions = PermissionMatrix::permissionsFor(UserRole::KetuaDkm->value);

    expect($permissions)
        ->toContain('approve:announcement')
        ->toContain('approve:article')
        ->toContain('approve:facility_booking')
        ->toContain('view_any:announcement')
        ->not->toContain('create:announcement')
        ->not->toContain('update:announcement');
});

it('memberi Bendahara kendali penuh keuangan tapi hanya baca untuk konten', function (): void {
    $permissions = PermissionMatrix::permissionsFor(UserRole::Bendahara->value);

    expect($permissions)
        ->toContain('create:finance_transaction')
        ->toContain('delete:finance_transaction')
        ->toContain('create:zakat_registration')
        ->toContain('view:announcement')
        ->not->toContain('create:announcement')
        ->not->toContain('create:faq');
});

it('tidak memberi role selain Admin akses ke manajemen user', function (): void {
    expect(PermissionMatrix::permissionsFor(UserRole::Admin->value))->toContain('create:user');

    foreach ([UserRole::KetuaDkm, UserRole::Sekretaris, UserRole::Bendahara] as $role) {
        expect(PermissionMatrix::permissionsFor($role->value))->not->toContain('create:user');
    }
});

it('menjadikan Log Aktivitas read-only untuk semua role', function (): void {
    foreach (UserRole::cases() as $role) {
        if ($role === UserRole::Superadmin) {
            continue;
        }

        expect(PermissionMatrix::permissionsFor($role->value))
            ->not->toContain('create:activity')
            ->not->toContain('update:activity')
            ->not->toContain('delete:activity');
    }
});

it('menurunkan hak hapus massal dan urut ulang dari hak induknya', function (): void {
    $actions = PermissionMatrix::withImplied([PermissionMatrix::UPDATE, PermissionMatrix::DELETE]);

    expect($actions)
        ->toContain(PermissionMatrix::DELETE_ANY)
        ->toContain(PermissionMatrix::REORDER);
});

it('memastikan setiap permission role terdaftar di daftar permission sistem', function (): void {
    $all = PermissionMatrix::allPermissions();

    foreach (UserRole::cases() as $role) {
        expect(PermissionMatrix::permissionsFor($role->value))->each->toBeIn($all);
    }
});
