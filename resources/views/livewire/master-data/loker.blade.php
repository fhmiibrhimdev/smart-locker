<div>
    <section class='section custom-section'>
        <div class='section-header'>
            <h1>Loker</h1>
        </div>

        <div class='section-body'>
            <div class='card'>
                <h3>Tabel Loker</h3>
                <div class='card-body'>
                    <div class='show-entries'>
                        <p class='show-entries-show'>Show</p>
                        <select wire:model.live='lengthData' id='length-data'>
                            <option value='25'>25</option>
                            <option value='50'>50</option>
                            <option value='100'>100</option>
                            <option value='250'>250</option>
                            <option value='500'>500</option>
                        </select>
                        <p class='show-entries-entries'>Entries</p>
                    </div>
                    <div class='search-column'>
                        <p>Search: </p><input type='search' wire:model.live.debounce.750ms='searchTerm' id='search-data' placeholder='Search here...' class='form-control'>
                    </div>
                    <div class='table-responsive tw-max-h-96 no-scrollbar'>
                        <table class='tw-w-full tw-table-auto'>
                            <thead class='tw-sticky tw-top-0'>
                                <tr class='tw-text-gray-700'>
                                    {{-- <th width='6%' class='text-center'>No</th> --}}
                                    <th class='text-center tw-whitespace-nowrap'>Nomor Loker</th>
                                    <th class='text-center tw-whitespace-nowrap'>Size</th>
                                    <th class='text-center tw-whitespace-nowrap'>Topic</th>
                                    <th class='text-center tw-whitespace-nowrap'>Status</th>
                                    <th class='text-center'><i class='fas fa-cog'></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data->groupBy('nama_lokasi') as $result)
                                <tr>
                                   <td colspan="6" class="tw-font-semibold">{{ $result[0]->nama_lokasi }}</td> 
                                </tr>
                                @foreach ($result as $row)
                                <tr class='text-center'>
                                    {{-- <td class='tw-whitespace-nowrap'>{{ $loop->index + 1 }}</td> --}}
                                    <td class='text-center tw-whitespace-nowrap'>{{ $row->nomor_loker }}</td>
                                    <td class='text-center tw-whitespace-nowrap'>{{ $row->size }}</td>
                                    <td class='text-center tw-whitespace-nowrap'>{{ $row->topic }}</td>
                                    <td class='text-center tw-whitespace-nowrap'>{{ $row->status }}</td>
                                    <td class='tw-whitespace-nowrap'>
                                        <button wire:click.prevent='edit({{ $row->id }})' class='btn btn-primary' data-toggle='modal' data-target='#formDataModal'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button wire:click.prevent='deleteConfirm({{ $row->id }})' class='btn btn-danger'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan='6' class='text-center'>No data available in the table</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class='px-3 mt-5'>
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
        <button wire:click.prevent='isEditingMode(false)' class='btn-modal' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#formDataModal'>
            <i class='far fa-plus'></i>
        </button>
    </section>

    <div class='modal fade' wire:ignore.self id='formDataModal' aria-labelledby='formDataModalLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='formDataModalLabel'>{{ $isEditing ? 'Edit Data' : 'Add Data' }}</h5>
                    <button type='button' wire:click='cancel()' class='close' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>
                <form>
                    <div class='modal-body'>
                        <div class='form-group'>
                            <label for='id_lokasi'>Nama Lokasi</label>
                            <select wire:model='id_lokasi' id='id_lokasi' class='form-control select2'>
                                @foreach ($lokasis as $lokasi)
                                    <option value='{{ $lokasi->id }}'>{{ $lokasi->nama_lokasi }}</option>
                                @endforeach
                            </select>
                            @error('id_lokasi') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='nomor_loker'>Nomor Loker</label>
                            <input type='text' wire:model='nomor_loker' id='nomor_loker' class='form-control'>
                            @error('nomor_loker') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='topic'>Topic</label>
                            <input type='text' wire:model='topic' id='topic' class='form-control'>
                            @error('topic') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='size'>Size</label>
                            <select wire:model='size' id='size' class='form-control select2'>
                                <option value='S'>S</option>
                                <option value='M'>M</option>
                                <option value='L'>L</option>
                            </select>
                            @error('size') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' wire:click='cancel()' class='btn btn-secondary tw-bg-gray-300' data-dismiss='modal'>Close</button>
                        <button type='submit' wire:click.prevent='{{ $isEditing ? 'update()' : 'store()' }}' wire:loading.attr='disabled' class='btn btn-primary tw-bg-blue-500'>Save Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('general-css')
<link href="{{ asset('assets/midragon/select2/select2.min.css') }}" rel="stylesheet" />
@endpush

@push('js-libraries')
<script src="{{ asset('/assets/midragon/select2/select2.full.min.js') }}"></script>
@endpush

@push('scripts')
<script>
    window.addEventListener('initSelect2', event => {
        $(document).ready(function() {
            $('.select2').select2();

            $('.select2').on('change', function(e) {
                var id = $(this).attr('id');
                var data = $(this).select2("val");
                @this.set(id, data);
            });
        });
    })
</script>
@endpush