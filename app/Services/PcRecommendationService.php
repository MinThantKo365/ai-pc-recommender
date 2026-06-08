<?php

namespace App\Services;

class PcRecommendationService
{
    private const USAGE_OPTIONS = [
        'programming' => 'Programming / Software Development',
        'gaming' => 'Gaming',
        'video_editing' => 'Video Editing',
        'graphic_design' => 'Graphic Design',
        'ai_ml' => 'AI / Machine Learning',
        'office_work' => 'Office Work',
        'student_use' => 'Student Use',
        'business_use' => 'Business Use',
        'content_creation' => 'Content Creation',
        'general_use' => 'General Use',
    ];

    public static function usageOptions(): array
    {
        return self::USAGE_OPTIONS;
    }

    /**
     * @param  array{budget: string, device_type: string, primary_usage: array<int, string>, additional_requirements?: string|null}  $input
     */
    public function recommend(array $input): string
    {
        $budget = $this->parseBudget($input['budget']);
        $deviceType = $input['device_type'];
        $usages = $input['primary_usage'];
        $additional = trim($input['additional_requirements'] ?? '');

        $tier = $this->resolveBudgetTier($budget);
        $specs = $this->buildSpecs($tier, $deviceType, $usages, $additional);
        $ratings = $this->buildRatings($specs, $usages, $tier);
        $explanation = $this->buildExplanation($specs, $usages, $tier, $deviceType, $additional);
        $notes = $this->buildNotes($specs, $tier, $deviceType, $usages, $budget);
        $summary = $this->buildSummary($specs, $tier, $usages);

        return $this->formatOutput(
            budget: $input['budget'],
            deviceType: $deviceType === 'laptop' ? 'Laptop' : 'PC',
            specs: $specs,
            ratings: $ratings,
            explanation: $explanation,
            notes: $notes,
            summary: $summary,
        );
    }

    private function parseBudget(string $budget): int
    {
        $normalized = preg_replace('/[^\d.]/', '', $budget);

        if ($normalized === '' || $normalized === null) {
            return 800;
        }

        $value = (int) round((float) $normalized);

        if (preg_match('/\d+\s*[-–—to]+\s*\d+/i', $budget)) {
            preg_match_all('/\d+/', $budget, $matches);
            $numbers = array_map('intval', $matches[0] ?? []);
            if (count($numbers) >= 2) {
                return (int) round(array_sum($numbers) / count($numbers));
            }
        }

        return max(300, $value);
    }

    private function resolveBudgetTier(int $budget): string
    {
        return match (true) {
            $budget < 500 => 'entry',
            $budget < 800 => 'budget',
            $budget < 1200 => 'mid',
            $budget < 1800 => 'upper_mid',
            $budget < 2500 => 'high',
            default => 'enthusiast',
        };
    }

