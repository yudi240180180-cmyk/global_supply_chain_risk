<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login (pilih role/company)
     */
    public function showLogin()
    {
        // Jika sudah login, redirect ke portal yang sesuai
        if (session('auth_user_id')) {
            $user = User::find(session('auth_user_id'));
            if ($user) {
                return redirect($user->portal_route);
            }
        }

        $managers = User::where('role', 'import_manager')
            ->orderBy('company_name')
            ->get();

        $admins = User::where('role', 'admin')->get();

        return view('auth.login', compact('managers', 'admins'));
    }

    /**
     * Proses login (session-based, tanpa password)
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Simpan ke session
        session([
            'auth_user_id'   => $user->id,
            'auth_user_name' => $user->name,
            'auth_user_role' => $user->role,
            'auth_company'   => $user->company_name,
            'auth_icon'      => $user->avatar_icon,
        ]);

        if ($user->isAdmin()) {
            return redirect('/')->with('success', "Welcome back, {$user->name}!");
        }

        return redirect('/manager')->with('success', "Welcome, {$user->company_name}!");
    }

    /**
     * Logout
     */
    public function logout()
    {
        session()->forget([
            'auth_user_id',
            'auth_user_name',
            'auth_user_role',
            'auth_company',
            'auth_icon',
        ]);

        return redirect('/login')->with('success', 'Logged out successfully.');
    }
}
