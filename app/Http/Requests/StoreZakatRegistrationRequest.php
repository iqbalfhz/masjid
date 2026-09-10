<?php

namespace App\Http\Requests;

use App\Enums\ZakatType;
use App\Http\Requests\Concerns\ProtectsPublicForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreZakatRegistrationRequest extends FormRequest
{
    use ProtectsPublicForm;

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'zakat_type' => ['required', Rule::enum(ZakatType::class)],
            'soul_count' => ['required_if:zakat_type,fitrah', 'nullable', 'integer', 'min:1', 'max:50'],
            'amount' => ['required_if:zakat_type,maal', 'nullable', 'numeric', 'min:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'phone' => 'nomor kontak',
            'zakat_type' => 'jenis zakat',
            'soul_count' => 'jumlah jiwa',
            'amount' => 'nominal',
            'notes' => 'catatan',
        ];
    }
}
