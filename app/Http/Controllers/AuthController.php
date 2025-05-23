<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                $user = Auth::user();
                $role = $user->role;
                return response()->json(['status' => 'success', 'message' => 'Login berhasil', 'role' => $role]);
            }

            return response()->json(['status' => 'error', 'message' => 'Username atau password salah'], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'username' => 'required|max:255',
                'password' => 'required',
                'nama' => 'required',
                'nohp' => 'required|numeric',
                'alamat' => 'required',
            ]);
            $user = User::create([
                'username' => $validatedData['username'],
                'password' => Hash::make($validatedData['password']),
                'nama' => $validatedData['nama'],
                'nohp' => $validatedData['nohp'],
                'alamat' => $validatedData['alamat'],
                'level' => 'Pembeli',
            ]);
            Auth::login($user);
            return response()->json(['status' => 'success', 'message' => 'Registrasi berhasil']);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('status', 'Anda telah berhasil logout');
    }
}
 