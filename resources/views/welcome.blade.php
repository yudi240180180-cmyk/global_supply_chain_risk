<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Global Supply Chain Risk Platform') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                :root { color-scheme: dark; font-family: Inter, system-ui, sans-serif; }
                body { margin: 0; background: #07111f; color: #f8fafc; }
            </style>
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-800 bg-slate-900/80 px-6 py-4 shadow-xl shadow-black/20 backdrop-blur">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-400">Global Supply Chain Risk Platform</p>
                    <h1 class="mt-1 text-2xl font-semibold text-white">Operational intelligence for your supplier network</h1>
                </div>
                <div class="rounded-full border border-slate-700 bg-slate-800 px-4 py-2 text-sm text-slate-300">
                    Live data coverage • {{ $totalCountries }} monitored markets
                </div>
            </header>

            <main class="grid flex-1 gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                <section class="space-y-6">
                    <div class="rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 p-6 shadow-2xl shadow-black/30">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium uppercase tracking-[0.3em] text-slate-400">Current risk posture</p>
                                <h2 class="mt-2 text-3xl font-semibold text-white">{{ $latestRisk ? $latestRisk->risk_level : 'Monitoring' }} signal</h2>
                            </div>
                            <div class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-sm font-medium text-emerald-300">
                                {{ $economicsCount }} economic snapshots
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                                <p class="text-sm text-slate-400">Countries tracked</p>
                                <p class="mt-2 text-3xl font-semibold text-white">{{ $totalCountries }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                                <p class="text-sm text-slate-400">Latest total score</p>
                                <p class="mt-2 text-3xl font-semibold text-white">{{ $latestRisk ? number_format($latestRisk->total_score, 1) : '—' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                                <p class="text-sm text-slate-400">High-risk nodes</p>
                                <p class="mt-2 text-3xl font-semibold text-white">{{ $highRiskCountries->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-black/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium uppercase tracking-[0.3em] text-slate-400">Watchlist</p>
                                <h3 class="mt-1 text-xl font-semibold text-white">High-risk countries</h3>
                            </div>
                            <span class="text-sm text-slate-400">Updated from API-backed scoring</span>
                        </div>

                        @if ($highRiskCountries->isNotEmpty())
                            <ul class="mt-5 space-y-3">
                                @foreach ($highRiskCountries as $item)
                                    <li class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">
                                        <div>
                                            <p class="font-medium text-white">{{ $item->country?->name ?? 'Unknown market' }}</p>
                                            <p class="text-sm text-slate-400">Total score {{ number_format($item->total_score, 1) }}</p>
                                        </div>
                                        <span class="rounded-full border border-rose-500/20 bg-rose-500/10 px-3 py-1 text-sm font-medium text-rose-300">
                                            {{ $item->risk_level }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="mt-5 rounded-2xl border border-dashed border-slate-700 bg-slate-950/40 p-6 text-sm text-slate-400">
                                No high-risk countries are available yet. The platform will populate this list as data syncs complete.
                            </div>
                        @endif
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-black/20">
                        <p class="text-sm font-medium uppercase tracking-[0.3em] text-slate-400">Data sources</p>
                        <ul class="mt-4 space-y-3 text-sm text-slate-300">
                            <li class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">REST Countries • supplier and region master data</li>
                            <li class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">World Bank • GDP, inflation, trade flows</li>
                            <li class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">Open-Meteo • weather and disruption risk</li>
                            <li class="rounded-2xl border border-slate-800 bg-slate-950/70 px-4 py-3">News and sentiment feeds • event-driven risk signals</li>
                        </ul>
                    </div>

                    <div class="rounded-3xl border border-slate-800 bg-gradient-to-br from-amber-500/10 to-rose-500/10 p-6 shadow-xl shadow-black/20">
                        <p class="text-sm font-medium uppercase tracking-[0.3em] text-amber-300">Next step</p>
                        <h3 class="mt-2 text-xl font-semibold text-white">Sync your live APIs to populate the platform with real risk insights.</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-300">The dashboard is now wired to the real models and will render immediately once country and risk records exist in the database.</p>
                    </div>
                </aside>
            </main>
        </div>
    </body>
</html>
