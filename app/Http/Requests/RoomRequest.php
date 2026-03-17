<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
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
            'kode_ruang' => [
                'required',
                'string',
                'max:20',
                Rule::unique('rooms', 'kode_ruang')->ignore($this->route('id'))
            ],
            'nama_ruang'  => 'required|string|max:100',
            'kapasitas'   => 'required|integer|min:10|max:200',
            'jenis_ruang' => 'required|in:Teori,Laboratorium',
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
