<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::with('kategori')->get();
        $kategoris = Kategori::all();
        return view('admin.produk.index', compact('produks', 'kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required',
            'id_kategori' => 'nullable|exists:kategori,id',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'berat' => 'required|integer|min:1',
            'deskripsi' => 'nullable',
            'gambar' => 'nullable|image'
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('produk', 'public'); // Simpan ke storage/app/public/produk
        }

        Produk::create([
            'nama_produk' => $request->nama_produk,
            'id_kategori' => $request->id_kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'berat' => $request->berat,
            'deskripsi' => $request->deskripsi,
            'gambar' => $gambarPath,
        ]);
        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        // Validasi dulu
        $request->validate([
            'nama_produk' => 'required',
            'id_kategori' => 'nullable|exists:kategori,id',
            'harga' => 'required',
            'stok' => 'required|integer',
            'berat' => 'required|integer|min:1',
            'deskripsi' => 'nullable',
            'gambar' => 'nullable|image'
        ]);

        // Ambil data produk berdasarkan ID
        $produk = Produk::findOrFail($id);

        // Konversi harga misal 65.000 menjadi 65000
        $harga = str_replace(['.', ','], '', $request->harga);

        // Handle gambar
        $gambarPath = $produk->gambar; // Default gunakan gambar lama
        if ($request->hasFile('gambar')) {
            // Simpan gambar baru ke storage/public/produk
            $gambarPath = $request->file('gambar')->store('produk', 'public');

            // Hapus gambar lama jika ada
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
        }

        // Update produk
        $produk->update([
            'nama_produk' => $request->nama_produk,
            'id_kategori' => $request->id_kategori,
            'harga' => $harga,
            'stok' => $request->stok,
            'berat' => $request->berat,
            'deskripsi' => $request->deskripsi,
            'gambar' => $gambarPath,
        ]);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();
        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