    /**
     * @param  array<int, string>  $usages
     * @return array{cpu: string, ram: string, storage: string, gpu: string, display: string, os: string, tier_label: string, budget_stretch: bool}
     */
    private function buildSpecs(string $tier, string $deviceType, array $usages, string $additional): array
    {
        $isLaptop = $deviceType === 'laptop';
        $needsGpu = $this->needsDiscreteGpu($usages);
        $needsHighRam = $this->needsHighRam($usages);
        $needsFastStorage = $this->needsFastStorage($usages);
        $portability = str_contains(strtolower($additional), 'portable')
            || str_contains(strtolower($additional), 'battery')
            || str_contains(strtolower($additional), 'travel');
        $quiet = str_contains(strtolower($additional), 'quiet');
        $budgetStretch = $this->isBudgetStretch($tier, $usages);

        $pcSpecs = [
            'entry' => [
                'cpu' => 'AMD Ryzen 5 5600 or Intel Core i5-12400F',
                'ram' => '16 GB DDR4',
                'storage' => '512 GB NVMe SSD',
                'gpu' => 'Integrated graphics or AMD Radeon RX 6500 XT',
                'display' => 'N/A (use your existing monitor)',
                'os' => 'Windows 11 Home or Linux (Ubuntu)',
            ],
            'budget' => [
                'cpu' => 'AMD Ryzen 5 7600 or Intel Core i5-13400F',
                'ram' => '16 GB DDR5',
                'storage' => '1 TB NVMe SSD',
                'gpu' => 'NVIDIA GeForce RTX 4060 or AMD Radeon RX 7600',
                'display' => 'N/A (24" 1080p monitor recommended separately)',
                'os' => 'Windows 11 Home',
            ],
            'mid' => [
                'cpu' => 'AMD Ryzen 7 7700 or Intel Core i7-13700F',
                'ram' => '32 GB DDR5',
                'storage' => '1 TB NVMe SSD (Gen4)',
                'gpu' => 'NVIDIA GeForce RTX 4060 Ti or AMD Radeon RX 7700 XT',
                'display' => 'N/A (27" 1440p monitor recommended separately)',
                'os' => 'Windows 11 Home or Pro',
            ],
            'upper_mid' => [
                'cpu' => 'AMD Ryzen 7 7800X3D or Intel Core i7-14700F',
                'ram' => '32 GB DDR5',
                'storage' => '2 TB NVMe SSD (Gen4)',
                'gpu' => 'NVIDIA GeForce RTX 4070 Super or AMD Radeon RX 7800 XT',
                'display' => 'N/A (27" 1440p 144Hz monitor recommended separately)',
                'os' => 'Windows 11 Pro',
            ],
            'high' => [
                'cpu' => 'AMD Ryzen 9 7900X or Intel Core i9-14900K',
                'ram' => '64 GB DDR5',
                'storage' => '2 TB NVMe SSD (Gen4) + 2 TB HDD for archives',
                'gpu' => 'NVIDIA GeForce RTX 4080 Super or AMD Radeon RX 7900 XTX',
                'display' => 'N/A (32" 4K or ultrawide monitor recommended separately)',
                'os' => 'Windows 11 Pro',
            ],
            'enthusiast' => [
                'cpu' => 'AMD Ryzen 9 9950X or Intel Core Ultra 9 285K',
                'ram' => '64–128 GB DDR5',
                'storage' => '4 TB NVMe SSD (Gen5) + 4 TB secondary SSD',
                'gpu' => 'NVIDIA GeForce RTX 5090 or AMD Radeon RX 7900 XTX',
                'display' => 'N/A (premium 4K/ultrawide display recommended separately)',
                'os' => 'Windows 11 Pro',
            ],
        ];

        $laptopSpecs = [
            'entry' => [
                'cpu' => 'AMD Ryzen 5 7530U or Intel Core i5-1335U',
                'ram' => '16 GB DDR4',
                'storage' => '512 GB NVMe SSD',
                'gpu' => 'Integrated graphics (AMD Radeon or Intel Iris Xe)',
                'display' => '14–15.6" Full HD (1920×1080), IPS',
                'os' => 'Windows 11 Home',
            ],
            'budget' => [
                'cpu' => 'AMD Ryzen 5 8640U or Intel Core Ultra 5 125U',
                'ram' => '16 GB LPDDR5X',
                'storage' => '512 GB–1 TB NVMe SSD',
                'gpu' => 'Integrated graphics or NVIDIA GeForce RTX 4050 (6 GB)',
                'display' => '14–16" Full HD or 2.5K IPS, 60–120 Hz',
                'os' => 'Windows 11 Home',
            ],
            'mid' => [
                'cpu' => 'AMD Ryzen 7 8840U or Intel Core Ultra 7 155H',
                'ram' => '32 GB LPDDR5X',
                'storage' => '1 TB NVMe SSD',
                'gpu' => 'NVIDIA GeForce RTX 4060 Laptop (8 GB)',
                'display' => '15.6–16" 2.5K IPS, 120–165 Hz, 100% sRGB',
                'os' => 'Windows 11 Home or Pro',
            ],
            'upper_mid' => [
                'cpu' => 'AMD Ryzen 9 8945HS or Intel Core Ultra 9 185H',
                'ram' => '32 GB DDR5',
                'storage' => '1–2 TB NVMe SSD (Gen4)',
                'gpu' => 'NVIDIA GeForce RTX 4070 Laptop (8 GB)',
                'display' => '16" 2.5K–3.2K IPS/OLED, 165 Hz, high color accuracy',
                'os' => 'Windows 11 Pro',
            ],
            'high' => [
                'cpu' => 'AMD Ryzen 9 8945HS or Intel Core Ultra 9 285H',
                'ram' => '64 GB DDR5',
                'storage' => '2 TB NVMe SSD (Gen4)',
                'gpu' => 'NVIDIA GeForce RTX 4080 Laptop (12 GB)',
                'display' => '16–18" 4K or 2.5K OLED, 240 Hz option',
                'os' => 'Windows 11 Pro',
            ],
            'enthusiast' => [
                'cpu' => 'AMD Ryzen 9 9955HX or Intel Core Ultra 9 285HX',
                'ram' => '64 GB DDR5',
                'storage' => '2–4 TB NVMe SSD (Gen4/Gen5)',
                'gpu' => 'NVIDIA GeForce RTX 5090 Laptop (24 GB)',
                'display' => '16–18" 4K Mini-LED or OLED, 240 Hz',
                'os' => 'Windows 11 Pro',
            ],
        ];

        $base = $isLaptop ? $laptopSpecs[$tier] : $pcSpecs[$tier];

        if ($needsHighRam && ! str_contains($base['ram'], '32') && ! str_contains($base['ram'], '64')) {
            $base['ram'] = $isLaptop ? '32 GB LPDDR5X (upgrade recommended)' : '32 GB DDR5';
        }

        if ($needsFastStorage && $tier !== 'enthusiast') {
            $base['storage'] = str_replace('512 GB', '1 TB', $base['storage']);
        }

        if (! $needsGpu && $tier === 'entry') {
            $base['gpu'] = $isLaptop
                ? 'Integrated graphics (sufficient for everyday tasks)'
                : 'Integrated graphics or entry-level GPU — skip discrete GPU to save budget';
        }

        if ($portability && $isLaptop) {
            $base['display'] = '14" 2.5K IPS, lightweight chassis (~1.2–1.5 kg), 10+ hour battery';
        }

        if ($quiet && ! $isLaptop) {
            $base['cpu'] .= ' (with quality air cooler for quiet operation)';
        }

        if (in_array('ai_ml', $usages, true) && $tier === 'entry') {
            $base['gpu'] = $isLaptop
                ? 'NVIDIA GPU with 6+ GB VRAM minimum (budget may limit training workloads)'
                : 'NVIDIA GeForce RTX 4060 (8 GB VRAM) — minimum for local AI experimentation';
            $base['ram'] = '32 GB (strongly recommended for AI/ML)';
        }

        if (in_array('graphic_design', $usages, true) || in_array('content_creation', $usages, true)) {
            if ($isLaptop) {
                $base['display'] = preg_replace('/Full HD/', '2.5K with 100% sRGB color accuracy', $base['display']) ?? $base['display'];
            }
        }

        $base['tier_label'] = $tier;
        $base['budget_stretch'] = $budgetStretch;

        return $base;
    }

