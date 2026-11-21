<?php

namespace App\Http\Requests;

use App\Enums\VenueStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'cpdo_admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('venues', 'name')->ignore($this->route('venue'))],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity_min' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'capacity_max' => [
                'required', 'integer', 'min:1', 'max:100000',
                Rule::when($this->filled('capacity_min'), ['gte:capacity_min']),
            ],
            'amenities' => ['nullable', 'string', 'max:3000'],
            'rules' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::enum(VenueStatus::class)],
            'status_note' => ['nullable', 'string', 'max:255'],
            'available_from' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'capacity_max.gte' => 'The maximum capacity must be the same as or higher than the minimum.',
            'image.max' => 'The photo may not be larger than 4 MB.',
        ];
    }
}
