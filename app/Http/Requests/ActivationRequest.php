<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'order_number' => 'required|string|max:50',
            'license_key' => 'required|string|max:100',
            'installation_id' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'order_number.required' => 'Nomor order wajib diisi.',
            'license_key.required' => 'Kunci lisensi wajib diisi.',
            'installation_id.required' => 'Installation ID wajib diisi.',
            'installation_id.regex' => 'Installation ID harus 54 atau 63 digit angka.',
        ];
    }
    
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $installationId = preg_replace('/[^0-9]/', '', $this->installation_id);
            $length = strlen($installationId);
            
            if (!in_array($length, [54, 63])) {
                $validator->errors()->add(
                    'installation_id',
                    'Installation ID harus 54 atau 63 digit angka.'
                );
            }
        });
    }
}