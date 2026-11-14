<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public const DEPARTMENTS = [];
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isStudent = $this->user()->role === 'student';
    
        return [
            'department' => $isStudent
                ? ['nullable', 'string', Rule::in([...config('departments'), 'Others'])]
                : ['prohibited'],
    
            'department_other' => [
                'required_if:department,Others',
                'nullable',
                'string',
                'max:255',
            ],
    
            'mobile_number' => $isStudent
                ? ['nullable', 'string', 'max:30']
                : ['prohibited'],
        ];
    }
}
