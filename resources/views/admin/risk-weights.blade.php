@extends('layouts.app')
@section('title', 'Admin — Risk Weights')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">⚙️ Risk Scoring Weights</h1>
            <p class="text-slate-400 mt-1">Adjust the contribution of each component to the total risk score. Weights must sum to 100.</p>
        </div>
        <a href="{{ route('admin.index') }}" class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">← Admin Home</a>
    </div>

    @if(session('success'))<div class="glass rounded-xl p-4 border border-green-500/30 text-green-400">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="glass rounded-xl p-4 border border-red-500/30 text-red-400">{{ session('error') }}</div>@endif

    <form method="POST" action="{{ route('admin.risk-weights.update') }}" id="weightsForm">
        @csrf
        <div class="glass rounded-2xl p-8">
            <div class="space-y-6">
                @foreach($weights as $w)
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="font-semibold capitalize">{{ $w->component_name }} Risk</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="weights[{{ $w->component_name }}]"
                                   id="w_{{ $w->component_name }}"
                                   value="{{ $w->weight_percentage }}"
                                   min="0" max="100" step="1"
                                   class="w-20 bg-slate-800 border border-slate-600 rounded-xl px-3 py-1.5 text-white text-sm text-center focus:outline-none focus:border-blue-500"
                                   oninput="updateTotal()">
                            <span class="text-slate-400">%</span>
                        </div>
                    </div>
                    <input type="range" min="0" max="100" value="{{ $w->weight_percentage }}"
                           class="w-full h-2 rounded-full appearance-none bg-slate-700 accent-blue-500"
                           oninput="document.getElementById('w_{{ $w->component_name }}').value=this.value; updateTotal()">
                </div>
                @endforeach
            </div>

            <div class="mt-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-slate-400">Total:</span>
                    <span id="totalDisplay" class="text-2xl font-black">
                        {{ $weights->sum('weight_percentage') }}%
                    </span>
                    <span id="totalStatus" class="text-sm {{ $weights->sum('weight_percentage') == 100 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $weights->sum('weight_percentage') == 100 ? '✓ Valid' : '✗ Must equal 100' }}
                    </span>
                </div>
                <button type="submit" id="submitBtn"
                    class="px-8 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ $weights->sum('weight_percentage') != 100 ? 'disabled' : '' }}>
                    Save Weights
                </button>
            </div>
        </div>
    </form>

    {{-- Explanation --}}
    <div class="glass rounded-2xl p-6">
        <h2 class="font-bold mb-3">How Risk Scoring Works</h2>
        <div class="text-sm text-slate-400 space-y-2">
            <p>Risk Score = (Weather × weather%) + (Economic × economic%) + (Currency × currency%) + (News × news%)</p>
            <p>Each component score is 0–100. The final weighted total is also 0–100.</p>
            <p><span class="text-green-400 font-semibold">Low Risk</span>: 0–34 &nbsp;|&nbsp; <span class="text-yellow-400 font-semibold">Medium Risk</span>: 35–64 &nbsp;|&nbsp; <span class="text-red-400 font-semibold">High Risk</span>: 65–100</p>
            <p>If 2+ components exceed 70, a 10% amplification factor is applied.</p>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function updateTotal() {
    const inputs = document.querySelectorAll('input[name^="weights["]');
    let total = 0;
    inputs.forEach(i => total += parseFloat(i.value) || 0);
    document.getElementById('totalDisplay').textContent = total + '%';
    const valid = Math.abs(total - 100) < 0.01;
    const status = document.getElementById('totalStatus');
    status.textContent = valid ? '✓ Valid' : '✗ Must equal 100';
    status.className = 'text-sm ' + (valid ? 'text-green-400' : 'text-red-400');
    document.getElementById('submitBtn').disabled = !valid;

    // Sync ranges
    inputs.forEach(i => {
        const comp = i.id.replace('w_','');
        const range = i.closest('div').nextElementSibling;
        if (range) range.value = i.value;
    });
}
</script>
@endpush
