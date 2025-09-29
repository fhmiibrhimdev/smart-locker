<?php

namespace App\Livewire\Dashboard;

use App\Models\Locker;
use App\Models\Parcel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardKurir extends Component
{
    use WithPagination;
    #[Title('Dashboard')]

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'delete',
        'mqttPublishSuccess',
        'mqttPublishError',
        'lockerOpenedSuccess'
    ];

    protected $rules = [
        'id_loker'            => '',
        'id_kurir'            => '',
        'no_penerima'         => 'required',
        'kode_paket'          => '',
        'kode_pengambilan'    => '',
        'status'              => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $lokers;
    public $allLokers; // For edit mode
    public $isLockerOpened = false;
    public $id_loker, $id_kurir, $no_penerima, $kode_paket, $kode_pengambilan, $status;
    public $statusConnected;

    public function sendWA()
    {
        $waBaseUrl = config('app.wa_base_url', 'http://localhost:5000');
        $credId = config('app.wa_cred_id', 'CrashDevSmartLocker');

        $raw  = @file_get_contents("{$waBaseUrl}/get-state?cred_id={$credId}");
        $json = $raw !== false ? json_decode($raw, true) : null;
        $this->statusConnected = is_string($json) ? $json === 'connected'
            : (is_array($json) ? (($json['state'] ?? null) === 'connected') : false);

        if (!$this->no_penerima || $this->no_penerima == "62") {
            $this->dispatchAlert('error', 'Gagal', 'Nomor WA tidak boleh kosong!');
            return;
        }

        // Get locker and location info
        $loker = DB::table('loker')
            ->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
            ->where('loker.id', $this->id_loker)
            ->select('nomor_loker', 'nama_lokasi')
            ->first();

        // Template pesan dengan 2 metode pengambilan
        $template_pesan = "🚚 *PAKET ANDA TELAH TERSIMPAN DI SMART LOCKER* 📦\n\n";
        $template_pesan .= "Halo! Paket Anda sudah berhasil disimpan di Smart Locker.\n\n";

        $template_pesan .= "📍 *Detail Lokasi:*\n";
        $template_pesan .= "• Lokasi: {$loker->nama_lokasi}\n";
        $template_pesan .= "• Nomor Loker: {$loker->nomor_loker}\n";
        $template_pesan .= "• Kode Resi: {$this->kode_paket}\n\n";

        $template_pesan .= "⏰ *Waktu Pengambilan:*\n";
        $template_pesan .= "Berlaku hingga: " . now()->addDay()->format('d/m/Y H:i') . " WIB\n\n";

        $template_pesan .= "🔐 *CARA PENGAMBILAN PAKET:*\n";
        $template_pesan .= config('app.url') . "/pickup/{$this->kode_paket}\n\n";

        $template_pesan .= "Terima kasih telah menggunakan Smart Locker! 🙏";

        if ($this->statusConnected) {
            // --- kirim lewat gateway ---
            $url = "{$waBaseUrl}/send-text-message?cred_id={$credId}";
            $payload = json_encode([
                'phone_number' => $this->no_penerima,
                'message'      => $template_pesan,
            ], JSON_UNESCAPED_UNICODE);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => $payload,
            ]);
            $resp = curl_exec($ch);
            $err  = curl_errno($ch);
            $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($err || $code < 200 || $code >= 300) {
                $this->dispatchAlert('error', 'Gagal', 'Kirim WA via gateway gagal.');
                return;
            }

            $this->dispatchAlert('success', 'Berhasil', 'Pesan terkirim via gateway.');
        } else {
            // --- fallback direct ke wa.me ---
            $url = "https://wa.me/{$this->no_penerima}?text=" . rawurlencode($template_pesan);
            $this->dispatch('open-wa', ['url' => $url]);
        }
    }

    public function mount()
    {
        // Lokers untuk store mode (hanya EMPTY)
        $this->lokers = DB::table('loker')->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
            ->select('loker.id', 'nomor_loker', 'size', 'nama_lokasi', 'loker.topic')
            ->where('loker.status', 'EMPTY')
            ->get();

        // All lokers untuk edit mode
        $this->allLokers = DB::table('loker')->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
            ->select('loker.id', 'nomor_loker', 'size', 'nama_lokasi', 'loker.topic')
            ->get();

        $this->resetInputFields();
    }

    public function getAvailableLokers()
    {
        return $this->isEditing ? $this->allLokers : $this->lokers;
    }

    public function refreshLokers()
    {
        // Refresh lokers untuk store mode (hanya EMPTY)
        $this->lokers = DB::table('loker')->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
            ->select('loker.id', 'nomor_loker', 'size', 'nama_lokasi', 'loker.topic')
            ->where('loker.status', 'EMPTY')
            ->get();

        // Refresh all lokers untuk edit mode
        $this->allLokers = DB::table('loker')->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
            ->select('loker.id', 'nomor_loker', 'size', 'nama_lokasi', 'loker.topic')
            ->get();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = Parcel::select('parcel.id', 'parcel.no_penerima', 'parcel.kode_paket', 'parcel.kode_pengambilan', 'parcel.status', 'loker.nomor_loker', 'lokasi.nama_lokasi', 'users.name as nama_kurir', 'parcel.expired_at')
            ->join('loker', 'loker.id', '=', 'parcel.id_loker')
            ->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
            ->join('users', 'users.id', '=', 'parcel.id_kurir')
            ->where(function ($query) use ($search) {
                $query->where('no_penerima', 'LIKE', $search);
                $query->orWhere('kode_paket', 'LIKE', $search);
            })
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.dashboard.dashboard-kurir', compact('data'));
    }

    public function store()
    {
        $this->validate();

        // Generate kode pengambilan jika belum ada
        if (empty($this->kode_pengambilan)) {
            $this->generateKodePengambilan();
        }

        // Create parcel dengan expired_at 1 hari dari sekarang
        $parcel = Parcel::create([
            'id_loker'            => $this->id_loker,
            'id_kurir'            => $this->id_kurir,
            'no_penerima'         => $this->no_penerima,
            'kode_paket'          => $this->kode_paket,
            'kode_pengambilan'    => $this->kode_pengambilan,
            'status'              => 'STORED',
            'expired_at'          => now()->addDay(), // 1 hari dari sekarang
        ]);

        // Update status loker menjadi STORED
        DB::table('loker')
            ->where('id', $this->id_loker)
            ->update(['status' => 'STORED']);

        // Log aktivitas create parcel
        DB::table('parcel_logs')->insert([
            'id_parcel' => $parcel->id,
            'action' => 'STORED',
            'description' => "Paket disimpan oleh kurir: " . Auth::user()->name . " - Kode: {$this->kode_paket} - Loker: {$this->id_loker}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->sendWa();
        $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
    }

    public function edit($id)
    {
        $this->isEditing        = true;
        $this->isLockerOpened   = false; // Set to false so user can open locker again
        $data = Parcel::where('id', $id)->first();
        $this->dataId           = $id;
        $this->id_loker         = $data->id_loker;
        $this->id_kurir         = $data->id_kurir;
        $this->no_penerima      = $data->no_penerima;
        $this->kode_paket       = $data->kode_paket;
        $this->kode_pengambilan = $data->kode_pengambilan;
        $this->status           = $data->status;
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            $parcel = Parcel::findOrFail($this->dataId);
            $oldLokerId = $parcel->id_loker;
            $oldData = [
                'kode_paket' => $parcel->kode_paket,
                'status' => $parcel->status,
                'loker_id' => $oldLokerId
            ];

            $parcel->update([
                'id_loker'            => $this->id_loker,
                'id_kurir'            => $this->id_kurir,
                'no_penerima'         => $this->no_penerima,
                'kode_paket'          => $this->kode_paket,
                'kode_pengambilan'    => $this->kode_pengambilan,
                'status'              => $this->status,
            ]);

            // Jika loker berubah, update status loker lama dan baru
            if ($oldLokerId != $this->id_loker) {
                // Set loker lama menjadi EMPTY
                DB::table('loker')
                    ->where('id', $oldLokerId)
                    ->update(['status' => 'EMPTY']);

                // Set loker baru menjadi STORED
                DB::table('loker')
                    ->where('id', $this->id_loker)
                    ->update(['status' => 'STORED']);
            }

            // Log aktivitas update parcel
            $description = "Paket diupdate oleh kurir: " . Auth::user()->name;
            if ($oldLokerId != $this->id_loker) {
                $description .= " - Loker dipindah dari {$oldLokerId} ke {$this->id_loker}";
            }
            if ($oldData['status'] != $this->status) {
                $description .= " - Status: {$oldData['status']} → {$this->status}";
            }
            $description .= " - Kode: {$this->kode_paket}";

            DB::table('parcel_logs')->insert([
                'id_parcel' => $parcel->id,
                'action' => 'UPDATED',
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->dispatchAlert('success', 'Success!', 'Data updated successfully.');
            $this->dataId = null;
        }
    }

    public function deleteConfirm($id)
    {
        $this->dataId = $id;
        $this->dispatch('swal:confirm', [
            'type'      => 'warning',
            'message'   => 'Are you sure?',
            'text'      => 'If you delete the data, it cannot be restored!'
        ]);
    }

    public function delete()
    {
        if (!$this->dataId) {
            return;
        }

        $parcel = Parcel::findOrFail($this->dataId);
        $lokerId = $parcel->id_loker;
        $kodeParcel = $parcel->kode_paket;

        // Log aktivitas delete sebelum hapus parcel
        DB::table('parcel_logs')->insert([
            'id_parcel' => $parcel->id,
            'action' => 'DELETED',
            'description' => "Paket dihapus oleh kurir: " . Auth::user()->name . " - Kode: {$kodeParcel} - Loker: {$lokerId}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Delete parcel
        $parcel->delete();

        // Set loker menjadi EMPTY
        DB::table('loker')
            ->where('id', $lokerId)
            ->update(['status' => 'EMPTY']);

        // Reset dataId
        $this->dataId = null;

        $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
    }

    public function openLocker()
    {
        $availableLokers = $this->getAvailableLokers();
        $loker = $availableLokers->firstWhere('id', $this->id_loker);

        if (!$loker) {
            $this->dispatch('swal:modal', [
                'type'      => 'error',
                'message'   => 'Error!',
                'text'      => 'Locker not found!'
            ]);
            return;
        }

        $topic = $loker->topic;

        if (!$topic) {
            $this->dispatch('swal:modal', [
                'type'      => 'error',
                'message'   => 'Error!',
                'text'      => 'Topic not configured for this locker!'
            ]);
            return;
        }

        // Debug logging
        Log::info('Opening locker', [
            'locker_id' => $this->id_loker,
            'topic' => $topic,
            'message' => 'OPEN'
        ]);

        // Menampilkan SweetAlert info bahwa sedang mencoba membuka loker
        $this->dispatch('mqtt:publish', [
            'topic' => $topic,
            'message' => 'OPEN',
            'locker_id' => $this->id_loker
        ]);
    }

    public function mqttPublishSuccess()
    {
        $this->isLockerOpened = true;

        // Generate kode pengambilan hanya jika belum ada (untuk data baru)
        if (empty($this->kode_pengambilan)) {
            $this->generateKodePengambilan();
        }

        $this->dispatch('swal:modal', [
            'type'      => 'success',
            'message'   => 'Success!',
            'text'      => 'Locker opened successfully!'
        ]);
    }

    public function mqttPublishError()
    {
        $this->dispatch('swal:modal', [
            'type'      => 'error',
            'message'   => 'Error!',
            'text'      => 'Failed to open locker. Please try again.'
        ]);
    }

    public function lockerOpenedSuccess()
    {
        $this->isLockerOpened = true;

        // Generate kode pengambilan hanya jika belum ada (untuk data baru)
        if (empty($this->kode_pengambilan)) {
            $this->generateKodePengambilan();
        }
    }

    private function generateKodePengambilan()
    {
        // Generate 6 digit code dengan karakter A-D dan 0-9
        $characters = 'ABCD0123456789';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        $this->kode_pengambilan = $code;
    }

    public function updatingLengthData()
    {
        $this->resetPage();
    }

    private function searchResetPage()
    {
        if ($this->searchTerm !== $this->previousSearchTerm) {
            $this->resetPage();
        }

        $this->previousSearchTerm = $this->searchTerm;
    }

    private function dispatchAlert($type, $message, $text)
    {
        $this->dispatch('swal:modal', [
            'type'      => $type,
            'message'   => $message,
            'text'      => $text
        ]);

        // Refresh lokers setelah operasi berhasil
        $this->refreshLokers();
        $this->resetInputFields();
    }

    public function isEditingMode($mode)
    {
        $this->dispatch('initSelect2');
        $this->isEditing = $mode;

        // Reset isLockerOpened jika sedang add data baru
        if (!$mode) {
            $this->isLockerOpened = false;
        }
    }

    public function updated()
    {
        $this->dispatch('initSelect2');
    }

    private function resetInputFields()
    {
        $this->id_loker            = $this->lokers->first()->id ?? null;
        $this->id_kurir            = Auth::user()->id;
        $this->no_penerima         = '62';
        $this->kode_paket          = '';
        $this->kode_pengambilan    = '';
        $this->status              = 'STORED';
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->isLockerOpened  = false;
        $this->resetInputFields();
    }
}
