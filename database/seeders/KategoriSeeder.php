<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'nama' => 'alam'
            ],
            [
                'nama' => 'budaya'
            ],
            [
                'nama' => 'religi'
            ],
            [
                'nama' => 'kuliner'
            ],
        ];

        foreach ($items as $item) {
            Kategori::create($item);
        }
    }
}
