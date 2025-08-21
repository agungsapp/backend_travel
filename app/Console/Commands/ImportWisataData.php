<?php

namespace App\Console\Commands;

use Database\Seeders\IntegratedWisataSeeder;
use Illuminate\Console\Command;

class ImportWisataData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wisata:import 
                           {--force : Force import tanpa konfirmasi}
                           {--clear : Hapus data wisata yang ada sebelum import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data wisata dari CSV files di public/seeder/data-scrap-25-akurat/';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 IMPORT DATA WISATA LAMPUNG');
        $this->info(str_repeat('=', 50));

        // Cek apakah ada data wisata existing
        $existingCount = \App\Models\Wisata::count();

        if ($existingCount > 0 && !$this->option('clear')) {
            $this->warn("⚠️  Ditemukan {$existingCount} data wisata di database.");

            if (!$this->option('force')) {
                if (!$this->confirm('Lanjutkan import? (data duplikasi akan dilewati)')) {
                    $this->info('❌ Import dibatalkan');
                    return 0;
                }
            }
        }

        // Opsi clear data
        if ($this->option('clear')) {
            if ($this->option('force') || $this->confirm("⚠️  Hapus semua data wisata yang ada? ({$existingCount} records)")) {
                $this->info('🗑️  Menghapus data wisata existing...');
                \App\Models\Wisata::truncate();
                $this->info('✅ Data wisata berhasil dihapus');
            }
        }

        // Validasi struktur direktori
        $csvDir = public_path('seeder/data-scrap-25-akurat/');
        $imageDir = public_path('seeder/image/wisata-all/');

        if (!file_exists($csvDir)) {
            $this->error("❌ Direktori CSV tidak ditemukan: {$csvDir}");
            return 1;
        }

        if (!file_exists($imageDir)) {
            $this->error("❌ Direktori gambar tidak ditemukan: {$imageDir}");
            return 1;
        }

        $csvFiles = glob($csvDir . '*.csv');
        $imageFiles = glob($imageDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);

        $this->info("📁 Struktur data:");
        $this->info("   📊 CSV files: " . count($csvFiles));
        $this->info("   🖼️  Image files: " . count($imageFiles));

        if (empty($csvFiles)) {
            $this->error("❌ Tidak ada file CSV ditemukan!");
            return 1;
        }

        if (empty($imageFiles)) {
            $this->warn("⚠️  Tidak ada file gambar ditemukan!");
        }

        // Jalankan seeder
        $this->info("\n🔄 Memulai proses import...");

        try {
            $seeder = new IntegratedWisataSeeder();
            $seeder->setCommand($this);  // Pass command instance untuk output
            $seeder->run();

            $this->info("\n🎉 Import selesai!");

            // Show final statistics
            $totalWisata = \App\Models\Wisata::count();
            $totalKategori = \App\Models\Kategori::count();

            $this->info("📊 Statistik akhir:");
            $this->info("   🏛️  Total wisata: {$totalWisata}");
            $this->info("   📂 Total kategori: {$totalKategori}");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error during import: " . $e->getMessage());
            $this->error("🔍 Stack trace: " . $e->getTraceAsString());

            return 1;
        }
    }
}
