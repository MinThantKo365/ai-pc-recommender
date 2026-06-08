<?php

namespace Tests\Feature;

use Tests\TestCase;

class RecommendationTest extends TestCase
{
    public function test_home_page_displays_recommendation_form(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Find your perfect');
        $response->assertSee('Get AI Powered Recommendation');
    }

    public function test_recommendation_returns_formatted_output(): void
    {
        $response = $this->post('/recommend', [
            'budget' => '$1,200',
            'device_type' => 'pc',
            'primary_usage' => ['gaming', 'programming'],
            'additional_requirements' => 'Prefer quiet operation',
        ]);

        $response->assertRedirect(route('recommend.show'));
        $response = $this->get('/recommend');
        $response->assertOk();
        $response->assertSee('AI COMPUTER RECOMMENDATION');
        $response->assertSee('Budget:');
        $response->assertSee('$1,200');
        $response->assertSee('Device Type:');
        $response->assertSee('PC');
        $response->assertSee('CPU:');
        $response->assertSee('RAM:');
        $response->assertSee('Why This Recommendation');
        $response->assertSee('Important Notes');
    }

    public function test_recommendation_requires_budget_and_usage(): void
    {
        $response = $this->post('/recommend', []);

        $response->assertSessionHasErrors(['budget', 'device_type', 'primary_usage']);
    }

    public function test_get_recommend_without_session_redirects_home(): void
    {
        $response = $this->get('/recommend');

        $response->assertRedirect(route('home'));
    }

    public function test_laptop_recommendation_includes_display(): void
    {
        $response = $this->post('/recommend', [
            'budget' => '900',
            'device_type' => 'laptop',
            'primary_usage' => ['student_use'],
        ]);

        $response->assertRedirect(route('recommend.show'));
        $response = $this->followRedirects($response);
        $response->assertOk();
        $response->assertSee('Laptop');
        $response->assertSee('Display:');
    }
}
