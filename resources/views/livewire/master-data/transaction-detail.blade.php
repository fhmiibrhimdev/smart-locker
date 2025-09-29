<div>
    <section class="section custom-section -tw-mt-16">
        <div class="section-header">
            <h1>Pickup Paket</h1>
        </div>

                <div class="section-body">
            <div class="tw-px-4 lg:tw-px-0">
                @if($parcel_id)
                <div class="alert alert-primary tw-shadow-lg tw-shadow-gray-300 tw-mt-8">
                    <i class="fas fa-info-circle"></i> Pastikan untuk mengambil paket sebelum batas waktu pengambilan
                    berakhir.
                </div>
                
                <div class="card tw-rounded-lg ">
                    <div class="card-body tw-text-[#34395e] tw-px-3 tw-py-3 tw-flex tw-items-center">
                        <div class="tw-bg-gray-200 tw-p-2 tw-rounded-lg tw-flex tw-items-center">
                            <div class="tw-bg-white tw-p-1.5 tw-rounded-lg">
                                <div class="tw-w-20 tw-h-20 tw-flex tw-items-center tw-justify-center">
                                    {!! $this->getQrCodeSmall() !!}
                                </div>
                            </div>
                        </div>
                        <div class="tw-ml-6">
                            <p class="tw-text-lg">Gunakan PIN</p>
                            <h3 class="tw-text-4xl tw-font-bold">{{ $kode_pengambilan }}</h3>
                            <p class="tw-text-blue-500 tw-cursor-pointer" data-toggle="modal" data-backdrop="static" data-keyboard="false"
                                data-target="#formDataModal">Show QRCode</p>
                        </div>
                    </div>
                </div>
                
                <div class="card tw-rounded-lg -tw-mt-4">
                    <div class="card-body tw-px-4 tw-py-2 tw-text-[#34395e]">
                        <p class="tw-font-semibold">Durasi Pengambilan</p>
                        @if($timeLeft === 'EXPIRED')
                            <div class="tw-text-center tw-py-8">
                                <div class="tw-text-red-500 tw-text-2xl tw-font-bold">
                                    <i class="fas fa-exclamation-triangle"></i> EXPIRED
                                </div>
                                <p class="tw-text-red-400">Batas waktu pengambilan telah berakhir</p>
                            </div>
                        @else
                            <div class="tw-grid tw-grid-cols-4 tw-gap-4 tw-mt-4" wire:poll.1s>
                                <!-- Hari -->
                                <div class="tw-flex tw-flex-col tw-items-center">
                                    <div class="tw-flex tw-space-x-1">
                                        <div class="tw-bg-gray-100 tw-px-2 tw-rounded-lg tw-text-center">
                                            <p>{{ substr($timeLeft['days'], 0, 1) }}</p>
                                        </div>
                                        <div class="tw-bg-gray-100 tw-px-2 tw-rounded-lg tw-text-center">
                                            <p>{{ substr($timeLeft['days'], 1, 1) }}</p>
                                        </div>
                                    </div>
                                    <p class="tw-mt-2">Hari</p>
                                </div>
                                <!-- Jam -->
                                <div class="tw-flex tw-flex-col tw-items-center">
                                    <div class="tw-flex tw-space-x-1">
                                        <div class="tw-bg-gray-100 tw-px-2 tw-rounded-lg tw-text-center">
                                            <p>{{ substr($timeLeft['hours'], 0, 1) }}</p>
                                        </div>
                                        <div class="tw-bg-gray-100 tw-px-2 tw-rounded-lg tw-text-center">
                                            <p>{{ substr($timeLeft['hours'], 1, 1) }}</p>
                                        </div>
                                    </div>
                                    <p class="tw-mt-2">Jam</p>
                                </div>
                                <!-- Menit -->
                                <div class="tw-flex tw-flex-col tw-items-center">
                                    <div class="tw-flex tw-space-x-1">
                                        <div class="tw-bg-gray-100 tw-px-2 tw-rounded-lg tw-text-center">
                                            <p>{{ substr($timeLeft['minutes'], 0, 1) }}</p>
                                        </div>
                                        <div class="tw-bg-gray-100 tw-px-2 tw-rounded-lg tw-text-center">
                                            <p>{{ substr($timeLeft['minutes'], 1, 1) }}</p>
                                        </div>
                                    </div>
                                    <p class="tw-mt-2">Menit</p>
                                </div>
                                <!-- Detik -->
                                <div class="tw-flex tw-flex-col tw-items-center">
                                    <div class="tw-flex tw-space-x-1">
                                        <div class="tw-bg-gray-100 tw-px-2 tw-rounded-lg tw-text-center">
                                            <p>{{ substr($timeLeft['seconds'], 0, 1) }}</p>
                                        </div>
                                        <div class="tw-bg-gray-100 tw-px-2 tw-rounded-lg tw-text-center">
                                            <p>{{ substr($timeLeft['seconds'], 1, 1) }}</p>
                                        </div>
                                    </div>
                                    <p class="tw-mt-2">Detik</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="tw-grid tw-grid-cols-2 tw-gap-x-4">
                    <div class="card tw-rounded-lg -tw-mt-4">
                        <div class="card-body tw-px-4 tw-py-2 tw-text-[#34395e]">
                            <p class="tw-font-semibold">Nomor Locker</p>
                            <p class="tw-font-bold tw-text-xl">{{ $nomor_loker }}</p>
                        </div>
                    </div>
                    <div class="card tw-rounded-lg -tw-mt-4">
                        <div class="card-body tw-px-4 tw-py-2 tw-text-[#34395e]">
                            <p class="tw-font-semibold">Ukuran Locker</p>
                            <p class="tw-font-bold tw-text-xl">{{ $loker_size }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="card tw-rounded-lg -tw-mt-4">
                    <div class="card-body tw-px-4 tw-py-2 tw-text-[#34395e]">
                        <p class="tw-font-semibold">Kode Paket</p>
                        <p class="tw-text-xl tw-font-bold tw-text-[#34395e]">{{ $kode_paket }}</p>
                    </div>
                </div>
                
                <div class="card tw-rounded-lg -tw-mt-4">
                    <div class="card-body tw-px-4 tw-py-2 tw-text-[#34395e]">
                        <p class="tw-font-semibold">Lokasi</p>
                        <p class="tw-font-semibold tw-text-lg">{{ $nama_lokasi }}</p>
                        @if($alamat)
                            <p class="tw-text-gray-500 tw-mb-2">{{ $alamat }}</p>
                        @endif
                        <button wire:click="openGoogleMaps" class="btn btn-primary tw-w-full tw-my-2">
                            <i class="fas fa-map-marker-alt"></i> 
                            Buka Google Maps @if($latitude && $longitude)(GPS)@endif
                        </button>
                    </div>
                </div>
                
                @else
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Paket dengan kode {{ $kode_paket }} tidak ditemukan.
                </div>
                @endif
            </div>
        </div>
    </section>
    
    <!-- Modal QR Code -->
    @if($parcel_id)
    <div class="modal fade" wire:ignore.self id="formDataModal" aria-labelledby="formDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formDataModalLabel">QR Code untuk Pickup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <center class="tw-text-[#34395e]">
                        <div class="tw-mb-4">
                            {!! $this->generateQRCode() !!}
                        </div>
                        <h4 class="tw-font-bold tw-mb-2">{{ $kode_pengambilan }}</h4>
                        <p class="tw-text-lg tw-mt-4">Tunjukkan QR Code ke scanner locker</p>
                        <div class="tw-mt-4 tw-p-4 tw-bg-blue-50 tw-rounded-lg">
                            <p class="tw-text-sm tw-text-blue-600">
                                <strong>Cara Penggunaan:</strong><br>
                                1. Arahkan QR Code ke scanner di mesin locker<br>
                                2. Tunggu bunyi beep dan LED menyala<br>
                                3. Locker akan terbuka otomatis<br>
                                4. Ambil paket Anda
                            </p>
                        </div>
                    </center>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Handle open Google Maps
    window.addEventListener('open-url', event => {
        window.open(event.detail[0].url, '_blank');
    });
</script>
@endpush
