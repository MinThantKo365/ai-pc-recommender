<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PC Recommander') — {{ config('app.name', 'PC Recommander') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-primary font-sans text-white antialiased">
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-32 right-0 h-[28rem] w-[28rem] rounded-full bg-accent/5 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-80 w-80 rounded-full bg-secondary/60 blur-3xl"></div>
    </div>

    <header class="border-b border-secondary-light/50 bg-primary/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6">
            <a href="{{ route('home') }}" class="group flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent shadow-lg shadow-accent/20">
                    <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold tracking-tight text-white transition-colors group-hover:text-accent">PC Recommander</p>
                    <p class="text-xs text-muted">AI Hardware Consultant</p>
                </div>
            </a>

            <span class="badge-gemini hidden sm:inline-flex">
                <!-- <svg class="badge-gemini-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="gemini-sparkle-header" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#4796E3" />
                            <stop offset="50%" stop-color="#9177C7" />
                            <stop offset="100%" stop-color="#CA6673" />
                        </linearGradient>
                    </defs>
                    <path fill="url(#gemini-sparkle-header)" d="M11.5 1.5c.45 3.55 3.05 6.15 6.6 6.6-3.55.45-6.15 3.05-6.6 6.6-.45-3.55-3.05-6.15-6.6-6.6 3.55-.45 6.15-3.05 6.6-6.6z" />
                    <path fill="url(#gemini-sparkle-header)" d="M19.75 8.25c.28 1.85 1.65 3.22 3.5 3.5-1.85.28-3.22 1.65-3.5 3.5-.28-1.85-1.65-3.22-3.5-3.5 1.85-.28 3.22-1.65 3.5-3.5z" />
                    <path fill="url(#gemini-sparkle-header)" d="M4.75 14.25c.22 1.55 1.25 2.78 2.8 3-1.55.22-2.78 1.25-3 2.8-.22-1.55-1.25-2.78-2.8-3 1.55-.22 2.78-1.25 3-2.8z" />
                </svg> -->
                <span class="badge-gemini-text">Gemini AI Powered</span>
            </span>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
        @yield('content')
    </main>

    <footer class="border-t border-secondary-light/50 py-6 text-center text-sm text-muted">
        <p>Personalized PC &amp; laptop recommendations based on your budget and needs. Powered by Gemini AI and developed by <a href="https://github.com/MinThantKo365" class="text-accent" target="_blank">MinThantKo</a></p>
    </footer>
</body>
</html>
