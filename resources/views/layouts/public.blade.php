<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GSCR — Watchlist')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        html, body {
            margin: 0; padding: 0;
            background: #0f172a;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
        }
        .glass {
            background: rgba(255,255,255,.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.08);
            transition: .3s;
        }
        .glass:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0,0,0,.3);
        }
        .scrollbar::-webkit-scrollbar { width: 6px; }
        .scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen">

    {{-- Public Top Nav --}}
    <nav class="sticky top-0 z-40 glass border-b border-slate-700/50 h-16 flex items-center justify-between px-8">
        <div class="flex items-center gap-4">
            <a href="/" class="flex items-center gap-2 font-black text-xl">
                🌎 <span>GSCR</span>
            </a>
            <span class="text-slate-600">|</span>
            <span class="text-slate-400 text-sm">⭐ Watchlist Monitor</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-slate-400 text-xs">Public View — No login required</span>
            @if(session('auth_user_id'))
                <a href="{{ url('/') }}" class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 transition text-sm font-semibold">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <form method="POST" action="{{ route('auth.logout') }}" class="inline">
                    @csrf
                    <button class="px-4 py-1.5 rounded-xl glass text-slate-400 hover:text-red-400 transition text-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 transition text-sm font-semibold">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            @endif
        </div>
    </nav>

    <main class="p-8 max-w-screen-2xl mx-auto">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
