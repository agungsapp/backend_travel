<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wisata;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class WisataSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan folder gambar ada
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

        // Lokasi CSV
        $csvPath = public_path('seeder/wisata.csv');

        if (!File::exists($csvPath)) {
            Log::error("File CSV tidak ditemukan: {$csvPath}");
            $this->command->error("File CSV tidak ditemukan di {$csvPath}");
            return;
        }

        // Baca CSV pakai koma sebagai delimiter
        if (($handle = fopen($csvPath, 'r')) !== false) {
            $header = fgetcsv($handle, 0, ','); // baca header

            if ($header === false) {
                Log::error("Header CSV kosong atau tidak terbaca.");
                $this->command->error("Header CSV kosong atau tidak terbaca.");
                return;
            }

            $rowIndex = 1;
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rowIndex++;

                try {
                    // Map kolom ke variabel
                    $namaLokasi     = $row[0] ?? null;
                    $latitude       = $row[1] ?? null;
                    $longitude      = $row[2] ?? null;
                    $alamat         = $row[3] ?? null;
                    $deskripsi      = $row[4] ?? 'deskripsi kosong atau tidak ada';
                    $kategoriNama   = $row[5] ?? null;
                    $kategoriId     = $row[6] ?? null;

                    if (!$namaLokasi || !$latitude || !$longitude || !$kategoriId) {
                        Log::warning("Baris {$rowIndex} dilewati karena data tidak lengkap: " . json_encode($row));
                        continue;
                    }

                    Wisata::create([
                        'nama'        => $namaLokasi,
                        'kategori_id' => (int)$kategoriId,
                        'deskripsi'   => $deskripsi ?: 'deskripsi kosong atau tidak ada',
                        'alamat'      => $alamat ?: '-',
                        'kordinat'    => json_encode([
                            'lat' => (float)$latitude,
                            'lng' => (float)$longitude,
                        ]),
                    ]);
                } catch (\Exception $e) {
                    Log::error("Gagal import baris {$rowIndex}: " . $e->getMessage());
                }
            }

            fclose($handle);
            $this->command->info('Data wisata dari CSV berhasil diimport.');
        } else {
            Log::error("Gagal membuka file CSV: {$csvPath}");
            $this->command->error("Gagal membuka file CSV.");
        }
    }
}
