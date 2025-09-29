<div>
    <section class="section custom-section">
        <div class="section-header">
            <h1>Profile</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <h3>Profile Information</h3>
                <div class="card-body tw-px-6">
                    <p>Update your account's profile information and email address. </p>
                    <div class="mt-3 form-group">
                        <label for="name">Name</label>
                        <input type="text" wire:model="name" class="form-control tw-w-full lg:tw-w-6/12">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" wire:model="email" class="form-control tw-w-full lg:tw-w-6/12">
                    </div>
                    <button wire:click.prevent="updateProfile()" class="btn btn-primary">SAVE PROFILE</button>
                </div>
            </div>
            <div class="card">
                <h3>Update Password</h3>
                <div class="card-body tw-px-6">
                    <p>Ensure your account is using a long, random password to stay secure. </p>
                    <div class="mt-3 form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" wire:model="current_password"
                            class="form-control tw-w-full lg:tw-w-6/12">
                    </div>
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" wire:model="password" class="form-control tw-w-full lg:tw-w-6/12">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" wire:model="password_confirmation"
                            class="form-control tw-w-full lg:tw-w-6/12">
                    </div>
                    <button wire:click.prevent="updatePassword()" class="btn btn-primary">SAVE PASSWORD</button>
                </div>
            </div>
        </div>
    </section>
    @if (Auth::user()->hasRole('admin'))
    @if (!$statusConnected)
    <button class="btn-modal tw-bg-blue-500" data-toggle="modal" data-backdrop="static" data-keyboard="false"
        data-target="#formDataModal">
        <i class="fab fa-whatsapp tw-text-2xl"></i>
    </button>
    @endif
    
    <div class="modal fade" data-backdrop="static" wire:ignore.self id="formDataModal"
        aria-labelledby="formDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formDataModalLabel">Register WhatsApp</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <center>
                            <p class="tw-tracking-wider tw-text-[#34395e] tw-font-semibold tw-text-xl tw-mb-3">Scan Here
                            </p>
                            <div wire:loading wire:target="generateQRCode" class="tw-mb-3">
                                <p class="text-info">Loading QR Code...</p>
                            </div>
                            <div id="qrcode-scan">
                                {!! $qrcodeHtml !!}
                            </div>
                            <button class="btn btn-primary tw-mt-5" wire:click.prevent="generateQRCode">Generate
                                QRCode</button>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
