<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Favorit;
use App\Models\Kategori;
use App\Models\Wisata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WisataController extends Controller
{


    public function addWisataToFavorites(Request $request, $id)
    {
        // Validasi bahwa $id adalah angka
        if (!is_numeric($id)) {
            return response()->json([
                'message' => 'ID wisata tidak valid!',
            ], 400);
        }

        // Pastikan pengguna terautentikasi
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Anda harus login untuk menambahkan favorit!',
            ], 401);
        }

        try {
            $wisata = Wisata::findOrFail($id); // Validasi wisata ada
            $user = Auth::user(); // Mendapatkan user yang sedang login

            // Cek apakah wisata sudah ada di favorit user
            $wisataAlreadyFavorited = Favorit::where('user_id', $user->id)
                ->where('wisata_id', $id)
                ->exists();

            if ($wisataAlreadyFavorited) {
                return response()->json([
                    'message' => 'Wisata sudah ada di favorit!',
                ], 400);
            }

            // Tambahkan wisata ke favorit
            Favorit::create([
                'user_id' => $user->id,
                'wisata_id' => $id,
            ]);

            return response()->json([
                'message' => 'Berhasil menambahkan wisata ke favorit!',
                'wisata' => $wisata,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Wisata tidak ditemukan!',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan favorit!',
            ], 500);
        }
    }



    public function getWisataById(Request $request, $id)
    {
        // Validasi bahwa $id adalah angka
        if (!is_numeric($id)) {
            return response()->json([
                'message' => 'ID wisata tidak valid!',
            ], 400);
        }

        try {
            $wisata = Wisata::with('kategori')->findOrFail($id);

            // Ubah path image wisata menjadi URL
            $wisata->image = url(Storage::url($wisata->image));

            // Ubah path image kategori menjadi URL jika ada kategori
            if ($wisata->kategori && isset($wisata->kategori->kategori_image)) {
                $wisata->kategori->kategori_image = url(Storage::url($wisata->kategori->kategori_image));
            }

            // Default is_favorit = false
            $wisata->is_favorit = false;

            // Jika user login, cek apakah wisata ini sudah difavoritkan
            $user = $request->user();


            if ($user) {
                $wisata->is_favorit = Favorit::where('user_id', $user->id)
                    ->where('wisata_id', $wisata->id)
                    ->exists();
            }

            return response()->json([
                'message' => 'Berhasil mengambil data wisata!',
                'wisata' => $wisata,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Wisata tidak ditemukan!',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data wisata!',
                'error' => $e->getMessage(), // Hapus di produksi
            ], 500);
        }
    }

    public function getWisataByKategori($id)
    {
        try {
            $kategori = Kategori::findOrFail($id); // Validasi kategori ada
            $wisata = Wisata::with('kategori')->where('kategori_id', $id)->get();

            $wisata->map(function ($w) {
                $w->image = url(Storage::url($w->image)); // Mengubah path image menjadi URL yang dapat diakses
                return $w;
            });

            return response()->json([
                'message' => 'Berhasil mengambil data wisata berdasarkan kategori!',
                'wisata' => $wisata,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Kategori tidak ditemukan!',
            ], 404);
        }
    }

    public function getWisata()
    {
        $wisata = Wisata::with('kategori')->get();

        $wisata->map(function ($w) {
            $w->image = url(Storage::url($w->image)); // Mengubah path image menjadi URL yang dapat diakses
            return $w;
        });

        return response()->json([
            'message' => 'Berhasil mengambil data wisata!',
            'wisata' => $wisata,
        ], 200);
    }

    public function getKategori()
    {
        $kategori = Kategori::all();

        $kategori->map(function ($k) {
            $k->kategori_image = url(Storage::url($k->kategori_image));
            return $k;
        });

        return response()->json([
            'message' => 'Berhasil mengambil data kategori!',
            'kategori' => $kategori,
        ], 200);
    }
}
