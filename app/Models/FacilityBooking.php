<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['booking_number', 'facility_id', 'name', 'phone', 'purpose', 'booking_date', 'start_time', 'end_time', 'status', 'approval_note', 'reviewed_by', 'reviewed_at'])]
class FacilityBooking extends Model
{
    use HasFactory, RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'status' => BookingStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $booking): void {
            $booking->booking_number ??= static::generateNumber();
        });
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Pengajuan yang memblokir slot kalender: sudah disetujui atau masih diproses.
     *
     * @param  Builder<static>  $query
     */
    public function scopeBlocking(Builder $query): void
    {
        $query->whereIn('status', [BookingStatus::Menunggu, BookingStatus::Disetujui]);
    }

    /**
     * Deteksi bentrok jadwal pada fasilitas & tanggal yang sama (PRD 5.2.11).
     *
     * Dua pemakaian dianggap bentrok hanya bila rentangnya benar-benar
     * beririsan; peminjaman yang bersambung (selesai 11.00, mulai 11.00)
     * tetap diperbolehkan.
     */
    public static function hasConflict(int $facilityId, string $date, string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        return static::query()
            ->blocking()
            ->where('facility_id', $facilityId)
            ->whereDate('booking_date', $date)
            ->when($ignoreId, fn (Builder $query, int $id) => $query->whereKeyNot($id))
            ->where('start_time', '<', static::normalizeTime($endTime))
            ->where('end_time', '>', static::normalizeTime($startTime))
            ->exists();
    }

    /**
     * Samakan jam ke format H:i:s supaya perbandingan tidak terpengaruh
     * panjang string (input form mengirim "11:00", database menyimpan "11:00:00").
     */
    private static function normalizeTime(string $time): string
    {
        return substr($time, 0, 5).':00';
    }

    public function approveBy(User $reviewer, ?string $note = null): void
    {
        $this->forceFill([
            'status' => BookingStatus::Disetujui,
            'approval_note' => $note,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }

    public function rejectBy(User $reviewer, string $note): void
    {
        $this->forceFill([
            'status' => BookingStatus::Ditolak,
            'approval_note' => $note,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'PJF-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (static::query()->where('booking_number', $number)->exists());

        return $number;
    }
}
