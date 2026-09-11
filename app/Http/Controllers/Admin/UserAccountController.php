<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\AccessControl\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class UserAccountController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('roles:id,name,slug')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = (string) $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $q->whereHas('roles', fn ($r) => $r->where('slug', $request->string('role')));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug', 'description']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,slug'],
        ]);

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);
        $user->syncRoles($data['roles']);

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun user baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user->load('roles:slug'),
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug', 'description']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,slug'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($data['roles']);

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun user berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun user berhasil dihapus.');
    }
}
