<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.user.index', compact('users'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'password' => 'required|string|min:6',
                'alamat' => 'nullable|string',
                'nohp' => 'nullable|string|max:15',
                'role' => 'required|in:admin,pembeli'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.user.index')
                ->withErrors($e->validator)
                ->withInput()
                ->with('modal_add_error', true);
        }

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'alamat' => $request->alamat,
            'nohp' => $request->nohp,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $id,
                'password' => 'nullable|string|min:6',
                'alamat' => 'nullable|string',
                'nohp' => 'nullable|string|max:15',
                'role' => 'required|in:admin,pembeli'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.user.index')
                ->withErrors($e->validator)
                ->withInput()
                ->with('modal_edit_error', $id);
        }

        $updateData = [
            'nama' => $request->nama,
            'username' => $request->username,
            'alamat' => $request->alamat,
            'nohp' => $request->nohp,
            'role' => $request->role,
        ];

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Cegah penghapusan user yang sedang login
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.user.index')->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        $user->delete();
        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }
}
