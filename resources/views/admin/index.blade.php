@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold">🛡️ Admin Dashboard</h1>
            <p class="text-slate-400 mt-1">Manage platform data, users, and risk configuration</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
        @foreach([
            ['Users',     $stats['users'],     'bi-people-fill',    'blue'],
            ['Countries', $stats['countries'], 'bi-globe2',         'green'],
            ['Ports',     $stats['ports'],     'bi-geo-alt-fill',   'cyan'],
            ['Articles',  $stats['articles'],  'bi-journal-text',   'purple'],
            ['News',      $stats['news'],      'bi-newspaper',      'orange'],
            ['Risk Scores',$stats['risks'],    'bi-exclamation-triangle-fill','red'],
        ] as [$label, $val, $icon, $color])
        <div class="glass rounded-2xl p-5 text-center">
            <i class="bi {{ $icon }} text-2xl text-{{ $color }}-400"></i>
            <div class="text-3xl font-black mt-2">{{ number_format($val) }}</div>
            <div class="text-slate-400 text-sm mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- Nav Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <a href="{{ route('admin.users') }}"
           class="glass rounded-2xl p-6 hover:border-blue-500/50 border border-transparent transition group">
            <i class="bi bi-people-fill text-3xl text-blue-400"></i>
            <div class="font-bold text-lg mt-3">User Management</div>
            <div class="text-slate-400 text-sm mt-1">Manage roles & access</div>
            <div class="text-blue-400 text-sm mt-3 group-hover:translate-x-1 transition-transform">→ Open</div>
        </a>
        <a href="{{ route('admin.ports') }}"
           class="glass rounded-2xl p-6 hover:border-cyan-500/50 border border-transparent transition group">
            <i class="bi bi-geo-alt-fill text-3xl text-cyan-400"></i>
            <div class="font-bold text-lg mt-3">Port Dataset</div>
            <div class="text-slate-400 text-sm mt-1">Manage port records</div>
            <div class="text-cyan-400 text-sm mt-3 group-hover:translate-x-1 transition-transform">→ Open</div>
        </a>
        <a href="{{ route('admin.articles') }}"
           class="glass rounded-2xl p-6 hover:border-purple-500/50 border border-transparent transition group">
            <i class="bi bi-journal-text text-3xl text-purple-400"></i>
            <div class="font-bold text-lg mt-3">Analysis Articles</div>
            <div class="text-slate-400 text-sm mt-1">Publish & manage articles</div>
            <div class="text-purple-400 text-sm mt-3 group-hover:translate-x-1 transition-transform">→ Open</div>
        </a>
        <a href="{{ route('admin.risk-weights') }}"
           class="glass rounded-2xl p-6 hover:border-yellow-500/50 border border-transparent transition group">
            <i class="bi bi-sliders text-3xl text-yellow-400"></i>
            <div class="font-bold text-lg mt-3">Risk Weights</div>
            <div class="text-slate-400 text-sm mt-1">Tune scoring algorithm</div>
            <div class="text-yellow-400 text-sm mt-3 group-hover:translate-x-1 transition-transform">→ Open</div>
        </a>
    </div>

    {{-- Recent Users --}}
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold">👥 Recent Users</h2>
            <a href="{{ route('admin.users') }}" class="text-blue-400 text-sm hover:underline">View all →</a>
        </div>
        <table class="w-full text-sm">
            <thead><tr class="text-slate-400 text-xs uppercase border-b border-slate-700">
                <th class="py-2 text-left">Name</th><th class="py-2 text-left">Email</th>
                <th class="py-2 text-center">Role</th><th class="py-2 text-right">Joined</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-700/40">
                @foreach($recentUsers as $u)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="py-3 font-semibold">{{ $u->name }}</td>
                    <td class="py-3 text-slate-400">{{ $u->email }}</td>
                    <td class="py-3 text-center">
                        <span class="px-2.5 py-0.5 rounded-full text-xs {{ $u->role==='admin'?'bg-red-500/20 text-red-400':'bg-blue-500/20 text-blue-400' }}">
                            {{ $u->role }}
                        </span>
                    </td>
                    <td class="py-3 text-right text-slate-400">{{ $u->created_at?->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="glass rounded-xl p-4 border border-green-500/30 text-green-400">{{ session('success') }}</div>
    @endif

</div>
@endsection
