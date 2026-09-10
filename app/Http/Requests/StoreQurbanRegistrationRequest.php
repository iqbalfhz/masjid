<?php

namespace App\Http\Requests;

use App\Enums\QurbanServiceType;
use App\Http\Requests\Concerns\ProtectsPublicForm;
use App\Models\QurbanRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQurbanRegistrationRequest extends FormRequest
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
            'service_type' => ['required', Rule::enum(QurbanServiceType::class)],
            'animal_type' => ['required', Rule::in(array_keys(QurbanRegistration::ANIMAL_TYPES))],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
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
            'service_type' => 'jenis layanan',
            'animal_type' => 'jenis hewan',
            'quantity' => 'jumlah',
            'notes' => 'catatan',
        ];
    }
}
