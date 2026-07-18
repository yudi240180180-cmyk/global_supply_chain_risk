@extends('layouts.app')

@section('title', 'News Intelligence')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold">📰 News Intelligence</h1>
            <p class="text-slate-400 mt-1">Supply chain, trade, shipping & geopolitical news with sentiment analysis</p>
        </div>
    </div>

    {{-- Sentiment Summary --}}
    <div class="grid grid-cols-4 gap-5">
        <div class="glass rounded-2xl p-5 text-center">
            <div class="text-3xl font-black">{{ $total }}</div>
            <div class="text-slate-400 text-sm mt-1">Articles Analyzed</div>
        </div>
        <div class="glass rounded-2xl p-5 text-center border-t-4 border-green-500">
            <div class="text-3xl font-black text-green-400">{{ $positive }}</div>
            <div class="text-slate-400 text-sm mt-1">Positive</div>
            <div class="text-xs text-slate-500">{{ $total > 0 ? round($positive/$total*100) : 0 }}%</div>
        </div>
        <div class="glass rounded-2xl p-5 text-center border-t-4 border-slate-500">
            <div class="text-3xl font-black text-slate-400">{{ $neutral }}</div>
            <div class="text-slate-400 text-sm mt-1">Neutral</div>
            <div class="text-xs text-slate-500">{{ $total > 0 ? round($neutral/$total*100) : 0 }}%</div>
        </div>
        <div class="glass rounded-2xl p-5 text-center border-t-4 border-red-500">
            <div class="text-3xl font-black text-red-400">{{ $negative }}</div>
            <div class="text-slate-400 text-sm mt-1">Negative</div>
            <div class="text-xs text-slate-500">{{ $total > 0 ? round($negative/$total*100) : 0 }}%</div>
        </div>
    </div>

    {{-- Sentiment Bar --}}
    @if($total > 0)
    <div class="glass rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-bold">Overall Sentiment Overview</h2>
            <span class="text-sm text-slate-400">Based on {{ $total }} articles</span>
        </div>
        <div class="flex h-5 rounded-full overflow-hidden">
            <div class="bg-green-500 transition-all" style="width:{{ $total>0?round($positive/$total*100):0 }}%" title="Positive"></div>
            <div class="bg-slate-500 transition-all" style="width:{{ $total>0?round($neutral/$total*100):0 }}%" title="Neutral"></div>
            <div class="bg-red-500 transition-all" style="width:{{ $total>0?round($negative/$total*100):0 }}%" title="Negative"></div>
        </div>
        <div class="flex gap-6 mt-2 text-xs text-slate-400">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-green-500 rounded-full inline-block"></span>Positive {{ $total>0?round($positive/$total*100):0 }}%</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-slate-500 rounded-full inline-block"></span>Neutral {{ $total>0?round($neutral/$total*100):0 }}%</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-red-500 rounded-full inline-block"></span>Negative {{ $total>0?round($negative/$total*100):0 }}%</span>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('news.index') }}" class="glass rounded-2xl p-5">
        <div class="flex gap-4 flex-wrap items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs text-slate-400 mb-1">Search</label>
                <input name="q" value="{{ request('q') }}" type="text" placeholder="Search articles…"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Category</label>
                <select name="category" class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Sentiment</label>
                <select name="sentiment" class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                    <option value="">All Sentiments</option>
                    <option value="Positive" @selected(request('sentiment') === 'Positive')>🟢 Positive</option>
                    <option value="Neutral"  @selected(request('sentiment') === 'Neutral')>⚪ Neutral</option>
                    <option value="Negative" @selected(request('sentiment') === 'Negative')>🔴 Negative</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 transition text-sm font-semibold">
                Filter
            </button>
            <a href="{{ route('news.index') }}" class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">
                Clear
            </a>
        </div>
    </form>

    {{-- Article Cards --}}
    @if($articles->count())
    <div class="space-y-4">
        @foreach($articles as $article)
            @php
                $s     = $article->sentiment;
                $label = $s?->sentiment_label ?? 'Unanalyzed';
                $badgeClass = match($label) {
                    'Positive'   => 'bg-green-500/20 text-green-400 border border-green-500/30',
                    'Negative'   => 'bg-red-500/20 text-red-400 border border-red-500/30',
                    'Neutral'    => 'bg-slate-500/20 text-slate-400 border border-slate-500/30',
                    default      => 'bg-slate-700/50 text-slate-500 border border-slate-600/30',
                };
                $categoryColors = [
                    'logistics'   => 'bg-blue-500/20 text-blue-400',
                    'trade'       => 'bg-purple-500/20 text-purple-400',
                    'shipping'    => 'bg-cyan-500/20 text-cyan-400',
                    'economy'     => 'bg-orange-500/20 text-orange-400',
                    'geopolitics' => 'bg-rose-500/20 text-rose-400',
                ];
                $catClass = $categoryColors[strtolower($article->category ?? '')] ?? 'bg-slate-500/20 text-slate-400';
            @endphp
            <div class="glass rounded-2xl p-6 hover:border-slate-600 transition border border-transparent">
                <div class="flex items-start gap-5">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            @if($article->category)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $catClass }}">
                                    {{ ucfirst($article->category) }}
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                {{ $label }}
                            </span>
                            @if($s)
                                <span class="text-xs text-slate-500">
                                    +{{ $s->positive_count }} / -{{ $s->negative_count }}
                                </span>
                            @endif
                        </div>
                        <h3 class="font-bold text-base leading-snug mb-2">
                            @if($article->url)
                                <a href="{{ $article->url }}" target="_blank" rel="noopener"
                                   class="hover:text-blue-400 transition">{{ $article->title }}</a>
                            @else
                                {{ $article->title }}
                            @endif
                        </h3>
                        @if($article->content_snippet)
                            <p class="text-slate-400 text-sm leading-relaxed line-clamp-3">
                                {{ $article->content_snippet }}
                            </p>
                        @endif
                        <div class="flex items-center gap-4 mt-3 text-xs text-slate-500">
                            @if($article->source)
                                <span>📡 {{ $article->source }}</span>
                            @endif
                            @if($article->published_at)
                                <span>🕒 {{ $article->published_at->format('d M Y, H:i') }}</span>
                            @endif
                            @if($article->url)
                                <a href="{{ $article->url }}" target="_blank" rel="noopener"
                                   class="hover:text-blue-400 transition">↗ Read full article</a>
                            @endif
                        </div>
                    </div>

                    {{-- Sentiment Score Bar --}}
                    @if($s && ($s->positive_count + $s->negative_count) > 0)
                        @php
                            $tot = $s->positive_count + $s->negative_count;
                            $pct = round($s->positive_count / $tot * 100);
                        @endphp
                        <div class="flex-shrink-0 w-20 text-center">
                            <div class="text-3xl font-black {{ $label==='Positive'?'text-green-400':($label==='Negative'?'text-red-400':'text-slate-400') }}">
                                {{ $pct }}%
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">positive</div>
                            <div class="mt-2 h-16 w-4 bg-slate-700 rounded-full mx-auto relative overflow-hidden">
                                <div class="absolute bottom-0 w-full rounded-full {{ $label==='Positive'?'bg-green-500':($label==='Negative'?'bg-red-500':'bg-slate-500') }}"
                                     style="height:{{ $pct }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center">
        {{ $articles->links('vendor.pagination.tailwind') }}
    </div>

    @else
    <div class="glass rounded-2xl p-16 text-center">
        <div class="text-5xl mb-4">📭</div>
        <div class="text-xl font-semibold">No articles found</div>
        <p class="text-slate-400 mt-2">Try adjusting your filters or sync news data.</p>
    </div>
    @endif

</div>
@endsection