    /**
     * @param  array<int, string>  $usages
     */
    private function needsDiscreteGpu(array $usages): bool
    {
        return count(array_intersect($usages, ['gaming', 'video_editing', 'ai_ml', 'content_creation', 'graphic_design'])) > 0;
    }

    /**
     * @param  array<int, string>  $usages
     */
    private function needsHighRam(array $usages): bool
    {
        return count(array_intersect($usages, ['programming', 'video_editing', 'ai_ml', 'content_creation', 'graphic_design'])) > 0;
    }

    /**
     * @param  array<int, string>  $usages
     */
    private function needsFastStorage(array $usages): bool
    {
        return count(array_intersect($usages, ['video_editing', 'content_creation', 'programming'])) > 0;
    }

    /**
     * @param  array<int, string>  $usages
     */
    private function isBudgetStretch(string $tier, array $usages): bool
    {
        $demanding = count(array_intersect($usages, ['gaming', 'video_editing', 'ai_ml', 'content_creation'])) > 0;

        return $demanding && in_array($tier, ['entry', 'budget'], true);
    }

    /**
     * @param  array{cpu: string, ram: string, storage: string, gpu: string, display: string, os: string, tier_label: string, budget_stretch: bool}  $specs
     * @param  array<int, string>  $usages
     * @return array<string, string>
     */
    private function buildRatings(array $specs, array $usages, string $tier): array
    {
        $tierScore = match ($tier) {
            'entry' => 1,
            'budget' => 2,
            'mid' => 3,
            'upper_mid' => 4,
            'high', 'enthusiast' => 5,
            default => 3,
        };

        $hasStrongGpu = str_contains(strtolower($specs['gpu']), 'rtx 40')
            || str_contains(strtolower($specs['gpu']), 'rtx 50')
            || str_contains(strtolower($specs['gpu']), '7900');

        $hasHighRam = str_contains($specs['ram'], '32') || str_contains($specs['ram'], '64');

        $ratings = [
            'programming' => $this->scoreToRating(min(5, $tierScore + ($hasHighRam ? 1 : 0))),
            'gaming' => $this->scoreToRating($hasStrongGpu ? min(5, $tierScore + 1) : max(1, $tierScore - 1)),
            'video_editing' => $this->scoreToRating(min(5, $tierScore + ($hasStrongGpu && $hasHighRam ? 1 : 0))),
            'ai_ml' => $this->scoreToRating($hasStrongGpu && $hasHighRam ? min(5, $tierScore) : max(1, $tierScore - 2)),
            'office_work' => $this->scoreToRating(min(5, $tierScore + 1)),
        ];

        if (in_array('gaming', $usages, true) && $tier === 'entry') {
            $ratings['gaming'] = 'Fair — 1080p low settings only';
        }

        if (in_array('ai_ml', $usages, true) && in_array($tier, ['entry', 'budget'], true)) {
            $ratings['ai_ml'] = 'Limited — cloud GPU recommended for serious training';
        }

        return $ratings;
    }

