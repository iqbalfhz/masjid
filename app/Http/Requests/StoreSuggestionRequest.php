<?php

namespace App\Http\Requests;

use App\Enums\SuggestionCategory;
use App\Http\Requests\Concerns\ProtectsPublicForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuggestionRequest extends FormRequest
{
    use ProtectsPublicForm;

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::enum(SuggestionCategory::class)],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'contact' => 'kontak',
            'category' => 'kategori',
            'message' => 'isi masukan',
        ];
    }
}
