<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class Profile extends Component
{
    #[Title('Setting Profile')]

    public $statusConnected, $qrcodeHtml = '';
    public $name, $email, $password, $current_password, $password_confirmation;

    public function mount()
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;

        $this->checkConnectionWa();
    }

    public function render()
    {
        return view('livewire.profile.profile');
    }

    private function dispatchAlert($type, $message, $text)
    {
        $this->dispatch('swal:modal', [
            'type'      => $type,
            'message'   => $message,
            'text'      => $text
        ]);
    }

    public function updateProfile()
    {
        $this->validate([
            'name'      => 'required',
            'email'     => 'required|email'
        ]);

        $data   = User::findOrFail(Auth::user()->id);
        $data->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);
        $this->dispatchAlert('success', 'Success!', 'Profile updated successfully.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password'  => 'required',
            'password'          => 'required'
        ]);

        $user   = User::findOrFail(Auth::user()->id);

        if (!Hash::check($this->current_password, $user->password)) {
            $this->dispatchAlert('warning', 'Alert!', 'Current password is incorrect.');
        } else if ($this->password !== $this->password_confirmation) {
            $this->dispatchAlert('warning', 'Alert!', 'Password and confirmation password doesn\'t match!.');
        } else {
            $user->password = Hash::make($this->password);
            $user->save();

            $this->dispatchAlert('success', 'Success!', 'Password updated successfully.');
        }
    }

    public function checkConnectionWa()
    {
        $waBaseUrl = config('app.wa_base_url', 'http://localhost:5000');
        $credId = config('app.wa_cred_id', 'CrashDevSmartLocker');

        $raw  = @file_get_contents("{$waBaseUrl}/get-state?cred_id={$credId}");
        $json = $raw !== false ? json_decode($raw, true) : null;
        $this->statusConnected = is_string($json) ? $json === 'connected'
            : (is_array($json) ? (($json['state'] ?? null) === 'connected') : false);
    }

    public function generateQRCode()
    {
        $waBaseUrl = config('app.wa_base_url', 'http://localhost:5000');
        $credId = config('app.wa_cred_id', 'CrashDevSmartLocker');

        $url = "{$waBaseUrl}/get-qrcode?cred_id={$credId}";
        $response = Http::get($url);

        if ($response->successful()) {
            // isi responsenya langsung <img ...> 
            $this->qrcodeHtml = $response->body();
        } else {
            $this->qrcodeHtml = '<p class="text-danger">Gagal ambil QR Code</p>';
        }
    }
}