    private function scoreToRating(int $score): string
    {
        return match (true) {
            $score >= 5 => 'Excellent',
            $score >= 4 => 'Very Good',
            $score >= 3 => 'Good',
            $score >= 2 => 'Fair',
            default => 'Limited',
        };
    }

    /**
     * @param  array{cpu: string, ram: string, storage: string, gpu: string, display: string, os: string, tier_label: string, budget_stretch: bool}  $specs
     * @param  array<int, string>  $usages
     */
    private function buildExplanation(array $specs, array $usages, string $tier, string $deviceType, string $additional): string
    {
        $usageLabels = array_map(fn (string $key) => self::USAGE_OPTIONS[$key] ?? $key, $usages);
        $usageText = implode(', ', $usageLabels);
        $device = $deviceType === 'laptop' ? 'laptop' : 'desktop PC';

        $parts = [];
        $parts[] = "This {$device} configuration is tailored for {$usageText}.";

        if (in_array('programming', $usages, true)) {
            $parts[] = "The {$specs['cpu']} provides strong multi-core performance for compiling code, running containers, and IDE workloads, while {$specs['ram']} keeps multiple development tools open without slowdowns.";
        }

        if (in_array('gaming', $usages, true)) {
            $parts[] = "The {$specs['gpu']} is the key gaming component here — it determines frame rates and visual quality at your target resolution.";
        }

        if (in_array('video_editing', $usages, true) || in_array('content_creation', $usages, true)) {
            $parts[] = "Fast {$specs['storage']} storage reduces project load times and scratch-disk bottlenecks during timeline editing and rendering.";
        }

        if (in_array('ai_ml', $usages, true)) {
            $parts[] = 'For AI and machine learning, GPU VRAM and system RAM are critical — this setup balances local experimentation with practical budget limits.';
        }

        if (in_array('office_work', $usages, true) || in_array('student_use', $usages, true) || in_array('business_use', $usages, true)) {
            $parts[] = 'For everyday productivity, this system offers responsive performance without paying for hardware you will not use.';
        }

        if (in_array('graphic_design', $usages, true)) {
            $parts[] = "Color-accurate display and sufficient RAM ensure smooth work in Photoshop, Illustrator, and similar creative tools.";
        }

        if ($specs['budget_stretch']) {
            $parts[] = 'Your budget is on the lower side for some of your selected workloads — this build prioritizes the components that matter most and explains trade-offs below.';
        }

        if ($additional !== '') {
            $parts[] = "Your additional note (\"{$additional}\") was considered when balancing portability, noise, and performance.";
        }

        return implode(' ', $parts);
    }

