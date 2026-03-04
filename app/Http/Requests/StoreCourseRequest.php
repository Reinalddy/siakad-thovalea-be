<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
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
            'code' => ['required', 'string', 'unique:courses'],
            'name' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1'],
            'semester_type' => ['required', 'in:Odd,Even'],
            'prerequisite_course_id' => ['nullable', 'exists:courses,id'],
        ];
    }
}
