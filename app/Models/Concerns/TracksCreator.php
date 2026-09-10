<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Mengisi kolom `created_by` dengan user yang sedang login saat record dibuat,
 * supaya riwayat "siapa yang membuat" selalu terisi tanpa perlu field manual
 * di form (PRD 5.2).
 */
trait TracksCreator
{
    public static function bootTracksCreator(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->created_by) && Auth::hasUser()) {
                $model->created_by = Auth::id();
            }
        });
    }
}
