<?php

namespace App\Models\Concerns;

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
        return match ($eventName) {
            'created' => 'Membuat '.$this->activityLogName(),
            'updated' => 'Mengubah '.$this->activityLogName(),
            'deleted' => 'Menghapus '.$this->activityLogName(),
            default => $eventName.' '.$this->activityLogName(),
        };
    }

    /**
     * Label modul yang muncul di Log Aktivitas.
     */
    public function activityLogName(): string
    {
        return str(class_basename($this))->headline()->lower()->value();
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
