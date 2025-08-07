<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data kategori yang akan diinsert
        $items = [
            [
                'nama' => 'alam',
                'kategori_image' => 'kategori/alam.jpeg'
            ],
            [
                'nama' => 'budaya',
                'kategori_image' => 'kategori/budaya.jpeg'
            ],
            [
                'nama' => 'religi',
                'kategori_image' => 'kategori/religi.jpeg'
            ],
            [
                'nama' => 'kuliner',
                'kategori_image' => 'kategori/pantai.jpeg'
            ],
        ];

        // Insert data ke database
        foreach ($items as $item) {
            Kategori::create($item);
        }

        // Buat direktori kategori di storage jika belum ada
        Storage::makeDirectory('public/kategori');

        // Pemindahan file manual dari public/seeder ke storage
        $sourceDir = public_path('seeder/image/kategori/');
        $storageDir = storage_path('app/public/kategori/');

        $files = ['alam.jpeg', 'budaya.jpeg', 'religi.jpeg', 'pantai.jpeg'];

        foreach ($files as $file) {
            $sourcePath = $sourceDir . $file;
            $destinationPath = $storageDir . $file;

            if (File::exists($sourcePath)) {
                try {
                    File::copy($sourcePath, $destinationPath);
                    Log::info("File {$file} berhasil dipindahkan ke storage/kategori/");
                } catch (\Exception $e) {
                    Log::error("Gagal memindahkan file {$file}: " . $e->getMessage());
                }
            } else {
                Log::warning("File sumber tidak ditemukan: {$sourcePath}");
            }
        }
    }
}
