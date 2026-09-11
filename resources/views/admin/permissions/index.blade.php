@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Admin</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Manajemen Permission</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola permission atomik untuk kontrol akses granular.</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="btn-action-primary">
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
                            <td class="px-5 py-3.5 text-right space-x-1.5">
                                <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn-table-edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" class="inline" onsubmit="return confirm('Hapus permission ini?')">
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
