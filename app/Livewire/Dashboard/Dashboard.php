<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    #[Title('Dashboard')]

    public function render()
    {
        $user = User::find(Auth::user()->id);

        if ($user->hasRole('admin')) {
            return view('livewire.dashboard.dashboard-admin');
        } else if ($user->hasRole('user')) {
            return view('livewire.dashboard.dashboard-user');
        }
    }
}
