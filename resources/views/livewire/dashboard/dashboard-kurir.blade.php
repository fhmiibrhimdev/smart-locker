<div>
    <section class='section custom-section'>
        <div class='section-header'>
            <h1>Dashboard Kurir</h1>
        </div>

        <div class='section-body'>

            <div class='card'>
                <h3>Tabel Dashboard Kurir</h3>
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
                                    <th width='6%' class='text-center'>No</th>
                                    <th class='tw-whitespace-nowrap'>Loker</th>
                                    <th class='tw-whitespace-nowrap'>Kurir</th>
                                    <th class='tw-whitespace-nowrap'>Nomor Penerima</th>
                                    <th class='tw-whitespace-nowrap'>Kode Resi</th>
                                    <th class='tw-whitespace-nowrap'>Kode Pengambilan</th>
                                    <th class='tw-whitespace-nowrap'>Status</th>
                                    <th class='tw-whitespace-nowrap'>Expired At</th>
                                    <th class='text-center'><i class='fas fa-cog'></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data->groupBy('nama_lokasi') as $result)
                                <tr>
                                    <td class="tw-font-semibold tw-tracking-wider" colspan="10">Lokasi: {{ $result[0]->nama_lokasi }}</td>
                                </tr>
                                @foreach ($result as $row)
                                <tr class='text-center'>
                                    <td class='tw-whitespace-nowrap'>{{ $loop->index + 1 }}</td>
                                    <td class='text-left tw-whitespace-nowrap'>{{ $row->nomor_loker }}</td>
                                    <td class='text-left tw-whitespace-nowrap'>{{ $row->nama_kurir }}</td>
                                    <td class='text-left tw-whitespace-nowrap'>{{ $row->no_penerima }}</td>
                                    <td class='text-left tw-whitespace-nowrap'>{{ $row->kode_paket }}</td>
                                    <td class='text-left tw-whitespace-nowrap'>{{ $row->kode_pengambilan }}</td>
                                    <td class='text-left tw-whitespace-nowrap'>{{ $row->status }}</td>
                                    <td class='text-left tw-whitespace-nowrap'>
                                        {{ $row->expired_at ? \Carbon\Carbon::parse($row->expired_at)->locale('id')->diffForHumans() : '-' }}
                                    </td>
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
                                    <td colspan='8' class='text-center'>No data available in the table</td>
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
                        @if(!$isLockerOpened)
                        <div class='form-group'>
                            <label for='id_loker'>Loker</label>
                            <select wire:model='id_loker' id='id_loker' class='form-control select2' @if($isEditing) disabled @endif>
                                @foreach ($this->getAvailableLokers()->groupBy('nama_lokasi') as $loker)
                                    <optgroup label='Lokasi: {{ $loker[0]->nama_lokasi }}'>
                                        @foreach ($loker as $lok)
                                            <option value='{{ $lok->id }}'>{{ $lok->nomor_loker }} ({{ $lok->size }})</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('id_loker') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <button wire:click.prevent="openLocker()" class="btn btn-primary form-control -tw-mt-2 tw-mb-4">Open Locker</button>
                        @else
                        <div class='alert alert-success'>
                            <i class='fas fa-check-circle'></i> Locker has been opened successfully! Please fill the parcel details below.
                        </div>
                        @endif
                        
                        @if($isLockerOpened || $isEditing)
                        <div class='form-group'>
                            <label for='no_penerima'>Nomor Penerima</label>
                            <input type='number' wire:model='no_penerima' id='no_penerima' class='form-control'>
                            @error('no_penerima') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='kode_paket'>Kode Resi</label>
                            <input type='text' wire:model='kode_paket' id='kode_paket' class='form-control'>
                            @error('kode_paket') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        <div class='form-group'>
                            <label for='kode_pengambilan'>Kode Pengambilan (Auto Generated)</label>
                            <input type='text' wire:model='kode_pengambilan' id='kode_pengambilan' class='form-control' readonly>
                            @error('kode_pengambilan') <span class='text-danger'>{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </div>
                    <div class='modal-footer'>
                        <button type='button' wire:click='cancel()' class='btn btn-secondary tw-bg-gray-300' data-dismiss='modal'>Close</button>
                        @if($isLockerOpened || $isEditing)
                        <button type='submit' wire:click.prevent='{{ $isEditing ? 'update()' : 'store()' }}' wire:loading.attr='disabled' class='btn btn-primary tw-bg-blue-500'>Save Data</button>
                        @endif
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.2/mqttws31.min.js" type="text/javascript"></script>
@endpush

@push('scripts')
<script>
    let mqttClient;
    let isConnected = false;

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
    
    window.addEventListener('mqtt:publish', event => {
        console.log('Received mqtt:publish event:', event.detail);
        const data = event.detail[0];
        
        if (data && data.topic && data.message) {
            publishToLocker(data.topic, data.message);
        } else {
            console.error('Invalid event data:', data);
            Swal.fire({
                title: 'Error!',
                text: 'Invalid topic or message data.',
                icon: 'error'
            });
        }
    });
    
    function publishToLocker(topic, message) {
        // Show info alert
        Swal.fire({
            title: 'Opening Locker...',
            text: 'Please wait while we open the locker.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        console.log('Publishing to topic:', topic, 'with message:', message);

        if (isConnected && mqttClient) {
            try {
                // Ensure message is a string
                let payload = message || 'OPEN';
                
                // Create MQTT message
                let mqttMessage = new Paho.MQTT.Message(payload);
                mqttMessage.destinationName = topic;
                mqttClient.send(mqttMessage);
                
                console.log(`Published "${payload}" to topic "${topic}"`);
            } catch (error) {
                console.error('Error publishing message:', error);
                Swal.close();
                Swal.fire({
                    title: "Error!",
                    text: "Error: " + error.message,
                    icon: "error",
                });
            }
        } else {
            console.error('MQTT client not connected');
            Swal.close();
            Swal.fire({
                title: "Error!",
                text: "Failed to open locker. Please try again.",
                icon: "error",
            });
        }
    }
</script>
<script>
    function startConnect() {
        clientID = "client_kurir_" + parseInt(Math.random() * 100);
        host = "103.197.190.125";
        port = "9001";
        mqttClient = new Paho.MQTT.Client(host, Number(port), clientID);
        mqttClient.onConnectionLost = onConnectionLost;
        mqttClient.onMessageArrived = onMessageArrived;

        mqttClient.connect({
            onSuccess: onConnect,
            onFailure: onConnectFailure,
            userName: 'nexaryn',
            password: '31750321'
        });
    }

    function onConnect() {
        mqttClient.subscribe("smart_locker/pnj/micro/feedback");
        isConnected = true;
        console.log("Berhasil terhubung ke MQTT");
    }

    function onConnectFailure(responseObject) {
        isConnected = false;
        console.error("Failed to connect to MQTT:", responseObject.errorMessage);
    }

    function onConnectionLost(responseObject) {
        isConnected = false;
        if (responseObject.errorCode !== 0) {
            console.log("Koneksi ke MQTT terputus");
            // Try to reconnect after 5 seconds
            setTimeout(startConnect, 5000);
        }
    }

    function onMessageArrived(message) {
        if (message.destinationName == "smart_locker/pnj/micro/feedback") {
            try {
                let data = message.payloadString;
                console.log('Received feedback:', data);
                
                // Handle feedback from MQTT
                if (data == 'OK') {
                    // Set timeout for success (simulate successful publish)
                    Swal.close();
                    @this.call('lockerOpenedSuccess');
                    Swal.fire({
                        title: "Success!",
                        text: "Locker opened successfully.",
                        icon: "success",
                    })
                }
            } catch (error) {
                console.error('Error parsing MQTT message:', error);
            }
        }
    }

    function startDisconnect() {
        if (mqttClient) {
            mqttClient.disconnect();
            isConnected = false;
        }
    }

    $(document).ready(function () {
        startConnect();
    });
</script>
@endpush