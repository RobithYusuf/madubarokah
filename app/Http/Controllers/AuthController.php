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
        // Redirect jika sudah login
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            // Check remember me option
            $remember = $request->boolean('remember');

            if (Auth::attempt($credentials, $remember)) {
                $request->session()->regenerate();
                $user = Auth::user();

                // Get intended URL or default redirect
                $redirectUrl = $this->getRedirectUrl($user);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login berhasil',
                    'redirect_url' => $redirectUrl,
                    'role' => strtolower($user->role ?? $user->level)
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah'
            ], 422);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }

    public function showRegisterForm()
    {
        // Redirect jika sudah login
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required|string|min:6',
                'nama' => 'required|string|max:255',
                'nohp' => 'required|string|max:15',
                'alamat' => 'required|string',
            ]);

            $user = User::create([
                'username' => $validatedData['username'],
                'password' => Hash::make($validatedData['password']),
                'nama' => $validatedData['nama'],
                'nohp' => $validatedData['nohp'],
                'alamat' => $validatedData['alamat'],
                'role' => 'pembeli', // atau 'level' => 'Pembeli' sesuai dengan struktur database
            ]);

            Auth::login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Registrasi berhasil',
                'redirect_url' => '/'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat registrasi'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Anda telah berhasil logout');
    }

    /**
     * Redirect berdasarkan role user
     */
    private function redirectBasedOnRole($user)
    {
        $role = strtolower($user->role ?? $user->level);

        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'pembeli':
                return redirect()->route('frontend.home');
            default:
                return redirect('/');
        }
    }

    /**
     * Get redirect URL based on intended URL or user role
     */
    private function getRedirectUrl($user)
    {
        // Check if there's an intended URL
        $intended = session()->pull('url.intended');
        if ($intended) {
            return $intended;
        }

        // Default redirect based on role
        $role = strtolower($user->role ?? $user->level);

        switch ($role) {
            case 'admin':
                return route('admin.dashboard');
            case 'pembeli':
                return route('frontend.home');
            default:
                return '/';
        }
    }
}
