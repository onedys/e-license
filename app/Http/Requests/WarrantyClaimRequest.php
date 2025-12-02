<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarrantyClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'license_key' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'license_key.required' => 'Kunci lisensi wajib diisi.',
            'license_key.exists' => 'Lisensi tidak ditemukan atau tidak memenuhi syarat garansi.',
        ];
    }
}