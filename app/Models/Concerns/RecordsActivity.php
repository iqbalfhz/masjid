<?php

namespace App\Models\Concerns;

use App\Support\ActivityLogPresenter;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Mencatat create/update/delete setiap model ke Log Aktivitas (PRD 5.2.16).
 *
 * Aksi approve/reject dicatat terpisah oleh alur approval karena butuh
 * deskripsi khusus (siapa meninjau, catatan revisi).
 */
trait RecordsActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->useLogName($this->activityLogName())
            ->logOnlyDirty()
            ->dontLogEmptyChanges();

        $attributes = $this->activityLoggedAttributes();

        return $attributes === []
            ? $options->logFillable()
            : $options->logOnly($attributes);
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        $modul = Str::lower($this->activityModuleLabel());

        return match ($eventName) {
            'created' => "Menambahkan {$modul}",
            'updated' => "Mengubah {$modul}",
            'deleted' => "Menghapus {$modul}",
            default => Str::headline($eventName)." {$modul}",
        };
    }

    /**
     * Kunci modul yang stabil untuk penyaringan (snake_case nama model).
     * Label yang dibaca pengurus diterjemahkan saat ditampilkan.
     */
    public function activityLogName(): string
    {
        return Str::snake(class_basename($this));
    }

    public function activityModuleLabel(): string
    {
        return ActivityLogPresenter::MODULES[$this->activityLogName()]
            ?? Str::headline(class_basename($this));
    }

    /**
     * Kolom yang ikut dicatat. Array kosong berarti seluruh kolom fillable.
     *
     * @return list<string>
     */
    public function activityLoggedAttributes(): array
    {
        return [];
    }
}
