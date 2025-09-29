<?php

namespace App\Livewire\MasterData;

use App\Models\Locker;
use App\Models\Lokasi;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

class Loker extends Component
{
    use WithPagination;
    #[Title('Loker')]

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'delete'
    ];

    protected $rules = [
        'id_lokasi'           => 'required',
        'nomor_loker'         => 'required',
        'topic'               => 'required',
        'size'                => 'required',
        'status'              => '',
    ];

    public $lengthData = 25;
    public $searchTerm;
    public $previousSearchTerm = '';
    public $isEditing = false;

    public $dataId;
    public $lokasis;
    public $id_lokasi, $nomor_loker, $topic, $size, $status;

    public function mount()
    {
        $this->lokasis = Lokasi::select('id', 'nama_lokasi')->get();

        $this->id_lokasi           = '';
        $this->nomor_loker         = '';
        $this->topic               = '';
        $this->size                = 'opsi1';
        $this->status              = 'opsi1';
    }

    public function render()
    {
        $this->searchResetPage();
        $search = '%' . $this->searchTerm . '%';

        $data = Locker::select('loker.id', 'loker.nomor_loker', 'loker.topic', 'loker.size', 'loker.status', 'lokasi.nama_lokasi')
            ->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
            ->where(function ($query) use ($search) {
                $query->where('nama_lokasi', 'LIKE', $search);
                $query->orWhere('nomor_loker', 'LIKE', $search);
                $query->orWhere('topic', 'LIKE', $search);
            })
            ->orderBy('id', 'ASC')
            ->paginate($this->lengthData);

        return view('livewire.master-data.loker', compact('data'));
    }

    public function store()
    {
        $this->validate();

        Locker::create([
            'id_lokasi'           => $this->id_lokasi,
            'nomor_loker'         => $this->nomor_loker,
            'topic'               => $this->topic,
            'size'                => $this->size,
            'status'              => $this->status,
        ]);

        $this->dispatchAlert('success', 'Success!', 'Data created successfully.');
    }

    public function edit($id)
    {
        $this->isEditing        = true;
        $data = Locker::where('id', $id)->first();
        $this->dataId           = $id;
        $this->id_lokasi        = $data->id_lokasi;
        $this->nomor_loker      = $data->nomor_loker;
        $this->topic            = $data->topic;
        $this->size             = $data->size;
        $this->status           = $data->status;
    }

    public function update()
    {
        $this->validate();

        if ($this->dataId) {
            Locker::findOrFail($this->dataId)->update([
                'id_lokasi'           => $this->id_lokasi,
                'nomor_loker'         => $this->nomor_loker,
                'topic'               => $this->topic,
                'size'                => $this->size,
                'status'              => $this->status,
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
        Locker::findOrFail($this->dataId)->delete();
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
        $this->dispatch('initSelect2');
        $this->isEditing = $mode;
    }

    public function updated()
    {
        $this->dispatch('initSelect2');
    }

    private function resetInputFields()
    {
        $this->id_lokasi           = '';
        $this->nomor_loker         = '';
        $this->topic               = '';
        $this->size                = 'opsi1';
        $this->status              = 'opsi1';
    }

    public function cancel()
    {
        $this->isEditing       = false;
        $this->resetInputFields();
    }
}
