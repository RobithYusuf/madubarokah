<?php

namespace App\Http\Controllers;

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
        return view('produk.index', compact('produks', 'kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required',
            'id_kategori' => 'nullable|exists:kategori,id',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
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
            'deskripsi' => $request->deskripsi,
            'gambar' => $gambarPath,
        ]);
        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        // Ambil data produk berdasarkan ID
        $produk = Produk::findOrFail($id);

        // Konversi harga misal 65.000 menjadi 65000
        $request->merge([
            'harga' => str_replace(['.', ','], '', $request->harga) // Hapus titik pemisah sebelum menyimpan
        ]);

        // Cek ada gambar baru atau tidak
        if ($request->hasFile('gambar')) {
            // Simpan gambar baru ke storage/public/produk
            $gambarPath = $request->file('gambar')->store('produk', 'public');

            // Hapus gambar lama jika ada
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }

            // Simpan path gambar baru
            $produk->gambar = $gambarPath;
        }


        $request->validate([
            'nama_produk' => 'required',
            'id_kategori' => 'nullable|exists:kategori,id',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'deskripsi' => 'nullable',
            'gambar' => 'nullable|image'
        ]);

        $produk->update([
            'nama_produk' => $request->nama_produk,
            'id_kategori' => $request->id_kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
            'gambar' => $produk->gambar, // Gunakan gambar lama jika tidak diubah
        ]);
        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();
        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
