<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'waste_category_id' => ['nullable', 'exists:waste_categories,id'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'risk_score' => ['nullable', 'integer', 'min:1', 'max:100'],
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_phone' => ['nullable', 'string', 'max:50'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:10240'],
            'ai_suggestions' => ['nullable', 'array'],
            'ai_suggestions.manpower' => ['nullable', 'integer', 'min:1'],
            'ai_suggestions.lorries' => ['nullable', 'integer', 'min:1'],
            'ai_suggestions.deadline' => ['nullable', 'date'],
        ];
    }
}
