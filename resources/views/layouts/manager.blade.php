<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Import Manager') — GSCR Platform</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <style>
        * { font-family: 'Inter', sans-serif; }

        html, body {
            margin: 0; padding: 0;
            background: #020817;
            color: #fff;
        }

        /* Sidebar */
        .manager-sidebar {
            width: 260px;
            background: rgba(255,255,255,.03);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255,255,255,.06);
            position: fixed;
            left: 0; top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 50;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 500;
            color: #94a3b8;
            transition: all .2s;
            text-decoration: none;
        }
        .nav-item:hover {
            background: rgba(139,92,246,.1);
            color: #c4b5fd;
        }
        .nav-item.active {
            background: linear-gradient(135deg, rgba(139,92,246,.2), rgba(99,102,241,.15));
            color: #c4b5fd;
            border: 1px solid rgba(139,92,246,.25);
        }
        .nav-item .icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .nav-item:hover .icon,
        .nav-item.active .icon {
            background: rgba(139,92,246,.2);
        }

        /* Main content */
        .manager-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* Topbar */
        .manager-topbar {
            background: rgba(255,255,255,.02);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,.05);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        /* Glass cards */
        .glass {
            background: rgba(255,255,255,.04);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.07);
        }

        /* Risk badges */
        .badge-low    { background:rgba(16,185,129,.15); color:#10b981; border:1px solid rgba(16,185,129,.3); }
        .badge-medium { background:rgba(245,158,11,.15); color:#f59e0b; border:1px solid rgba(245,158,11,.3); }
        .badge-high   { background:rgba(239,68,68,.15);  color:#ef4444; border:1px solid rgba(239,68,68,.3); }

        /* Status badges */
        .status-planning  { background:rgba(100,116,139,.15); color:#94a3b8; border:1px solid rgba(100,116,139,.3); }
        .status-ready     { background:rgba(59,130,246,.15);  color:#60a5fa; border:1px solid rgba(59,130,246,.3); }
        .status-loading   { background:rgba(245,158,11,.15);  color:#fbbf24; border:1px solid rgba(245,158,11,.3); }
        .status-departed  { background:rgba(139,92,246,.15);  color:#a78bfa; border:1px solid rgba(139,92,246,.3); }
        .status-at-sea    { background:rgba(6,182,212,.15);   color:#22d3ee; border:1px solid rgba(6,182,212,.3); }
        .status-arrived   { background:rgba(16,185,129,.15);  color:#34d399; border:1px solid rgba(16,185,129,.3); }
        .status-completed { background:rgba(16,185,129,.25);  color:#10b981; border:1px solid rgba(16,185,129,.4); }
        .status-delayed   { background:rgba(239,68,68,.15);   color:#f87171; border:1px solid rgba(239,68,68,.3); }
        .status-cancelled { background:rgba(239,68,68,.1);    color:#9ca3af; border:1px solid rgba(239,68,68,.2); }

        .scrollbar::-webkit-scrollbar { width: 4px; }
        .scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

        /* Form inputs */
        .form-input {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            width: 100%;
            font-size: 14px;
            transition: border-color .2s;
        }
        .form-input:focus {
            outline: none;
            border-color: rgba(139,92,246,.6);
            background: rgba(139,92,246,.05);
        }
        .form-input option { background: #1e293b; color: #fff; }

        .form-label {
            display: block;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(99,102,241,.35);
        }

        .btn-secondary {
            background: rgba(255,255,255,.06);
            color: #94a3b8;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            border: 1px solid rgba(255,255,255,.08);
            cursor: pointer;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
        }

        canvas { max-width: 100%; }
        .leaflet-container { background: #020817; }
    </style>

    @stack('head')
</head>
<body>

{{-- Sidebar --}}
@include('components.manager-sidebar')

{{-- Main Content --}}
<div class="manager-content">

    {{-- Topbar --}}
    <div class="manager-topbar">
        <div>
            <h1 class="text-lg font-bold text-white">@yield('page-title', 'Dashboard')</h1>
            <p class="text-slate-500 text-xs">@yield('page-desc', 'Import Manager Portal')</p>
        </div>

        <div class="flex items-center gap-4">
            {{-- Company badge --}}
            <div class="glass rounded-xl px-4 py-2 flex items-center gap-3">
                <div class="text-xl">{{ session('auth_icon', '🏢') }}</div>
                <div>
                    <div class="text-white text-sm font-semibold">{{ session('auth_company', session('auth_user_name')) }}</div>
                    <div class="text-slate-500 text-xs">Import Manager</div>
                </div>
            </div>

            {{-- Logout --}}
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="glass rounded-xl px-3 py-2 text-slate-400 hover:text-red-400 transition text-sm flex items-center gap-2">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mx-8 mt-4 glass rounded-xl p-4 border border-emerald-500/30 text-emerald-400 flex items-center gap-3">
        <i class="bi bi-check-circle-fill text-xl"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mx-8 mt-4 glass rounded-xl p-4 border border-red-500/30 text-red-400 flex items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill text-xl"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <main class="p-8">
        @yield('content')
    </main>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@stack('scripts')

</body>
</html>
