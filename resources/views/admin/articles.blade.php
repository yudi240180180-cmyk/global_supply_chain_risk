@extends('layouts.app')
@section('title', 'Admin — Articles')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">📝 Analysis Articles</h1>
        <a href="{{ route('admin.index') }}" class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">← Admin Home</a>
    </div>

    @if(session('success'))<div class="glass rounded-xl p-4 border border-green-500/30 text-green-400">{{ session('success') }}</div>@endif

    {{-- Create Form --}}
    <div class="glass rounded-2xl p-6">
        <h2 class="text-lg font-bold mb-5">Create New Article</h2>
        <form method="POST" action="{{ route('admin.articles.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1">Title</label>
                <input name="title" type="text" required placeholder="Article title…"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Content</label>
                <textarea name="content" rows="6" required placeholder="Write your analysis…"
                    class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Publish Date (optional)</label>
                <input name="published_at" type="datetime-local"
                    class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 transition font-semibold">
                Publish Article
            </button>
        </form>
    </div>

    {{-- Articles List --}}
    <div class="space-y-4">
        @forelse($articles as $art)
        <div class="glass rounded-2xl p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-base">{{ $art->title }}</h3>
                    <div class="text-xs text-slate-500 mt-1">
                        By {{ optional($art->author)->name ?? 'Admin' }} ·
                        {{ optional($art->published_at)->format('d M Y H:i') ?? 'Draft' }}
                    </div>
                    <p class="text-slate-400 text-sm mt-2 line-clamp-3">{{ $art->content }}</p>
                </div>
                <form method="POST" action="{{ route('admin.articles.destroy', $art->id) }}"
                      onsubmit="return confirm('Delete this article?')" class="flex-shrink-0">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 text-xs transition">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="glass rounded-2xl p-12 text-center text-slate-400">No articles yet. Create one above.</div>
        @endforelse
    </div>
    <div>{{ $articles->links('vendor.pagination.tailwind') }}</div>
</div>
@endsection
