<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_lokasi'         => 'Loker Gd.A PNJ',
                'alamat'              => 'Gd. A, Jurusan Teknik Mesin, Kampus PNJ, Depok',
                'latitude'            => '-6.3714',
                'longitude'           => '106.823581',
            ],
            [
                'nama_lokasi'         => 'Loker Gd.D PNJ',
                'alamat'              => 'Gd. D, Jurusan Teknik Elektro, Kampus PNJ, Depok',
                'latitude'            => '-6.371411',
                'longitude'           => '106.823001',
            ],
            [
                'nama_lokasi'         => 'Loker Gd.F PNJ',
                'alamat'              => 'Gd. F, Jurusan Teknik Akuntansi, Kampus PNJ, Depok',
                'latitude'            => '-6.370931',
                'longitude'           => '106.823736',
            ],
        ];

        Lokasi::insert($data);
    }
}
