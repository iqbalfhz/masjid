<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProtectsPublicForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRsvpRequest extends FormRequest
{
    use ProtectsPublicForm;

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(['kajian', 'kegiatan'])],
            'id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'person_count' => ['required', 'integer', 'min:1', 'max:20'],
            'note' => ['nullable', 'string', 'max:500'],
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
            'person_count' => 'jumlah orang',
            'note' => 'catatan',
        ];
    }
}
