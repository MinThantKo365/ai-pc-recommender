<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecommendationRequest;
use App\Services\PcRecommendationService;
use App\Services\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
    ) {}

    public function index(): View
    {
        return $this->renderForm();
    }

    public function show(): View|RedirectResponse
    {
        $recommendation = session('recommendation');
        $input = session('recommendation_input');

        if (! is_string($recommendation) || ! is_array($input)) {
            return redirect()->route('home');
        }

        return $this->renderForm($recommendation, $input);
    }

    public function store(RecommendationRequest $request): RedirectResponse
    {
        $input = $request->validated();

        session([
            'recommendation' => $this->recommendationService->recommend($input),
            'recommendation_input' => $input,
        ]);

        return redirect()->route('recommend.show');
    }

    /**
     * @param  array{budget: string, device_type: string, primary_usage: array<int, string>, additional_requirements?: string|null}|null  $input
     */
    private function renderForm(?string $recommendation = null, ?array $input = null): View
    {
        return view('recommendations.index', [
            'usageOptions' => PcRecommendationService::usageOptions(),
            'recommendation' => $recommendation,
            'input' => $input,
        ]);
    }
}
