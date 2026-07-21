@extends('layouts.manager')

@section('title', 'Suppliers')
@section('page-title', 'Supplier Directory')
@section('page-desc', 'Evaluate registered supplier entities, corporate ratings, risk rankings, and historical delivery counts.')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="bi bi-people text-violet-400"></i> Corporate Partners
        </h2>
    </div>

    {{-- Table list --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-white/[0.02] border-b border-slate-800 text-slate-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="p-4">Company Name</th>
                        <th>Origin Country</th>
                        <th>Type</th>
                        <th>Rating</th>
                        <th>Risk Profile</th>
                        <th>Total Shipments</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-white/[0.01] transition">
                        <td class="p-4 font-bold text-white">
                            {{ $supplier->company_name }}
                            <span class="block text-[10px] text-slate-500 font-normal">Contact: {{ $supplier->contact_person }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2 text-white">
                                <span class="text-lg">{{ $supplier->country->flag ?? '🌍' }}</span>
                                <span class="font-medium">{{ $supplier->country->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-xs bg-slate-900 border border-slate-800 rounded px-2.5 py-0.5 text-slate-300">
                                {{ $supplier->supplier_type }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-0.5 text-amber-400">
                                
    @for($i = 1; $i <= 5; $i++)
        @if($i <= round($supplier->rating))
            <i class="bi bi-star-fill text-xs"></i>
        @else
            <i class="bi bi-star text-xs text-slate-600"></i>
        @endif
    @endfor
    <span class="text-xs text-slate-400 ml-1 font-semibold">
        ({{ number_format($supplier->rating, 1) }})
    </span>
</div>
                        </td>
                        <td>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold badge-{{ strtolower($supplier->risk_level) }}">
                                {{ $supplier->risk_level }} Risk
                            </span>
                        </td>
                        <td>
                            <span class="font-semibold text-white">{{ $supplier->shipments_count }}</span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('manager.suppliers.show', $supplier) }}" class="btn-secondary py-1.5 px-3 text-xs">
                                <i class="bi bi-folder2-open"></i> View Profile
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            No supplier partners defined in directory.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="p-4 border-t border-slate-800 bg-white/[0.01]">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
