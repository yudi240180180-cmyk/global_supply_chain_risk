@extends('layouts.app')
@section('title', 'Admin — Users')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">👥 User Management</h1>
        <a href="{{ route('admin.index') }}" class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 transition text-sm">← Admin Home</a>
    </div>

    @if(session('success'))<div class="glass rounded-xl p-4 border border-green-500/30 text-green-400">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="glass rounded-xl p-4 border border-red-500/30 text-red-400">{{ session('error') }}</div>@endif

    <div class="glass rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-800/70">
                <tr class="text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-5 py-4 text-left">User</th>
                    <th class="px-5 py-4 text-left">Email</th>
                    <th class="px-5 py-4 text-center">Role</th>
                    <th class="px-5 py-4 text-right">Joined</th>
                    <th class="px-5 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/40">
                @foreach($users as $u)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($u->name,0,1)) }}
                            </div>
                            <span class="font-semibold">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-slate-400">{{ $u->email }}</td>
                    <td class="px-5 py-4 text-center">
                        <form method="POST" action="{{ route('admin.users.role', $u->id) }}" class="inline-flex gap-2 items-center">
                            @csrf @method('POST')
                            <select name="role" class="bg-slate-800 border border-slate-600 rounded-lg px-2 py-1 text-xs text-white">
                                <option value="user"  @selected($u->role==='user')>user</option>
                                <option value="admin" @selected($u->role==='admin')>admin</option>
                            </select>
                            <button type="submit" class="px-2 py-1 rounded-lg bg-blue-600/30 hover:bg-blue-600/60 text-blue-400 text-xs transition">Save</button>
                        </form>
                    </td>
                    <td class="px-5 py-4 text-right text-slate-400 text-xs">{{ $u->created_at?->format('d M Y') }}</td>
                    <td class="px-5 py-4 text-center">
                        @if($u->role !== 'admin')
                        <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}"
                              onsubmit="return confirm('Delete {{ $u->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-400 text-xs transition">Delete</button>
                        </form>
                        @else
                        <span class="text-xs text-slate-500">Protected</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>{{ $users->links('vendor.pagination.tailwind') }}</div>
</div>
@endsection
