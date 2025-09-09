<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wisata;
use App\Models\Kategori;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class IntegratedWisataSeeder extends Seeder
{
    private $processedImages = [];
    private $kategoriMapping = [];
    private $successCount = 0;
    private $failedCount = 0;
    private $skippedCount = 0;

    public function run(): void
    {
        $this->command->info('🚀 Memulai proses import data wisata...');

        // Step 1: Setup kategori mapping
        $this->setupKategoriMapping();

        // Step 2: Setup direktori dan pindahkan gambar
        $this->setupStorageAndMoveImages();

        // Step 3: Proses semua file CSV
        $this->processAllCsvFiles();

        // Step 4: Tampilkan laporan
        $this->showFinalReport();
    }

    private function setupKategoriMapping(): void
    {
        $this->command->info('📝 Setup kategori mapping...');

        // Mapping nama file ke ID kategori (berdasarkan seeder kategori)
        $this->kategoriMapping = [
            'hotel_bandar_lampung_25.csv' => 5, // Hotel
            'kuliner_bandar_lampung_25.csv' => 4, // Kuliner
            'lokasi_wisata_pesawaran_25.csv' => 1, // Alam
            'pantai_lampung_selatan_25.csv' => 1, // Alam (Pantai)
            'pantai_pesawaran_25.csv' => 1, // Alam (Pantai)
            'pantai_pesisir_barat_25.csv' => 1, // Alam (Pantai)
            'wisata_lampung_timur_25.csv' => 1, // Alam
            'wisata_religi_lampung_25.csv' => 3, // Religi
        ];

        $this->command->info('✅ Kategori mapping berhasil dibuat');
    }

    private function setupStorageAndMoveImages(): void
    {
        $this->command->info('📁 Setup storage dan pemindahan gambar...');

        // Pastikan folder storage ada
        Storage::makeDirectory('public/wisata');

        // Path gambar dari CSV menggunakan images-wisata-lampung
        $sourceDir = public_path('images-wisata-lampung/');
        $storageDir = storage_path('app/public/wisata/');

        if (!File::exists($sourceDir)) {
            $this->command->warn("⚠️ Direktori gambar tidak ditemukan: {$sourceDir}");
            $this->command->info("💡 Pastikan folder images-wisata-lampung ada di public/");
            return;
        }

        // Copy default image jika ada
        $defaultSource = public_path('seeder/image/wisata/default.png');
        if (File::exists($defaultSource)) {
            File::copy($defaultSource, $storageDir . 'default.png');
            $this->command->info("✅ Default image berhasil disalin");
        }

        // Pindahkan semua gambar dari images-wisata-lampung
        $imageFiles = File::files($sourceDir);
        $movedCount = 0;

        foreach ($imageFiles as $file) {
            if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $filename = $file->getFilename();
                try {
                    File::copy($file->getPathname(), $storageDir . $filename);
                    $this->processedImages[$filename] = "wisata/{$filename}";
                    $movedCount++;

                    if ($movedCount % 20 == 0) {
                        $this->command->info("📸 Progress: {$movedCount} gambar dipindahkan...");
                    }
                } catch (Exception $e) {
                    $this->command->warn("⚠️ Gagal memindahkan: {$filename}");
                }
            }
        }

        $this->command->info("✅ {$movedCount} gambar berhasil dipindahkan ke storage/wisata/");
    }

    private function processAllCsvFiles(): void
    {
        $csvDir = public_path('seeder/data-scrap-25-akurat/');

        if (!File::exists($csvDir)) {
            $this->command->error("❌ Direktori CSV tidak ditemukan: {$csvDir}");
            return;
        }

        $csvFiles = File::glob($csvDir . '*.csv');

        if (empty($csvFiles)) {
            $this->command->error("❌ Tidak ada file CSV ditemukan");
            return;
        }

        $this->command->info("📋 Memproses " . count($csvFiles) . " file CSV");

        foreach ($csvFiles as $csvFile) {
            $filename = basename($csvFile);
            $this->processCsvFile($csvFile, $filename);
        }
    }

    private function processCsvFile(string $csvPath, string $filename): void
    {
        if (!isset($this->kategoriMapping[$filename])) {
            $this->command->warn("⚠️ Tidak ada mapping untuk: {$filename}");
            return;
        }

        $this->command->info("🔄 Memproses: {$filename}");

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->command->error("❌ Gagal membuka: {$filename}");
            return;
        }

        // Baca header
        $header = fgetcsv($handle, 0, ',');
        $kategoriId = $this->kategoriMapping[$filename];

        $processed = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $data = array_combine($header, $row);

            if (!$data) {
                $skipped++;
                continue;
            }

            $nama = trim($data['Nama Lokasi'] ?? '');
            $latitude = $data['Latitude'] ?? null;
            $longitude = $data['Longitude'] ?? null;
            $alamat = trim($data['Alamat'] ?? '');
            $deskripsi = trim($data['Deskripsi'] ?? '');
            $imagePath = $data['Image_Path'] ?? '';
            $imageStatus = $data['Image_Status'] ?? '';

            // Validasi data wajib
            if (empty($nama) || !is_numeric($latitude) || !is_numeric($longitude)) {
                $skipped++;
                continue;
            }

            // Cek duplikasi
            $exists = Wisata::where('nama', $nama)
                ->whereRaw("JSON_EXTRACT(kordinat, '$.lat') = ?", [(float)$latitude])
                ->whereRaw("JSON_EXTRACT(kordinat, '$.lng') = ?", [(float)$longitude])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Konversi path gambar dari CSV ke format storage
            $finalImagePath = $this->convertImagePath($imagePath, $imageStatus);

            try {
                Wisata::create([
                    'nama' => $nama,
                    'kategori_id' => $kategoriId,
                    'image' => $finalImagePath,
                    'deskripsi' => !empty($deskripsi) ? $deskripsi : 'Deskripsi akan segera ditambahkan',
                    'alamat' => !empty($alamat) ? $alamat : 'Alamat tidak tersedia',
                    'kordinat' => json_encode([
                        'lat' => (float)$latitude,
                        'lng' => (float)$longitude,
                    ]),
                ]);

                $processed++;
                $this->successCount++;
            } catch (Exception $e) {
                $this->failedCount++;
                Log::error("Gagal insert {$nama}: " . $e->getMessage());
            }
        }

        fclose($handle);
        $this->command->info("   ✅ {$processed} berhasil, {$skipped} dilewati");
    }

    private function convertImagePath(string $imagePath, string $imageStatus): string
    {
        // Jika tidak ada image path atau status gagal, gunakan default
        if (empty($imagePath) || $imageStatus !== 'SUCCESS') {
            return 'wisata/default.png';
        }

        // Konversi dari: ..\images-wisata-lampung\20250816_002328_Yunna_Hotel_Lampung.jpg
        // Menjadi: wisata/20250816_002328_Yunna_Hotel_Lampung.jpg

        // Extract filename saja
        $filename = basename($imagePath);

        // Cek apakah file gambar benar-benar ada di storage
        $storagePath = storage_path('app/public/wisata/' . $filename);

        if (File::exists($storagePath)) {
            return 'wisata/' . $filename;
        }

        // Jika file tidak ditemukan, coba cari dengan nama yang mirip
        $storageDir = storage_path('app/public/wisata/');
        if (File::exists($storageDir)) {
            $files = File::files($storageDir);

            foreach ($files as $file) {
                if ($file->getFilename() === $filename) {
                    return 'wisata/' . $filename;
                }
            }
        }

        // Jika tetap tidak ditemukan, gunakan default
        $this->command->warn("⚠️ Gambar tidak ditemukan: {$filename}");
        return 'wisata/default.png';
    }

    private function showFinalReport(): void
    {
        $this->command->info("\n" . str_repeat('=', 50));
        $this->command->info('📋 LAPORAN IMPORT DATA WISATA');
        $this->command->info(str_repeat('=', 50));
        $this->command->info("✅ Berhasil: {$this->successCount}");
        $this->command->info("⏭️ Dilewati: {$this->skippedCount}");
        $this->command->info("❌ Gagal: {$this->failedCount}");
        $this->command->info("🖼️ Gambar: " . count($this->processedImages));
        Log::info("\n" . str_repeat('=', 50));
        Log::info('📋 LAPORAN IMPORT DATA WISATA');
        Log::info(str_repeat('=', 50));
        Log::info("✅ Berhasil: {$this->successCount}");
        Log::info("⏭️ Dilewati: {$this->skippedCount}");
        Log::info("❌ Gagal: {$this->failedCount}");
        Log::info("🖼️ Gambar: " . count($this->processedImages));

        if ($this->successCount > 0) {
            $this->command->info("\n🎉 Import selesai!");
        }
    }
}
