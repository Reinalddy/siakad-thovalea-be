<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
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
            'kode_mk' => [
                'required',
                'string',
                'max:20',
                Rule::unique('courses', 'kode_mk')->ignore($this->route('id'))
            ],
            'nama_mk'  => 'required|string|max:255',
            'sks'      => 'required|integer|min:1|max:6',
            'semester_plot' => 'required|integer|min:1|max:8',
            'tipe'    => 'required|in:Wajib,Pilihan',
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
