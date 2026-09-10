<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProtectsPublicForm;
use App\Models\FacilityBooking;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityBookingRequest extends FormRequest
{
    use ProtectsPublicForm;

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'purpose' => ['required', 'string', 'max:255'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /**
     * Cek bentrok jadwal sebelum pengajuan tersimpan, supaya jamaah tidak
     * mengajukan slot yang sudah terpakai (PRD 5.1.14 & 12).
     */
    public function withValidator(Validator $validator): void
    {
        $this->protectAgainstSpam($validator);

        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $conflict = FacilityBooking::hasConflict(
                (int) $this->input('facility_id'),
                (string) $this->input('booking_date'),
                (string) $this->input('start_time'),
                (string) $this->input('end_time'),
            );

            if ($conflict) {
                $validator->errors()->add(
                    'booking_date',
                    'Fasilitas sudah dipesan pada tanggal dan jam tersebut. Silakan pilih waktu lain — lihat kalender ketersediaan di samping.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'facility_id' => 'fasilitas',
            'name' => 'nama pemohon',
            'phone' => 'nomor kontak',
            'purpose' => 'keperluan',
            'booking_date' => 'tanggal pemakaian',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
        ];
    }
}
