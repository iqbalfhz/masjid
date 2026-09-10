<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Proteksi spam untuk form publik (PRD bagian 6 & 12).
 *
 * Dua lapis sederhana tanpa layanan pihak ketiga:
 * 1. Honeypot — kolom umpan yang tersembunyi dari manusia; kalau terisi, submit
 *    hampir pasti berasal dari bot.
 * 2. Batas waktu minimum — form yang dikirim beberapa milidetik setelah dimuat
 *    menandakan pengisian otomatis.
 *
 * Form Request yang perlu validasi tambahan cukup menimpa `withValidator()`
 * dan memanggil `protectAgainstSpam()` di dalamnya.
 */
trait ProtectsPublicForm
{
    public function withValidator(Validator $validator): void
    {
        $this->protectAgainstSpam($validator);
    }

    protected function protectAgainstSpam(Validator $validator): void
    {
        $validator->after(function (): void {
            if (filled($this->input(config('masjid.forms.honeypot_field')))) {
                $this->rejectAsSpam();
            }

            $renderedAt = $this->decryptRenderedAt();

            if ($renderedAt === null) {
                return;
            }

            if (now()->timestamp - $renderedAt < (int) config('masjid.forms.honeypot_min_seconds')) {
                $this->rejectAsSpam();
            }
        });
    }

    private function decryptRenderedAt(): ?int
    {
        $encrypted = $this->input(config('masjid.forms.honeypot_time_field'));

        if (blank($encrypted)) {
            return null;
        }

        try {
            return (int) decrypt($encrypted);
        } catch (Throwable) {
            $this->rejectAsSpam();
        }
    }

    private function rejectAsSpam(): never
    {
        throw ValidationException::withMessages([
            'form' => 'Pengiriman tidak dapat diproses. Silakan muat ulang halaman dan coba lagi.',
        ]);
    }
}
