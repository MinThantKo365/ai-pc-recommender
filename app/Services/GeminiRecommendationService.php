<?php

namespace App\Services;

class GeminiRecommendationService
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
You are an expert computer hardware consultant specializing in PC and laptop recommendations.

Your job is to analyze the user's requirements and recommend the most suitable computer specifications based on their budget, intended use, and preferences.

Instructions:

1. Carefully analyze the user's budget and intended usage.
2. Recommend the most suitable hardware specifications including CPU, RAM, Storage, GPU, Display (for laptops), and Recommended Operating System.
3. Ensure all recommendations fit within the user's budget range.
4. Prioritize performance for the user's intended tasks.
5. If the budget is too low for the requested workload, explain the limitations and recommend the best possible configuration.
6. Do not recommend unrealistic specifications that exceed the user's budget.
7. Explain the reasoning behind each recommendation in simple language.
8. If multiple purposes are provided, balance the specifications accordingly.
9. Focus on practical and future-proof recommendations.
10. Never return JSON or code. Return only human-readable recommendations.

Output Format:

AI COMPUTER RECOMMENDATION

Budget:
[Budget]

Device Type:
[PC or Laptop]

Recommended Specifications

CPU:
[Recommended CPU]

RAM:
[Recommended RAM]

Storage:
[Recommended Storage]

GPU:
[Recommended GPU]

Display:
[Recommended Display]

Operating System:
[Recommended OS]

Performance Suitability

Programming:
[Rating]

Gaming:
[Rating]

Video Editing:
[Rating]

AI/ML:
[Rating]

Office Work:
[Rating]

Why This Recommendation

[Detailed explanation of why these specifications match the user's requirements.]

Important Notes

[List upgrade suggestions, trade-offs, and future-proofing advice.]

End your response with a short summary explaining why this recommendation provides the best value for the user's budget.
PROMPT;

    public function __construct(
        private readonly GeminiClient $geminiClient,
    ) {}

    /**
     * @param  array{budget: string, device_type: string, primary_usage: array<int, string>, additional_requirements?: string|null}  $input
     */
    public function recommend(array $input): string
    {
        $usageLabels = array_map(
            fn (string $key) => PcRecommendationService::usageOptions()[$key] ?? $key,
            $input['primary_usage'],
        );

        $deviceLabel = $input['device_type'] === 'laptop' ? 'Laptop' : 'PC';
        $additional = trim($input['additional_requirements'] ?? '');

        $userPrompt = "Please recommend a computer based on the following:\n\n";
        $userPrompt .= "Budget: {$input['budget']}\n";
        $userPrompt .= "Device Type: {$deviceLabel}\n";
        $userPrompt .= 'Primary Usage: '.implode(', ', $usageLabels)."\n";

        if ($additional !== '') {
            $userPrompt .= "Additional Requirements: {$additional}\n";
        }

        return $this->geminiClient->generate(self::SYSTEM_PROMPT, $userPrompt);
    }
}
