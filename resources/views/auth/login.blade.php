<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Platform Risiko Rantai Pasok Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #020817; overflow-x: hidden; }

        .bg-grid {
            background-image:
                linear-gradient(rgba(99,102,241,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,.05) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        .glass {
            background: rgba(255,255,255,.04);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.07);
        }

        .card-hover {
            transition: all .25s ease;
            cursor: pointer;
        }
        .card-hover:hover {
            background: rgba(99,102,241,.15);
            border-color: rgba(99,102,241,.5);
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(99,102,241,.2);
        }
        .card-hover.selected {
            background: rgba(99,102,241,.25);
            border-color: rgba(99,102,241,.8);
            box-shadow: 0 0 0 2px rgba(99,102,241,.5);
        }

        .glow-blue { box-shadow: 0 0 80px rgba(99,102,241,.25); }
        .glow-violet { box-shadow: 0 0 60px rgba(139,92,246,.2); }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .35;
            pointer-events: none;
        }

        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50%      { transform: translateY(-12px); }
        }
        .float { animation: float 4s ease-in-out infinite; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp .5s ease forwards; }
        .fade-up-1 { animation-delay:.1s; opacity:0; }
        .fade-up-2 { animation-delay:.2s; opacity:0; }
        .fade-up-3 { animation-delay:.3s; opacity:0; }
        .fade-up-4 { animation-delay:.4s; opacity:0; }

        .risk-badge-low    { background: rgba(16,185,129,.15); color: #10b981; border: 1px solid rgba(16,185,129,.3); }
        .risk-badge-medium { background: rgba(245,158,11,.15); color: #f59e0b; border: 1px solid rgba(245,158,11,.3); }
        .risk-badge-high   { background: rgba(239,68,68,.15);  color: #ef4444; border: 1px solid rgba(239,68,68,.3); }

        .btn-login {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            transition: all .2s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99,102,241,.4);
        }
        .btn-login:active { transform: translateY(0); }
    </style>
</head>
<body class="min-h-screen bg-grid text-white flex items-center justify-center p-6">

    {{-- Ambient orbs --}}
    <div class="orb w-96 h-96 bg-indigo-600 top-[-100px] left-[-100px]"></div>
    <div class="orb w-80 h-80 bg-violet-600 bottom-[-80px] right-[-80px]"></div>
    <div class="orb w-64 h-64 bg-blue-600 top-1/2 left-1/3"></div>

    <div class="w-full max-w-5xl relative z-10">

        {{-- Header --}}
        <div class="text-center mb-10 fade-up fade-up-1">
            <div class="float inline-block text-6xl mb-4">🌎</div>
            <h1 class="text-4xl font-black tracking-tight bg-gradient-to-r from-white via-indigo-200 to-violet-300 bg-clip-text text-transparent">
                Risiko Rantai Pasok Global
            </h1>
            <p class="text-slate-400 mt-2 text-lg">Sistem Pendukung Keputusan (DSS) · Pilih akun untuk melanjutkan</p>
        </div>

        @if(session('error'))
            <div class="mb-6 glass rounded-xl p-4 border border-red-500/30 text-red-400 text-center fade-up fade-up-1">
                <i class="bi bi-exclamation-triangle-fill mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('auth.login') }}" id="loginForm">
            @csrf
            <input type="hidden" name="user_id" id="selectedUserId" value="">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- Admin Section --}}
                <div class="fade-up fade-up-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-blue-600/20 border border-blue-500/30 flex items-center justify-center">
                            <i class="bi bi-shield-lock-fill text-blue-400 text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-white font-bold">Administrator Sistem</h2>
                            <p class="text-slate-500 text-xs">Kendali penuh & pemantauan sistem</p>
                        </div>
                    </div>

                    @foreach($admins as $admin)
                    <div class="glass rounded-2xl p-5 card-hover mb-3 border border-white/5"
                         onclick="selectUser({{ $admin->id }}, this)"
                         data-id="{{ $admin->id }}">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-600/20 flex items-center justify-center text-2xl border border-blue-500/20">
                                {{ $admin->avatar_icon ?? '👑' }}
                            </div>
                            <div class="flex-1">
                                <div class="font-bold text-white">{{ $admin->name }}</div>
                                <div class="text-slate-400 text-sm">{{ $admin->email }}</div>
                                <div class="mt-1">
                                    <span class="text-xs bg-blue-600/20 text-blue-300 border border-blue-500/20 px-2 py-0.5 rounded-full">
                                        <i class="bi bi-shield-fill mr-1"></i>Administrator
                                    </span>
                                </div>
                            </div>
                            <div class="w-6 h-6 rounded-full border-2 border-slate-600 flex items-center justify-center check-indicator">
                                <i class="bi bi-check-lg text-white text-xs hidden"></i>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Admin Features --}}
                    <div class="glass rounded-xl p-4 border border-white/5 mt-4">
                        <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-3">Akses Admin</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['Kelola Negara','Kelola Pelabuhan','Kelola User','Kelola Berita','Bobot Risiko','Monitor API'] as $f)
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <i class="bi bi-check-circle-fill text-blue-400 text-xs"></i>{{ $f }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Import Manager Section --}}
                <div class="fade-up fade-up-3">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-violet-600/20 border border-violet-500/30 flex items-center justify-center">
                            <i class="bi bi-briefcase-fill text-violet-400 text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-white font-bold">Import Manager (User Perusahaan)</h2>
                            <p class="text-slate-500 text-xs">Perencanaan pengiriman & analisis risiko</p>
                        </div>
                    </div>

                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1" style="scrollbar-width:thin;scrollbar-color:#475569 transparent;">
                        @foreach($managers as $manager)
                        <div class="glass rounded-2xl p-4 card-hover border border-white/5"
                             onclick="selectUser({{ $manager->id }}, this)"
                             data-id="{{ $manager->id }}">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-violet-600/20 flex items-center justify-center text-xl border border-violet-500/20">
                                    {{ $manager->avatar_icon ?? '🏢' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-white truncate">{{ $manager->company_name ?? $manager->name }}</div>
                                    <div class="text-slate-400 text-xs">{{ $manager->name }}</div>
                                    <div class="mt-1">
                                        @php
                                            $supplier = \App\Models\Supplier::where('company_name', $manager->company_name)->first();
                                        @endphp
                                        @if($supplier)
                                            <span class="text-xs px-2 py-0.5 rounded-full
                                                @if($supplier->risk_level === 'Low') risk-badge-low
                                                @elseif($supplier->risk_level === 'Medium') risk-badge-medium
                                                @else risk-badge-high @endif">
                                                Risiko {{ $supplier->risk_level === 'Low' ? 'Rendah' : ($supplier->risk_level === 'Medium' ? 'Sedang' : 'Tinggi') }}
                                            </span>
                                        @endif
                                        <span class="text-xs bg-violet-600/20 text-violet-300 border border-violet-500/20 px-2 py-0.5 rounded-full ml-1">
                                            Import Manager
                                        </span>
                                    </div>
                                </div>
                                <div class="w-6 h-6 rounded-full border-2 border-slate-600 flex items-center justify-center check-indicator flex-shrink-0">
                                    <i class="bi bi-check-lg text-white text-xs hidden"></i>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Manager Features --}}
                    <div class="glass rounded-xl p-4 border border-white/5 mt-4">
                        <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-3">Akses Manager</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['Rencana Impor','Rekomendasi Rute','Pelacakan Status','Purchase Order','Estimasi Biaya','Analisis Risiko'] as $f)
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <i class="bi bi-check-circle-fill text-violet-400 text-xs"></i>{{ $f }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- Login Button --}}
            <div class="mt-8 text-center fade-up fade-up-4">
                <button type="submit" id="loginBtn"
                    class="btn-login px-12 py-4 rounded-2xl text-white font-bold text-lg disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none"
                    disabled>
                    <i class="bi bi-box-arrow-in-right mr-2"></i>
                    <span id="loginBtnText">Pilih akun untuk masuk</span>
                </button>
                <p class="text-slate-600 text-xs mt-3">Masuk tanpa password · Lingkungan Demo</p>
            </div>

        </form>
    </div>

    <script>
        let selectedId = null;

        function selectUser(id, el) {
            // Reset semua
            document.querySelectorAll('.card-hover').forEach(c => {
                c.classList.remove('selected');
                c.querySelector('.check-indicator i').classList.add('hidden');
                c.querySelector('.check-indicator').style.background = '';
                c.querySelector('.check-indicator').style.borderColor = '';
            });

            // Pilih yang ini
            el.classList.add('selected');
            const indicator = el.querySelector('.check-indicator');
            indicator.style.background = '#6366f1';
            indicator.style.borderColor = '#6366f1';
            indicator.querySelector('i').classList.remove('hidden');

            // Update form
            selectedId = id;
            document.getElementById('selectedUserId').value = id;

            // Enable button
            const btn = document.getElementById('loginBtn');
            btn.removeAttribute('disabled');

            // Update button text
            const name = el.querySelector('.font-bold, .font-semibold')?.textContent?.trim() || 'Akun Terpilih';
            document.getElementById('loginBtnText').textContent = `Masuk sebagai ${name}`;
        }
    </script>

</body>
</html>
