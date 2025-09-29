<?php

namespace App\Livewire\MasterData;

use App\Models\Parcel;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class TransactionDetail extends Component
{
    #[Title('Pickup Paket')]

    public $kode_paket;

    // Individual properties instead of object to fix Livewire serialization
    public $parcel_id;
    public $nomor_loker;
    public $loker_size;
    public $kode_pengambilan;
    public $nama_lokasi;
    public $alamat;
    public $latitude;
    public $longitude;
    public $nama_kurir;
    public $expired_at;

    public $timeLeft;

    public function mount($kode_paket)
    {
        $this->kode_paket = $kode_paket;
        $this->loadParcelData();
    }

    public function loadParcelData()
    {
        // Load parcel data dengan relasi
        $parcel = DB::table('parcel')
            ->join('loker', 'loker.id', '=', 'parcel.id_loker')
            ->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
            ->join('users', 'users.id', '=', 'parcel.id_kurir')
            ->where('parcel.kode_paket', $this->kode_paket)
            ->select(
                'parcel.*',
                'loker.nomor_loker',
                'loker.size as loker_size',
                'lokasi.nama_lokasi',
                'lokasi.alamat',
                'lokasi.latitude',
                'lokasi.longitude',
                'users.name as nama_kurir'
            )
            ->first();

        if (!$parcel) {
            abort(404, 'Paket tidak ditemukan');
        }

        // Convert object properties to individual properties for Livewire compatibility
        $this->parcel_id = $parcel->id;
        $this->nomor_loker = $parcel->nomor_loker;
        $this->loker_size = $parcel->loker_size;
        $this->kode_pengambilan = $parcel->kode_pengambilan;
        $this->nama_lokasi = $parcel->nama_lokasi;
        $this->alamat = $parcel->alamat;
        $this->latitude = $parcel->latitude;
        $this->longitude = $parcel->longitude;
        $this->nama_kurir = $parcel->nama_kurir;
        $this->expired_at = $parcel->expired_at;

        // Check if expired
        if (now() > $this->expired_at) {
            $this->timeLeft = 'EXPIRED';
        } else {
            $this->calculateTimeLeft();
        }
    }

    public function calculateTimeLeft()
    {
        $expiredAt = \Carbon\Carbon::parse($this->expired_at);
        $now = now();

        if ($now >= $expiredAt) {
            $this->timeLeft = 'EXPIRED';
            return;
        }

        $diff = $now->diff($expiredAt);

        $this->timeLeft = [
            'days' => str_pad($diff->d, 2, '0', STR_PAD_LEFT),
            'hours' => str_pad($diff->h, 2, '0', STR_PAD_LEFT),
            'minutes' => str_pad($diff->i, 2, '0', STR_PAD_LEFT),
            'seconds' => str_pad($diff->s, 2, '0', STR_PAD_LEFT)
        ];
    }

    public function generateQRCode()
    {
        // Data untuk ESP CAM - API call untuk claim paket
        $qrData = json_encode([
            'api_endpoint' => config('app.url') . '/pickup/verify-and-claim',
            'method' => 'POST',
            'data' => [
                'kode_paket' => $this->kode_paket,
                'kode_pengambilan' => $this->kode_pengambilan,
                'parcel_id' => $this->parcel_id,
                'nomor_loker' => $this->nomor_loker
            ]
        ]);

        return QrCode::size(300)->generate($qrData);
    }

    public function getQrCodeSmall()
    {
        // QR code kecil untuk display
        $qrData = json_encode([
            'api_endpoint' => config('app.url') . '/pickup/verify-and-claim',
            'method' => 'POST',
            'data' => [
                'kode_paket' => $this->kode_paket,
                'kode_pengambilan' => $this->kode_pengambilan,
                'parcel_id' => $this->parcel_id,
                'nomor_loker' => $this->nomor_loker
            ]
        ]);

        return QrCode::size(80)->generate($qrData);
    }

    public function openGoogleMaps()
    {
        // Prioritas: latitude/longitude > alamat > nama_lokasi
        if (!empty($this->latitude) && !empty($this->longitude)) {
            // Gunakan koordinat lat/lng untuk akurasi yang lebih baik
            $url = "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        } elseif (!empty($this->alamat)) {
            // Fallback ke alamat jika koordinat tidak tersedia
            $alamat = urlencode($this->alamat);
            $url = "https://www.google.com/maps/search/" . $alamat;
        } else {
            // Fallback terakhir ke nama lokasi
            $alamat = urlencode($this->nama_lokasi);
            $url = "https://www.google.com/maps/search/" . $alamat;
        }

        $this->dispatch('open-url', ['url' => $url]);
    }

    public function render()
    {
        // Refresh time calculation setiap render
        if ($this->parcel_id && $this->timeLeft !== 'EXPIRED') {
            $this->calculateTimeLeft();
        }

        return view('livewire.master-data.transaction-detail');
    }
}
