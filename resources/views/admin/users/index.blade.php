@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Admin</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Manajemen User</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola akun login user dan peran aksesnya.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-action-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah User Baru
        </a>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-4 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau Email"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Role</label>
                <select name="role" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" {{ request('role') == $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full btn-action-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter Data
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-3.5 font-bold text-ink">{{ $user->name }}</td>
                            <td class="px-4 py-3.5 text-ink-soft text-xs">{{ $user->email }}</td>
                            <td class="px-4 py-3.5">
                                @if($user->roles->count())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gold-soft text-gold-deep">{{ $role->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-ink-muted text-[11px]">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1.5">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-table-edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus akun user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-table-delete">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-ink-muted">Belum ada data user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $users->links() }}</div>
    </div>
</main>
@endsection
