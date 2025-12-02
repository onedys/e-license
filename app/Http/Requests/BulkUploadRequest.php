<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'keys' => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak valid.',
            'keys.required' => 'Kunci lisensi wajib diisi.',
            'keys.min' => 'Minimal 1 kunci lisensi.',
        ];
    }
    
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $keys = array_filter(array_map('trim', explode("\n", $this->keys)));
            
            if (count($keys) > 100) {
                $validator->errors()->add(
                    'keys',
                    'Maksimal 100 lisensi per batch.'
                );
            }
            
            if (empty($keys)) {
                $validator->errors()->add(
                    'keys',
                    'Tidak ada lisensi yang valid ditemukan.'
                );
            }
        });
    }
}