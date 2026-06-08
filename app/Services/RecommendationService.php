<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class RecommendationService
{
    public function __construct(
        private readonly GeminiRecommendationService $geminiRecommendationService,
        private readonly PcRecommendationService $pcRecommendationService,
    ) {}

    /**
     * @param  array{budget: string, device_type: string, primary_usage: array<int, string>, additional_requirements?: string|null}  $input
     */
    public function recommend(array $input): string
    {
        if ($this->shouldUseGemini()) {
            try {
                return $this->geminiRecommendationService->recommend($input);
            } catch (\Throwable $exception) {
                Log::warning('Gemini recommendation failed, using rule-based fallback.', [
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $this->pcRecommendationService->recommend($input);
    }

    private function shouldUseGemini(): bool
    {
        return filled(config('services.gemini.api_key'));
    }
}