    /**
     * @param  array{cpu: string, ram: string, storage: string, gpu: string, display: string, os: string, tier_label: string, budget_stretch: bool}  $specs
     * @param  array<int, string>  $usages
     * @return array<int, string>
     */
    private function buildNotes(array $specs, string $tier, string $deviceType, array $usages, int $budget): array
    {
        $notes = [];
        $isLaptop = $deviceType === 'laptop';

        if ($specs['budget_stretch']) {
            $notes[] = 'Consider increasing your budget by $200–400 for noticeably better GPU performance in demanding tasks.';
        }

        if (! $isLaptop) {
            $notes[] = 'Desktop PCs offer better performance per dollar and easier future upgrades (GPU, RAM, storage).';
            $notes[] = 'Remember to budget for peripherals: monitor, keyboard, mouse, and speakers/headset if you do not already own them.';
        } else {
            $notes[] = 'Laptop GPUs are less powerful than desktop equivalents at the same model number — expect roughly 20–30% lower performance.';
            $notes[] = 'Check battery life reviews if you plan to work unplugged frequently.';
        }

        if (in_array('gaming', $usages, true)) {
            $notes[] = 'For gaming, prioritize GPU upgrades first, then CPU. A faster SSD has minimal impact on frame rates.';
        }

        if (in_array('ai_ml', $usages, true)) {
            $notes[] = 'For serious model training, cloud services (Google Colab, AWS, Azure) may be more cost-effective than local hardware at lower budgets.';
        }

        if (in_array('programming', $usages, true)) {
            $notes[] = '32 GB RAM is worth the investment if you run Docker, virtual machines, or large monorepos regularly.';
        }

        if ($tier === 'entry' || $tier === 'budget') {
            $notes[] = 'Future-proofing tip: choose a motherboard/platform with upgrade paths for RAM and storage.';
        }

        if ($budget < 600 && in_array('gaming', $usages, true)) {
            $notes[] = 'At this budget, consider a used or previous-generation GPU to maximize gaming value.';
        }

        $notes[] = 'Prices fluctuate — shop during sales events and compare pre-built vs. custom-built options.';

        return $notes;
    }

    /**
     * @param  array{cpu: string, ram: string, storage: string, gpu: string, display: string, os: string}  $specs
     * @param  array<int, string>  $usages
     */
    private function buildSummary(array $specs, string $tier, array $usages): string
    {
        $focus = match (true) {
            in_array('gaming', $usages, true) => 'gaming performance',
            in_array('ai_ml', $usages, true) => 'AI and compute workloads',
            in_array('video_editing', $usages, true) => 'content creation speed',
            in_array('programming', $usages, true) => 'development productivity',
            default => 'balanced everyday performance',
        };

        $tierLabel = match ($tier) {
            'entry' => 'entry-level',
            'budget' => 'budget-friendly',
            'mid' => 'mid-range',
            'upper_mid' => 'upper mid-range',
            'high' => 'high-performance',
            'enthusiast' => 'enthusiast-grade',
            default => 'well-balanced',
        };

        return "This {$tierLabel} build delivers strong value by investing in the right components for {$focus}, staying realistic about your budget while leaving room for practical upgrades later.";
    }

    /**
     * @param  array{cpu: string, ram: string, storage: string, gpu: string, display: string, os: string}  $specs
     * @param  array<string, string>  $ratings
     * @param  array<int, string>  $notes
     */
    private function formatOutput(
        string $budget,
        string $deviceType,
        array $specs,
        array $ratings,
        string $explanation,
        array $notes,
        string $summary,
    ): string {
        $display = $deviceType === 'PC' && $specs['display'] === 'N/A (use your existing monitor)'
            ? 'N/A (desktop — use your existing monitor)'
            : $specs['display'];

        $notesText = implode("\n", array_map(fn (string $note) => "- {$note}", $notes));

        return <<<OUTPUT

AI COMPUTER RECOMMENDATION

Budget:
{$budget}

Device Type:
{$deviceType}

Recommended Specifications

CPU:
{$specs['cpu']}

RAM:
{$specs['ram']}

Storage:
{$specs['storage']}

GPU:
{$specs['gpu']}

Display:
{$display}

Operating System:
{$specs['os']}

Performance Suitability

Programming:
{$ratings['programming']}

Gaming:
{$ratings['gaming']}

Video Editing:
{$ratings['video_editing']}

AI/ML:
{$ratings['ai_ml']}

Office Work:
{$ratings['office_work']}

Why This Recommendation

{$explanation}

Important Notes

{$notesText}

{$summary}
OUTPUT;
    }
}
