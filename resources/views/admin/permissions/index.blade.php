@extends('layouts.app')

@section('content')
<main class="flex-1 px-6 lg:px-10 py-8">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Admin</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Manajemen Permission</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola permission atomik untuk kontrol akses granular.</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="inline-flex items-center gap-2 bg-navy text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-navy-deep transition shadow-card">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Permission
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3">Nama Permission</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3">Modul</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3 text-center">Role</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($permissions as $permission)
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-3.5 font-bold text-ink">{{ $permission->name }}</td>
                            <td class="px-4 py-3.5"><span class="font-mono text-xs text-ink-soft">{{ $permission->slug }}</span></td>
                            <td class="px-4 py-3.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-navy-tint text-navy">{{ $permission->module }}</span></td>
                            <td class="px-4 py-3.5 text-ink-muted text-xs max-w-xs truncate">{{ $permission->description ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center num font-semibold text-ink-soft">{{ $permission->roles_count }}</td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.permissions.edit', $permission) }}" class="text-xs font-semibold text-navy hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" class="inline" onsubmit="return confirm('Hapus permission ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-ink-muted">Belum ada data permission.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $permissions->links() }}</div>
    </div>
</main>
@endsection
