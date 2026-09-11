<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Event;
use App\Models\Study;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Daftar modul yang melewati alur approval (PRD 5.2).
 *
 * Keempat modul ini berbagi trait HasApprovalWorkflow, tapi berbeda pada tiga
 * hal saja: sebutannya di notifikasi, kolom yang dipakai sebagai judul, dan
 * rute halaman ubahnya. Sebelumnya ketiga hal itu ditulis ulang di tiap berkas
 * tabel resource; begitu dashboard ikut membutuhkannya, definisi yang tersebar
 * berubah jadi jebakan — mengganti sebutan di satu tempat meninggalkan tempat
 * lain yang usang tanpa ada yang gagal.
 *
 * Di sini semuanya disatukan supaya tabel resource dan antrean dashboard
 * membaca sumber yang sama.
 */
class ApprovableModules
{
    /**
     * @var array<class-string<Model>, array{label: string, title: string, route: string, permission: string}>
     */
    public const MODULES = [
        Announcement::class => [
            'label' => 'pengumuman',
            'title' => 'title',
            'route' => 'filament.admin.resources.announcements.edit',
            'permission' => 'approve:announcement',
        ],
        Study::class => [
            'label' => 'kajian',
            'title' => 'theme',
            'route' => 'filament.admin.resources.studies.edit',
            'permission' => 'approve:study',
        ],
        Event::class => [
            'label' => 'kegiatan',
            'title' => 'title',
            'route' => 'filament.admin.resources.events.edit',
            'permission' => 'approve:event',
        ],
        Article::class => [
            'label' => 'artikel',
            'title' => 'title',
            'route' => 'filament.admin.resources.articles.edit',
            'permission' => 'approve:article',
        ],
    ];

    /**
     * @return array{label: string, title: string, route: string, permission: string}
     */
    public static function definitionFor(Model $record): array
    {
        return self::MODULES[$record::class]
            ?? throw new \InvalidArgumentException($record::class.' bukan modul yang melewati approval.');
    }

    public static function labelFor(Model $record): string
    {
        return self::definitionFor($record)['label'];
    }

    public static function titleFor(Model $record): string
    {
        return (string) $record->getAttribute(self::definitionFor($record)['title']);
    }

    public static function urlFor(Model $record): string
    {
        return route(self::definitionFor($record)['route'], $record);
    }

    public static function permissionFor(Model $record): string
    {
        return self::definitionFor($record)['permission'];
    }

    /**
     * Kunci unik lintas modul.
     *
     * Antrean dashboard mencampur empat model berbeda, dan id-nya bisa
     * bertabrakan — Pengumuman #1 dan Kajian #1 sama-sama ada. Tipe modul ikut
     * disertakan agar tiap baris tetap bisa dibedakan.
     */
    public static function keyFor(Model $record): string
    {
        return self::labelFor($record).':'.$record->getKey();
    }

    /**
     * Seluruh konten yang menunggu ditinjau, terbaru lebih dulu.
     *
     * Dibatasi pada modul yang benar-benar boleh disetujui user tersebut, agar
     * antrean tidak memuat pekerjaan yang bukan wewenangnya.
     *
     * @return Collection<string, Model>
     */
    public static function awaitingApproval(?User $user = null): Collection
    {
        $items = new Collection;

        foreach (array_keys(self::MODULES) as $model) {
            if ($user !== null && ! $user->can(self::MODULES[$model]['permission'])) {
                continue;
            }

            /** @var Collection<int, Model> $pending */
            $pending = $model::query()->awaitingApproval()->with('creator')->get();

            foreach ($pending as $record) {
                $items->put(self::keyFor($record), $record);
            }
        }

        return $items->sortByDesc(fn (Model $record): mixed => $record->updated_at);
    }

    /**
     * Baris siap-tabel untuk antrean dashboard.
     *
     * Sengaja berupa array, bukan model Eloquent. Untuk data source kustom,
     * Filament mengunci ulang tiap model dengan $record->getKey() tanpa
     * menyediakan hook — sehingga Pengumuman #1 dan Kajian #1 saling menimpa dan
     * hanya satu baris yang tersisa. Record array mempertahankan kunci sendiri,
     * jadi itulah jalur yang didukung untuk tabel lintas model.
     *
     *  Collection<string, array<string, mixed>>
     */
    public static function queueRows(?User $user = null): Collection
    {
        return self::awaitingApproval($user)->map(fn (Model $record, string $key): array => [
            'key' => $key,
            'modul' => ucfirst(self::labelFor($record)),
            'judul' => self::titleFor($record),
            'penulis' => $record->creator?->name ?? '—',
            'menunggu_sejak' => $record->updated_at,
        ]);
    }

    /**
     * Kembalikan model dari kunci antrean, mis. "kajian:1".
     */
    public static function resolve(string $key): ?Model
    {
        [$label, $id] = array_pad(explode(':', $key, 2), 2, null);

        foreach (self::MODULES as $model => $definition) {
            if ($definition['label'] === $label) {
                return $model::query()->find($id);
            }
        }

        return null;
    }

    public static function awaitingApprovalCount(?User $user = null): int
    {
        return self::awaitingApproval($user)->count();
    }

    /**
     * Apakah user ini punya wewenang approval sama sekali?
     */
    public static function userCanApproveAnything(User $user): bool
    {
        foreach (self::MODULES as $definition) {
            if ($user->can($definition['permission'])) {
                return true;
            }
        }

        return false;
    }
}
