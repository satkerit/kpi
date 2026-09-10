@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Admin</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Manajemen Role</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola peran akses dan permission yang melekat.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 bg-navy text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-navy-deep transition shadow-card">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Role
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3">Nama Role</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3 text-center">Permission</th>
                        <th class="px-4 py-3 text-center">User</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $role)
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-3.5 font-bold text-ink">{{ $role->name }}</td>
                            <td class="px-4 py-3.5"><span class="font-mono text-xs text-ink-soft">{{ $role->slug }}</span></td>
                            <td class="px-4 py-3.5 text-ink-muted text-xs max-w-xs truncate">{{ $role->description ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center num font-semibold text-ink-soft">{{ $role->permissions_count }}</td>
                            <td class="px-4 py-3.5 text-center num font-semibold text-ink-soft">{{ $role->users_count }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 {{ $role->is_system ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' }}">
                                    {{ $role->is_system ? 'Sistem' : 'Kustom' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="text-xs font-semibold text-navy hover:underline">Edit</a>
                                @if(!$role->is_system)
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Hapus role ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                    </form>
                                @else
                                    <span class="text-xs font-semibold text-gray-400 cursor-not-allowed">Tidak bisa dihapus</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-ink-muted">Belum ada data role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $roles->links() }}</div>
    </div>
</main>
@endsection
