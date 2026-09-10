<?php

namespace App\Support;

use App\Enums\UserRole;

/**
 * Matriks hak akses per role, terjemahan langsung dari PRD bagian 5.3.
 *
 * Nama permission mengikuti format Filament Shield yang dikonfigurasi di
 * `config/filament-shield.php`: `{aksi}:{model}` dengan aksi & model dalam
 * snake_case, misal `view_any:announcement` atau `approve:article`. Format ini
 * dipakai supaya permission hasil `php artisan shield:generate` dan permission
 * dari seeder ini merujuk record yang sama.
 */
class PermissionMatrix
{
    public const VIEW = 'view';

    public const VIEW_ANY = 'view_any';

    public const CREATE = 'create';

    public const UPDATE = 'update';

    public const DELETE = 'delete';

    public const DELETE_ANY = 'delete_any';

    public const REORDER = 'reorder';

    public const APPROVE = 'approve';

    public const MODERATE = 'moderate';

    /**
     * Aksi turunan yang selalu ikut ketika sebuah role punya aksi induknya.
     *
     * `delete_any` dibutuhkan tombol hapus massal, `reorder` dibutuhkan tabel
     * yang bisa diurut ulang (FAQ, pengurus, isi galeri).
     *
     * @var array<string, string>
     */
    public const IMPLIED = [
        self::DELETE => self::DELETE_ANY,
        self::UPDATE => self::REORDER,
    ];

    /**
     * Aksi yang tercakup oleh singkatan "CRUD" di PRD.
     *
     * @var list<string>
     */
    public const CRUD = [self::VIEW_ANY, self::VIEW, self::CREATE, self::UPDATE, self::DELETE];

    /**
     * Aksi read-only.
     *
     * @var list<string>
     */
    public const READ = [self::VIEW_ANY, self::VIEW];

    /**
     * Aksi untuk pembuat draft: boleh membuat & mengubah, tapi tidak menghapus.
     *
     * @var list<string>
     */
    public const DRAFT = [self::VIEW_ANY, self::VIEW, self::CREATE, self::UPDATE];

    /**
     * Modul pada matriks PRD 5.3 → label UI dan model yang tercakup di dalamnya.
     *
     * Satu baris matriks bisa mencakup beberapa model (misal "Keuangan"
     * mencakup transaksi dan kategorinya) supaya hak akses tetap sesederhana
     * yang dibaca pengurus.
     *
     * @var array<string, array{label: string, models: list<string>}>
     */
    public const MODULES = [
        'announcement' => ['label' => 'Pengumuman', 'models' => ['announcement']],
        'study' => ['label' => 'Kajian', 'models' => ['study']],
        'event' => ['label' => 'Kegiatan', 'models' => ['event']],
        'rsvp' => ['label' => 'RSVP Kajian & Kegiatan', 'models' => ['rsvp']],
        'finance' => ['label' => 'Keuangan', 'models' => ['finance_transaction', 'finance_category']],
        'gallery' => ['label' => 'Galeri', 'models' => ['gallery_album', 'gallery_item']],
        'article' => ['label' => 'Artikel', 'models' => ['article', 'article_category']],
        'library_material' => ['label' => 'E-Library', 'models' => ['library_material']],
        'testimonial' => ['label' => 'Buku Tamu & Testimoni', 'models' => ['testimonial']],
        'suggestion' => ['label' => 'Kotak Saran & Pengaduan', 'models' => ['suggestion']],
        'qurban_registration' => ['label' => 'Pendaftaran Kurban & Aqiqah', 'models' => ['qurban_registration']],
        'zakat_registration' => ['label' => 'Pendaftaran Zakat', 'models' => ['zakat_registration']],
        'facility_booking' => ['label' => 'Peminjaman Fasilitas', 'models' => ['facility_booking', 'facility']],
        'faq' => ['label' => 'FAQ', 'models' => ['faq']],
        'board_member' => ['label' => 'Pengurus', 'models' => ['board_member']],
        'prayer_schedule' => ['label' => 'Jadwal Sholat', 'models' => ['prayer_schedule']],
        'user' => ['label' => 'User & Role', 'models' => ['user', 'role']],
        'activity_log' => ['label' => 'Log Aktivitas', 'models' => ['activity']],
        'setting' => ['label' => 'Pengaturan Umum', 'models' => ['mosque_setting']],
    ];

    /**
     * Aksi di luar CRUD yang berlaku pada model tertentu.
     *
     * @return array<string, list<string>>
     */
    public static function extraActions(): array
    {
        return [
            'announcement' => [self::APPROVE],
            'study' => [self::APPROVE],
            'event' => [self::APPROVE],
            'article' => [self::APPROVE],
            'facility_booking' => [self::APPROVE],
            'testimonial' => [self::MODERATE],
        ];
    }

