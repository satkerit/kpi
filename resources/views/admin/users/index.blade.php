@extends('layouts.app')

@section('content')
<main class="flex-1 px-6 lg:px-10 py-8">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Admin</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Manajemen User</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola akun login user dan peran aksesnya.</p>
        </div>
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
                <button type="submit" class="w-full px-4 py-2.5 bg-navy text-white rounded-xl text-sm font-semibold hover:bg-navy-deep transition">
                    Filter
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
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-xs font-semibold text-navy hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus akun user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
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