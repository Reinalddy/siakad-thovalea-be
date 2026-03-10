<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AcademicPeriodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tahun_akademik' => 'required|string|max:9', // cth: 2025/2026
            'semester'       => 'required|in:Ganjil,Genap,Pendek',
            'krs_start'      => 'nullable|date',
            'krs_end'        => 'nullable|date|after_or_equal:krs_start',
            'nilai_start'    => 'nullable|date',
            'nilai_end'      => 'nullable|date|after_or_equal:nilai_start',
            'ukt_start'      => 'nullable|date',
            'ukt_end'        => 'nullable|date|after_or_equal:ukt_start',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => $validator->errors()->first(),
            'data'    => $validator->errors()
        ], 422));
    }
}
