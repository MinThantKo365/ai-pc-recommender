<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiClient
{
    public function generate(string $systemPrompt, string $userPrompt): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.0-flash');
        $baseUrl = rtrim(config('services.gemini.base_url', 'https://generativelanguage.googleapis.com'), '/');

        if (empty($apiKey)) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $url = "{$baseUrl}/v1beta/models/{$model}:generateContent";

        try {
            $response = Http::timeout(60)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemPrompt],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $userPrompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048,
                    ],
                ])
                ->throw();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('error.message')
                ?? $exception->getMessage();

            throw new RuntimeException("Gemini API request failed: {$message}", 0, $exception);
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini API returned an empty response.');
        }

        return trim($text);
    }
}
