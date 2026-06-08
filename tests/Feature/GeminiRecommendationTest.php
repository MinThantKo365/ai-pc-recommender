<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiRecommendationTest extends TestCase
{
    public function test_uses_gemini_when_api_key_is_configured(): void
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "========================\nAI COMPUTER RECOMMENDATION\n==========================\n\nBudget:\n\$1,200"],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->post('/recommend', [
            'budget' => '$1,200',
            'device_type' => 'pc',
            'primary_usage' => ['gaming'],
        ]);

        $response = $this->followRedirects($response);
        $response->assertOk();
        $response->assertSee('AI COMPUTER RECOMMENDATION');

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-goog-api-key', 'test-key')
                && str_contains($request->url(), 'gemini-2.0-flash:generateContent');
        });
    }

    public function test_falls_back_to_rule_based_when_gemini_fails(): void
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'API key invalid'],
            ], 400),
        ]);

        $response = $this->post('/recommend', [
            'budget' => '$1,200',
            'device_type' => 'pc',
            'primary_usage' => ['gaming'],
        ]);

        $response = $this->followRedirects($response);
        $response->assertOk();
        $response->assertSee('AI COMPUTER RECOMMENDATION');
        $response->assertSee('CPU:');
    }
}