    /**
     * Peta modul → role → aksi yang diizinkan (PRD 5.3).
     *
     * Superadmin tidak dicantumkan: ia memakai Gate::before sehingga selalu
     * lolos seluruh pengecekan permission.
     *
     * @return array<string, array<string, list<string>>>
     */
    public static function matrix(): array
    {
        $admin = UserRole::Admin->value;
        $ketua = UserRole::KetuaDkm->value;
        $sekretaris = UserRole::Sekretaris->value;
        $bendahara = UserRole::Bendahara->value;

        $crudApprove = [...self::CRUD, self::APPROVE];
        $readApprove = [...self::READ, self::APPROVE];

        return [
            'announcement' => [
                $admin => $crudApprove,
                $ketua => $readApprove,
                $sekretaris => self::DRAFT,
                $bendahara => self::READ,
            ],
            'study' => [
                $admin => $crudApprove,
                $ketua => $readApprove,
                $sekretaris => self::DRAFT,
                $bendahara => self::READ,
            ],
            'event' => [
                $admin => $crudApprove,
                $ketua => $readApprove,
                $sekretaris => self::DRAFT,
                $bendahara => self::READ,
            ],
            'rsvp' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::CRUD,
                $bendahara => self::READ,
            ],
            'finance' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::READ,
                $bendahara => self::CRUD,
            ],
            'gallery' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::CRUD,
                $bendahara => self::READ,
            ],
            'article' => [
                $admin => $crudApprove,
                $ketua => $readApprove,
                $sekretaris => self::DRAFT,
                $bendahara => self::READ,
            ],
            'library_material' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::CRUD,
                $bendahara => self::READ,
            ],
            'testimonial' => [
                $admin => [...self::CRUD, self::MODERATE],
                $ketua => self::READ,
                $sekretaris => [...self::READ, self::MODERATE],
                $bendahara => self::READ,
            ],
            'suggestion' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::CRUD,
                $bendahara => self::READ,
            ],
            'qurban_registration' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::READ,
                $bendahara => self::CRUD,
            ],
            'zakat_registration' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::READ,
                $bendahara => self::CRUD,
            ],
            'facility_booking' => [
                $admin => $crudApprove,
                $ketua => $readApprove,
                $sekretaris => self::CRUD,
                $bendahara => self::READ,
            ],
            'faq' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::CRUD,
            ],
            'board_member' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::CRUD,
            ],
            'prayer_schedule' => [
                $admin => self::CRUD,
                $ketua => self::READ,
                $sekretaris => self::CRUD,
            ],
            'user' => [
                $admin => self::CRUD,
            ],
            'activity_log' => [
                $admin => self::READ,
                $ketua => self::READ,
            ],
            'setting' => [
                $admin => [self::VIEW_ANY, self::VIEW, self::UPDATE],
                $ketua => self::READ,
            ],
        ];
    }

    public static function name(string $action, string $model): string
    {
        return $action.config('filament-shield.permissions.separator', ':').$model;
    }

    /**
     * Seluruh nama permission yang harus ada di database.
     *
     * @return list<string>
     */
    public static function allPermissions(): array
    {
        $extra = self::extraActions();
        $permissions = [];

        foreach (self::MODULES as $module) {
            foreach ($module['models'] as $model) {
                $actions = self::withImplied([...self::CRUD, ...($extra[$model] ?? [])]);

                foreach ($actions as $action) {
                    $permissions[] = self::name($action, $model);
                }
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * @param  list<string>  $actions
     * @return list<string>
     */
    public static function withImplied(array $actions): array
    {
        foreach (self::IMPLIED as $parent => $implied) {
            if (in_array($parent, $actions, true)) {
                $actions[] = $implied;
            }
        }

        return array_values(array_unique($actions));
    }

    /**
     * Nama permission yang dimiliki sebuah role.
     *
     * @return list<string>
     */
    public static function permissionsFor(string $role): array
    {
        $extra = self::extraActions();
        $permissions = [];

        foreach (self::matrix() as $moduleKey => $roles) {
            $actions = $roles[$role] ?? [];

            if ($actions === []) {
                continue;
            }

            foreach (self::MODULES[$moduleKey]['models'] as $model) {
                $allowed = array_intersect($actions, [...self::CRUD, ...($extra[$model] ?? [])]);

                foreach (self::withImplied(array_values($allowed)) as $action) {
                    $permissions[] = self::name($action, $model);
                }
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Label modul untuk sebuah nama model, dipakai mengelompokkan permission di UI.
     */
    public static function labelForModel(string $model): string
    {
        foreach (self::MODULES as $module) {
            if (in_array($model, $module['models'], true)) {
                return $module['label'];
            }
        }

        return str($model)->replace('_', ' ')->title()->value();
    }
}
