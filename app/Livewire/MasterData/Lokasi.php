<?php

namespace App\Livewire\MasterData;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Lokasi as ModelsLokasi;

class Lokasi extends Component
{
    use WithPagination;
    #[Title('Lokasi - Smart Locker')]

    protected $listeners = [
        'delete'
    ];
    protected $rules = [
        'nama_lokasi' => 'required',
        'alamat' => 'required',
        'latitude' => 'required',
        'longitude' => 'required',
    ];
    protected $paginationTheme = 'bootstrap';

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId, $nama_lokasi, $alamat, $latitude, $longitude;

    public function mount()
    {
        $this->resetInputFields();
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = ModelsLokasi::select()
            ->where(function ($query) use ($search) {
                $query->where('nama_lokasi', 'like', $search)
                    ->orWhere('alamat', 'like', $search);
            })
            ->paginate($this->lengthData);

        return view('livewire.master-data.lokasi', compact('data'));
    }

    public function store()
    {
        $this->validate();

        ModelsLokasi::create([
            'nama_lokasi' => $this->nama_lokasi,
            'alamat' => $this->alamat,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
    }

    public function edit($id)
    {
        $this->isEditing = true;
        $data = ModelsLokasi::findOrFail($id);
        $this->dataId = $id;
        $this->nama_lokasi = $data->nama_lokasi;
        $this->alamat = $data->alamat;
        $this->latitude = $data->latitude;
        $this->longitude = $data->longitude;
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            ModelsLokasi::findOrFail($this->dataId)->update([
                'nama_lokasi' => $this->nama_lokasi,
                'alamat' => $this->alamat,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
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
        ModelsLokasi::findOrFail($this->dataId)->delete();
        $this->dispatchAlert('success', 'Success!', 'Data deleted successfully.');
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

        $this->resetInputFields();
    }

    public function isEditingMode($mode)
    {
        $this->isEditing = $mode;
    }

    private function resetInputFields()
    {
        $this->nama_lokasi = '';
        $this->alamat = '';
        $this->latitude = '';
        $this->longitude = '';
    }

    public function cancel()
    {
        $this->resetInputFields();
    }
}
