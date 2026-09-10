<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\AccessControl\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('module')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.permissions.index', [
            'permissions' => $permissions,
        ]);
    }

    public function create(): View
    {
        return view('admin.permissions.form', ['permission' => new Permission]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug', 'alpha_dash'],
            'module' => ['required', 'string', 'max:100', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Permission::create($data);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil ditambahkan.');
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permissions.form', ['permission' => $permission]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,'.$permission->id],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug,'.$permission->id, 'alpha_dash'],
            'module' => ['required', 'string', 'max:100', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $permission->update($data);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }
}
