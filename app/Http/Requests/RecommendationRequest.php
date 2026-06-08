<?php

namespace App\Http\Requests;

use App\Services\PcRecommendationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'budget' => ['required', 'string', 'max:100'],
            'device_type' => ['required', Rule::in(['pc', 'laptop'])],
            'primary_usage' => ['required', 'array', 'min:1'],
            'primary_usage.*' => ['string', Rule::in(array_keys(PcRecommendationService::usageOptions()))],
            'additional_requirements' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'budget.required' => 'Please enter your budget.',
            'device_type.required' => 'Please select PC or Laptop.',
            'primary_usage.required' => 'Please select at least one primary usage.',
            'primary_usage.min' => 'Please select at least one primary usage.',
        ];
    }
}
