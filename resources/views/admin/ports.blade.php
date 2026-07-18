@extends('layouts.app')
@section('title', 'Admin — Ports')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">🚢 Port Dataset Management</h1>
        <a href="{{ route('admin.index') }}" class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">← Admin Home</a>
    </div>

    @if(session('success'))<div class="glass rounded-xl p-4 border border-green-500/30 text-green-400">{{ session('success') }}</div>@endif

    <div class="glass rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700/50">
            <input id="portAdminSearch" type="text" placeholder="Search ports…"
                class="bg-slate-800 border border-slate-600 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-blue-500 w-72">
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/70">
                    <tr class="text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Port</th>
                        <th class="px-5 py-3 text-left">Country</th>
                        <th class="px-5 py-3">LOCODE</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3 text-right">Outflows</th>
                        <th class="px-5 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40" id="portAdminBody">
                    @foreach($ports as $p)
                    <tr class="port-admin-row hover:bg-slate-800/30 transition" data-name="{{ strtolower($p->name) }}">
                        <td class="px-5 py-3 font-semibold">{{ $p->name }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ optional($p->country)->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-center font-mono text-xs">{{ $p->locode ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-slate-400">{{ $p->port_type ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format($p->outflows ?? 0) }}</td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('ports.show', $p->id) }}" class="px-3 py-1 rounded-lg bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 text-xs">View</a>
                                <form method="POST" action="{{ route('admin.ports.destroy', $p->id) }}"
                                      onsubmit="return confirm('Delete {{ $p->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 text-xs">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $ports->links('vendor.pagination.tailwind') }}</div>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('portAdminSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.port-admin-row').forEach(r => {
        r.style.display = r.dataset.name.includes(q) ? '' : 'none';
    });
});
</script>
@endpush
