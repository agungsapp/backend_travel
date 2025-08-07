<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wisata;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class WisataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Storage::makeDirectory('public/wisata');
        $sourceDir = public_path('seeder/image/wisata/');
        $storageDir = storage_path('app/public/wisata/');

        $files = ['default.png'];

        foreach ($files as $file) {
            $sourcePath = $sourceDir . $file;
            $destinationPath = $storageDir . $file;

            if (File::exists($sourcePath)) {
                try {
                    File::copy($sourcePath, $destinationPath);
                    Log::info("File {$file} berhasil dipindahkan ke storage/wisata/");
                } catch (\Exception $e) {
                    Log::error("Gagal memindahkan file {$file}: " . $e->getMessage());
                }
            } else {
                Log::warning("File sumber tidak ditemukan: {$sourcePath}");
            }
        }

        $this->command->info('File gambar wisata telah dipindahkan ke storage/wisata/');

        // Data Wisata
        $wisatas = [
            [
                'nama' => 'Air Terjun Ciupang',
                'kategori_id' => 1,
                'deskripsi' => 'deskripsi kosong atau tidak ada',
                'alamat' => 'Sumber Jaya, Way Ratai, Pesawaran',
                'kordinat' => json_encode(['lat' => -5.588565, 'lng' => 105.020494]),
            ],
            [
                'nama' => 'WAY MIOS Gunung Batu',
                'kategori_id' => 2,
                'deskripsi' => 'deskripsi kosong atau tidak ada',
                'alamat' => 'Pampangan, Gedong Tataan, Pesawaran',
                'kordinat' => json_encode(['lat' => -5.439725, 'lng' => 105.096933]),
            ],
            [
                'nama' => 'Puncak Bukit Cendana',
                'kategori_id' => 3,
                'deskripsi' => 'deskripsi kosong atau tidak ada',
                'alamat' => 'Hutan, Pesawaran',
                'kordinat' => json_encode(['lat' => -5.539993, 'lng' => 105.107705]),
            ],
        ];

        foreach ($wisatas as $data) {
            Wisata::create($data);
        }
        $this->command->info('Data wisata telah berhasil diisi.');
    }
}
