@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0 fw-bold">Selamat Datang di KameraKita!</h3>
                    <p class="mb-0 opacity-75">Mari lengkapi profil Anda sebelum mulai bekerja</p>
                </div>

                <div class="card-body p-5">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Wizard Navigation (Visual only) -->
                    <div class="d-flex justify-content-between mb-5 position-relative">
                        <div class="progress position-absolute w-100" style="top: 50%; transform: translateY(-50%); height: 4px; z-index: 1;">
                            <div class="progress-bar" id="wizard-progress" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="step-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold position-relative" style="width: 40px; height: 40px; z-index: 2;" id="indicator-1">1</div>
                        <div class="step-icon bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold position-relative border" style="width: 40px; height: 40px; z-index: 2;" id="indicator-2">2</div>
                        <div class="step-icon bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold position-relative border" style="width: 40px; height: 40px; z-index: 2;" id="indicator-3">3</div>
                    </div>

                    <form action="{{ route('onboarding.save') }}" method="POST" id="onboarding-form">
                        @csrf

                        <!-- STEP 1: Panduan -->
                        <div class="wizard-step" id="step-1">
                            <h4 class="fw-bold mb-4">Langkah 1: Panduan Dasar Bekerja</h4>
                            <div class="p-4 bg-light rounded border mb-4" style="max-height: 300px; overflow-y: auto;">
                                <h5>Ketentuan Bekerja di KameraKita</h5>
                                <p>Sebagai partner/pekerja di KameraKita, Anda diwajibkan untuk:</p>
                                <ul>
                                    <li>Membaca dan mengikuti seluruh instruksi kerja dengan teliti.</li>
                                    <li>Menjaga kerahasiaan data yang diberikan oleh KameraKita.</li>
                                    <li>Memastikan kualitas kerja sesuai dengan standar yang ditetapkan.</li>
                                    <li>Menjaga komunikasi yang baik dengan tim Quality Control.</li>
                                </ul>
                                <p class="text-muted small"><em>Catatan: Detail panduan lengkap dari PDF akan ditampilkan di sini.</em></p>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-primary px-5" onclick="nextStep(2)">Selanjutnya <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- STEP 2: Data Pembayaran -->
                        <div class="wizard-step d-none" id="step-2">
                            <h4 class="fw-bold mb-4">Langkah 2: Data Pembayaran & Kontak</h4>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor WhatsApp Aktif</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">+62</span>
                                    <input type="text" name="whatsapp_number" class="form-control" placeholder="81234567890" value="{{ old('whatsapp_number', $partner->whatsapp_number ?? '') }}" required>
                                </div>
                                <small class="text-muted">Gunakan nomor yang aktif untuk koordinasi dengan tim.</small>
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">Informasi Rekening Bank (Untuk Payroll)</h6>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Bank</label>
                                <select name="bank_name" class="form-select" required>
                                    <option value="">-- Pilih Bank --</option>
                                    @php
                                        $banks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'BTN', 'Mega'];
                                        $selectedBank = old('bank_name', $partner->bank_name ?? '');
                                    @endphp
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank }}" {{ $selectedBank == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor Rekening</label>
                                <input type="text" name="bank_account_number" class="form-control" placeholder="Contoh: 1234567890" value="{{ old('bank_account_number', $partner->bank_account_number ?? '') }}" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama Pemilik Rekening</label>
                                <input type="text" name="bank_account_owner" class="form-control" placeholder="Sesuai buku tabungan" value="{{ old('bank_account_owner', $partner->bank_account_owner ?? '') }}" required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary px-4" onclick="nextStep(1)"><i class="bi bi-arrow-left me-2"></i> Kembali</button>
                                <button type="button" class="btn btn-primary px-5" onclick="nextStep(3)">Selanjutnya <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- STEP 3: Persetujuan (Consent) -->
                        <div class="wizard-step d-none" id="step-3">
                            <h4 class="fw-bold mb-4">Langkah 3: Syarat & Ketentuan (ToS)</h4>
                            
                            <div class="p-4 bg-light rounded border mb-4 text-sm" style="max-height: 250px; overflow-y: auto; font-size: 0.9rem;">
                                <h6 class="fw-bold">Persetujuan Kepatuhan & Kerahasiaan Data (Consent Form)</h6>
                                <p>Dengan mendaftar sebagai partner/pekerja di sistem KameraKita, saya menyatakan bahwa:</p>
                                <ol>
                                    <li><strong>Kerahasiaan Data (NDA):</strong> Saya setuju untuk menjaga kerahasiaan seluruh data proyek, video, dan informasi internal KameraKita. Saya tidak akan menyebarkan, mengunduh secara ilegal, atau memperjualbelikan data tersebut kepada pihak manapun.</li>
                                    <li><strong>Izin Penggunaan Data Diri:</strong> Saya memberikan izin kepada KameraKita untuk menyimpan dan menggunakan data pribadi saya (Nomor WhatsApp, Rekening Bank, dll) secara eksklusif untuk keperluan internal seperti komunikasi kerja dan pembayaran (payroll).</li>
                                    <li><strong>Kepatuhan Aturan Kerja:</strong> Saya bersedia mengikuti semua Standar Operasional Prosedur (SOP) dan arahan kerja yang ditetapkan. Pelanggaran terhadap SOP dapat berakibat pada penalti atau pemutusan hubungan kemitraan.</li>
                                </ol>
                                <p class="mb-0 fw-bold text-danger">Jika terbukti melakukan pelanggaran fatal terkait kebocoran data, KameraKita berhak memproses secara hukum yang berlaku.</p>
                            </div>

                            <div class="form-check mb-4 bg-warning bg-opacity-10 p-3 rounded border border-warning">
                                <input class="form-check-input ms-1" type="checkbox" name="tos_accepted" id="tos_accepted" value="1" required style="transform: scale(1.5); margin-top: 0.2rem;">
                                <label class="form-check-label ms-3 fw-bold" for="tos_accepted">
                                    Saya telah membaca, memahami, dan menyetujui seluruh Syarat, Ketentuan, dan Aturan Kerahasiaan Data di atas.
                                </label>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary px-4" onclick="nextStep(2)"><i class="bi bi-arrow-left me-2"></i> Kembali</button>
                                <button type="submit" class="btn btn-success px-5 fw-bold" id="btn-submit" disabled><i class="bi bi-check-circle me-2"></i> Selesai & Mulai Bekerja</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function nextStep(step) {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('d-none'));
        // Show target step
        document.getElementById('step-' + step).classList.remove('d-none');
        
        // Update progress bar
        let progress = (step - 1) * 50;
        document.getElementById('wizard-progress').style.width = progress + '%';
        
        // Update indicators
        for(let i=1; i<=3; i++) {
            let ind = document.getElementById('indicator-' + i);
            if(i <= step) {
                ind.classList.remove('bg-light', 'text-secondary', 'border');
                ind.classList.add('bg-primary', 'text-white');
            } else {
                ind.classList.remove('bg-primary', 'text-white');
                ind.classList.add('bg-light', 'text-secondary', 'border');
            }
        }
    }

    // Enable/Disable submit button based on checkbox
    document.getElementById('tos_accepted').addEventListener('change', function() {
        document.getElementById('btn-submit').disabled = !this.checked;
    });
</script>
@endsection
