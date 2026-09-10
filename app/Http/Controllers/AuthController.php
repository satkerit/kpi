<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\HumanResource\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AuthController extends Controller
{
    /**
     * Registrasi pegawai + akun user, mengembalikan token Sanctum.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'nik' => ['required', 'string', 'unique:employees,nik'],
            'phone' => ['nullable', 'string', 'max:30'],
            'office_id' => ['nullable', 'integer', 'exists:offices,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'direct_supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $employee = Employee::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'nik' => $data['nik'],
            'phone' => $data['phone'] ?? null,
            'office_id' => $data['office_id'] ?? null,
            'division_id' => $data['division_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'direct_supervisor_id' => $data['direct_supervisor_id'] ?? null,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(['employee' => $employee, 'token' => $token], 201);
    }

    /**
     * Login via email+password, mengembalikan token Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Kredensial tidak valid.'], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    /**
     * Logout: hapus token saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }
}
