<?php

namespace Database\Seeders;

use App\Models\Locker;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LokerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_lokasi'           => '1',
                'nomor_loker'         => 'A101',
                'size'                => 'S',
                'topic'               => 'smart_locker/pnj/101/cmd',
                'status'              => 'EMPTY',
            ],

            [
                'id_lokasi'           => '1',
                'nomor_loker'         => 'A102',
                'size'                => 'M',
                'topic'               => 'smart_locker/pnj/102/cmd',
                'status'              => 'EMPTY',
            ],

            [
                'id_lokasi'           => '1',
                'nomor_loker'         => 'A103',
                'size'                => 'L',
                'topic'               => 'smart_locker/pnj/103/cmd',
                'status'              => 'EMPTY',
            ],
        ];

        Locker::insert($data);
    }
}
