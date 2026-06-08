@extends('layouts.app')

@section('title', 'Get Your Recommendation')

@section('content')
<div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
    <section>
        <div class="mb-8">
            <span class="badge-ai mb-4">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                </svg>
                Smart Matching
            </span>
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Find your perfect
                <span class="text-accent">computer build</span>
            </h1>
            <p class="mt-3 leading-relaxed text-muted">
                Tell us your budget, device preference, and how you'll use it. We'll recommend the best CPU, RAM, storage, GPU, and more — tailored to your needs.
            </p>
        </div>

        <form action="{{ route('recommend') }}" method="POST" class="card-container space-y-6">
            @csrf

            <div>
                <label for="budget" class="form-label">Budget</label>
                <p class="form-hint">e.g. $800, $1000-1500, 1200 USD</p>
                <input
                    type="text"
                    name="budget"
                    id="budget"
                    value="{{ old('budget', $input['budget'] ?? '') }}"
                    placeholder="$1,000"
                    required
                    class="form-input mt-2"
                >
                @error('budget')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="form-label">Device Type</span>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach (['pc' => 'Desktop PC', 'laptop' => 'Laptop'] as $value => $label)
                        <label class="relative cursor-pointer">
                            <input
                                type="radio"
                                name="device_type"
                                value="{{ $value }}"
                                class="peer sr-only"
                                {{ old('device_type', $input['device_type'] ?? 'pc') === $value ? 'checked' : '' }}
                                required
                            >
                            <div class="option-card py-4 text-center peer-checked:border-accent peer-checked:bg-accent-soft peer-checked:ring-2 peer-checked:ring-accent/25">
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('device_type')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="form-label">Primary Usage</span>
                <p class="form-hint">Select all that apply</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($usageOptions as $value => $label)
                        <label class="usage-option option-card flex cursor-pointer items-center gap-3 has-[:checked]:option-card-checked">
                            <input
                                type="checkbox"
                                name="primary_usage[]"
                                value="{{ $value }}"
                                class="peer sr-only"
                                {{ in_array($value, old('primary_usage', $input['primary_usage'] ?? [])) ? 'checked' : '' }}
                            >
                            <span class="usage-checkbox">
                                <svg class="usage-checkbox-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.42 0l-3.25-3.25a1 1 0 111.42-1.42l2.54 2.54 6.54-6.54a1 1 0 011.42 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('primary_usage')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="additional_requirements" class="form-label">Additional Requirements</label>
                <p class="form-hint">Optional — portability, quiet operation, specific software, etc.</p>
                <textarea
                    name="additional_requirements"
                    id="additional_requirements"
                    rows="3"
                    placeholder="e.g. Needs to be portable for college, prefer quiet fans..."
                    class="form-input mt-2 resize-none"
                >{{ old('additional_requirements', $input['additional_requirements'] ?? '') }}</textarea>
                @error('additional_requirements')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-cta">
                Get AI Powered Recommendation
            </button>
        </form>
    </section>

    <section>
        @if (isset($recommendation))
            <div class="sticky top-8">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <span class="badge-ai">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                        </svg>
                        Recommended for you
                    </span>
                    <span class="badge-match">
                        98% Match
                    </span>
                </div>

                <div class="card-container">
                    <pre class="whitespace-pre-wrap font-sans text-sm leading-relaxed text-muted">{{ $recommendation }}</pre>
                </div>

                <p class="mt-4 text-center text-xs text-muted">
                    Copy and share this recommendation, or adjust your inputs and try again.
                </p>
            </div>
        @else
            <div class="flex h-full min-h-[400px] flex-col items-center justify-center rounded-2xl border border-dashed border-secondary-light bg-secondary/40 p-8 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-accent/10">
                    <svg class="h-8 w-8 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-white">Ready when you are</h2>
                <p class="mt-2 max-w-sm text-sm leading-relaxed text-muted">
                    Fill out the form and we'll analyze your requirements to produce a detailed, human-readable hardware recommendation.
                </p>
                <div class="mt-8 grid w-full max-w-sm gap-3 text-left text-sm">
                    <div class="flex items-start gap-3 rounded-xl border border-secondary-light bg-secondary px-4 py-3">
                        <span class="font-bold text-accent">✓</span>
                        <span class="text-muted">Budget-aware CPU, RAM, GPU &amp; storage picks</span>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-secondary-light bg-secondary px-4 py-3">
                        <span class="font-bold text-accent">★</span>
                        <span class="text-muted">Performance ratings for your use cases</span>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-secondary-light bg-secondary px-4 py-3">
                        <span class="font-bold text-accent">✦</span>
                        <span class="text-muted">Upgrade tips and honest trade-off notes</span>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
