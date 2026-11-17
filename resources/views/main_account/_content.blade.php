<style>
    .kk-hero-banner {
        background: linear-gradient(110deg, #229ed0 -5%, #0284c7 64%, #0788c9 100%);
        border-radius: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(2, 132, 199, 0.22);
    }
    .kk-eyebrow-yellow {
        background-color: #fde047;
        color: #0369a1;
        font-weight: 800;
        letter-spacing: 0.08em;
    }
    .kk-benefit-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        transition: transform 240ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 240ms ease, border-color 240ms ease;
    }
    .kk-benefit-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 55px rgba(2, 132, 199, 0.12);
        border-color: #7dd3fc;
    }
    .kk-form-shell {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 32px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
        position: relative;
        overflow: hidden;
    }
    .kk-btn-blue {
        background-color: #0284c7;
        color: #ffffff;
        box-shadow: 0 10px 20px -5px rgba(2, 132, 199, 0.38);
        transition: transform 200ms ease, background-color 200ms ease, box-shadow 200ms ease;
    }
    .kk-btn-blue:hover {
        background-color: #0369a1;
        transform: translateY(-1px);
        box-shadow: 0 14px 24px -6px rgba(2, 132, 199, 0.45);
    }
</style>

<!-- Session Notifications -->
@if(session('success'))
<div class="bg-white border-2 border-emerald-400 p-6 rounded-2xl shadow-sm">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <h3 class="text-base font-bold text-slate-900">Pengajuan Berhasil Dikirim</h3>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ session('success') }}</p>
            <p class="text-[11px] text-slate-500 mt-2 font-medium">Tim admin kami akan memverifikasi dan mengaktifkan peran Main Account Anda. Notifikasi aktivasi akan dikirim melalui WhatsApp.</p>
        </div>
    </div>
</div>
@endif

@if(session('info'))
<div class="bg-white border border-sky-200 p-5 rounded-2xl shadow-sm flex items-start gap-3.5">
    <svg class="w-5 h-5 text-sky-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="text-xs text-slate-700 leading-relaxed font-medium">
        {{ session('info') }}
    </div>
</div>
@endif

@if(session('error'))
<div class="bg-white border border-rose-200 p-5 rounded-2xl shadow-sm flex items-start gap-3.5">
    <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="text-xs text-rose-700 leading-relaxed font-medium">
        {{ session('error') }}
    </div>
</div>
@endif

<!-- Active Main Account State -->
@if(isset($partner) && $partner && $partner->isMainAccount())
<div class="kk-hero-banner text-white p-8">
    <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
        <div class="max-w-xl space-y-3">
            <span class="kk-eyebrow-yellow inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] uppercase">
                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                Status: Main Account Aktif
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Akun Anda Sudah Berstatus Main Account</h2>
            <p class="text-sm text-sky-50 leading-relaxed">
                Anda telah memiliki akses penuh untuk mendaftarkan dan mengelola seluruh sub-akun di bawah akun utama Anda dengan rate penuh Rp60.000/jam.
            </p>
            <div class="pt-2 flex flex-wrap gap-3">
                <a href="{{ route('vendor.workers.index') }}" class="px-5 py-2.5 bg-white text-sky-700 hover:bg-sky-50 rounded-xl text-xs font-extrabold transition shadow-md">
                    Buka Kelola Sub-Akun Saya &rarr;
                </a>
                <a href="{{ route('vendor.recordings.index') }}" class="px-5 py-2.5 bg-white/15 hover:bg-white/25 text-white rounded-xl text-xs font-bold transition border border-white/30">
                    Lihat Recording Semua Akun
                </a>
            </div>
        </div>
        <img src="{{ asset('assets/figma/steps-03.png') }}" alt="Main Account Aktif" class="w-36 h-36 object-contain shrink-0 drop-shadow-xl">
    </div>
</div>
@elseif(isset($pendingRequest) && $pendingRequest)
<!-- Waiting List State -->
<div class="bg-white border-2 border-amber-300 p-6 rounded-3xl shadow-sm">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="space-y-1.5 flex-1">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-amber-100 text-amber-900 rounded-full text-[11px] font-bold uppercase tracking-wider">
                Sedang Dalam Antrean Review
            </div>
            <h3 class="text-base font-bold text-slate-900">Pengajuan Akun Induk Anda Sedang Diproses</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Pengajuan untuk email <strong>{{ $pendingRequest->email }}</strong> telah tercatat pada <strong>{{ $pendingRequest->created_at->format('d M Y, H:i') }}</strong>. Tim admin kami sedang memvalidasi data Anda. Begitu disetujui, role akun Anda akan langsung aktif sebagai Main Account.
            </p>
            @if($pendingRequest->notes)
            <div class="mt-2 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600">
                <span class="font-bold text-slate-800">Catatan Pengajuan:</span> {{ $pendingRequest->notes }}
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Hero Showcase Matching Landing Page -->
@if(!isset($partner) || !$partner || !$partner->isMainAccount())
<div class="kk-hero-banner p-7 sm:p-10 text-white">
    <!-- Subtle decorative coins from landing page -->
    <img src="{{ asset('assets/figma/hero-13.png') }}" alt="" aria-hidden="true" class="hidden md:block absolute right-6 top-4 w-16 h-16 object-contain opacity-90 pointer-events-none">
    <img src="{{ asset('assets/figma/hero-16.png') }}" alt="" aria-hidden="true" class="hidden md:block absolute right-48 bottom-3 w-12 h-12 object-contain opacity-80 pointer-events-none">

    <div class="grid grid-cols-1 md:grid-cols-12 items-center gap-6 relative z-10">
        <div class="md:col-span-8 space-y-3">
            <span class="kk-eyebrow-yellow inline-block px-3.5 py-1 rounded-full text-[11px] uppercase">
                KEUNGGULAN AKUN INDUK
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight text-white">
                Satu Akun Induk untuk Seluruh Email Atlas Anda
            </h1>
            <p class="text-sm text-sky-50 leading-relaxed max-w-2xl">
                Dirancang khusus bagi kontributor yang sering mengganti email pengerjaan. Kelola seluruh sub-akun, pantau OTP, dan cairkan seluruh penghasilan dalam satu pintu tanpa potongan komisi.
            </p>
        </div>
        <div class="md:col-span-4 flex justify-center">
            <img src="{{ asset('assets/figma/benefits-record-figma.png') }}" alt="Ilustrasi Kontributor KameraKita" class="w-40 h-40 sm:w-48 sm:h-48 object-contain drop-shadow-2xl">
        </div>
    </div>
</div>
@endif

<!-- 4 Landing-Page Style Benefit Cards (No Emojis, Using Landing Page Figma Assets) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <!-- Benefit 1 -->
    <article class="kk-benefit-card p-6 text-center flex flex-col items-center">
        <div class="w-28 h-28 mb-4 flex items-center justify-center">
            <img src="{{ asset('assets/figma/benefits-wallet-figma.png') }}" alt="Rate Penuh Rp60.000 per Jam" class="w-full h-full object-contain">
        </div>
        <h3 class="text-base font-extrabold tracking-tight mb-2" style="color: #0284c7;">Rate Penuh Rp60.000/Jam</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
            Tidak ada potongan sepeser pun. 100% hasil kerja seluruh sub-akun adalah milik Anda sepenuhnya.
        </p>
    </article>

    <!-- Benefit 2 -->
    <article class="kk-benefit-card p-6 text-center flex flex-col items-center">
        <div class="w-28 h-28 mb-4 flex items-center justify-center">
            <img src="{{ asset('assets/figma/benefits-ai-figma.png') }}" alt="Kotak Masuk OTP Terpadu" class="w-full h-full object-contain">
        </div>
        <h3 class="text-base font-extrabold tracking-tight mb-2" style="color: #0284c7;">Kotak Masuk OTP Terpadu</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
            Seluruh kode OTP verifikasi email sub-akun langsung muncul di dashboard Anda tanpa perlu buka banyak Gmail.
        </p>
    </article>

    <!-- Benefit 3 -->
    <article class="kk-benefit-card p-6 text-center flex flex-col items-center">
        <div class="w-28 h-28 mb-4 flex items-center justify-center">
            <img src="{{ asset('assets/figma/steps-03.png') }}" alt="Tracking Terpusat" class="w-full h-full object-contain">
        </div>
        <h3 class="text-base font-extrabold tracking-tight mb-2" style="color: #0284c7;">Tracking Terpusat</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
            Pantau total jam kerja, recording, dan status verifikasi QC seluruh email pengerjaan Anda dalam satu layar.
        </p>
    </article>

    <!-- Benefit 4 -->
    <article class="kk-benefit-card p-6 text-center flex flex-col items-center">
        <div class="w-28 h-28 mb-4 flex items-center justify-center">
            <img src="{{ asset('assets/figma/steps-09.png') }}" alt="Pencairan 1 Rekening" class="w-full h-full object-contain">
        </div>
        <h3 class="text-base font-extrabold tracking-tight mb-2" style="color: #0284c7;">Pencairan 1 Rekening</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
            Penggajian otomatis ditransfer ke satu rekening utama yang Anda daftarkan, aman dan bebas risiko tertukar.
        </p>
    </article>
</div>

@if(!isset($partner) || !$partner || (!$partner->isMainAccount() && (!isset($pendingRequest) || !$pendingRequest)))
<!-- Application Form Shell (Matching Landing Page Calculator Shell) -->
<div class="kk-form-shell">
    <div class="p-6 sm:p-8 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shadow-sm shrink-0" style="background-color: #0284c7;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Formulir Pengajuan Akun Induk</h3>
                <p class="text-xs text-slate-500 mt-0.5">Data profil terisi otomatis. Anda dapat memperbarui nomor WhatsApp &amp; rekening jika diperlukan.</p>
            </div>
        </div>
        @if(isset($partner) && $partner?->mitra_id)
            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700">
                ID Kontributor: {{ $partner->mitra_id }}
            </span>
        @endif
    </div>

    @php
        $prefilledPhone = old('phone_number', $partner?->whatsapp_number ?? '');
        $prefilledBankName = old('bank_name', $partner?->bank_name ?? '');
        $prefilledBankNumber = old('bank_account_number', $partner?->bank_account_number ?: ($partner?->account_number ?? ''));
        $prefilledBankOwner = old('bank_account_owner', $partner?->bank_account_owner ?: ($partner?->account_owner_name ?? ''));
    @endphp

    <form method="POST" action="{{ route('main-account.request.store') }}" class="p-6 sm:p-8 space-y-6 bg-white">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Email Akun Terdaftar (Locked) -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="email" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                        Email Akun Terdaftar <span class="text-rose-500">*</span>
                    </label>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Terkunci
                    </span>
                </div>
                <input type="email" id="email" name="email" value="{{ $user->email }}" readonly
                       class="w-full text-xs font-semibold rounded-xl border border-slate-200 px-3.5 py-3 text-slate-600 bg-slate-100 cursor-not-allowed">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                <p class="text-[11px] text-slate-400 mt-1.5">Email mengikuti akun yang sedang login dan tidak dapat diubah.</p>
            </div>

            <!-- Password Konfirmasi Keamanan -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Kata Sandi Akun <span class="text-rose-500">*</span>
                </label>
                <input type="password" id="password" name="password" required
                       class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-3 text-slate-900 bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                       placeholder="Masukkan kata sandi akun Anda saat ini">
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
                <p class="text-[11px] text-slate-400 mt-1.5">Verifikasi keamanan untuk memastikan Anda adalah pemilik sah akun ini.</p>
            </div>

            <!-- WhatsApp Number (Auto-filled & Editable) -->
            <div>
                <label for="phone_number" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Nomor WhatsApp Aktif
                </label>
                <input type="text" id="phone_number" name="phone_number"
                       value="{{ $prefilledPhone }}"
                       class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-3 text-slate-900 bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                       placeholder="Contoh: 081234567890">
                <p class="text-[11px] text-slate-400 mt-1.5">Digunakan untuk notifikasi WhatsApp dari admin saat pengajuan disetujui (dapat diubah).</p>
            </div>

            <!-- Estimasi Akun yang Dikelola -->
            <div>
                <label for="estimated_accounts" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                    Perkiraan Jumlah Akun yang Dikelola
                </label>
                <select id="estimated_accounts" name="estimated_accounts"
                        class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-3 text-slate-900 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white">
                    <option value="2-5 Akun" {{ old('estimated_accounts') == '2-5 Akun' ? 'selected' : '' }}>2 – 5 Akun Email</option>
                    <option value="6-10 Akun" {{ old('estimated_accounts') == '6-10 Akun' ? 'selected' : '' }}>6 – 10 Akun Email</option>
                    <option value="11-20 Akun" {{ old('estimated_accounts') == '11-20 Akun' ? 'selected' : '' }}>11 – 20 Akun Email</option>
                    <option value="> 20 Akun" {{ old('estimated_accounts') == '> 20 Akun' ? 'selected' : '' }}>Lebih dari 20 Akun</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1.5">Membantu admin memetakan kapasitas akun dan mailbox Anda.</p>
            </div>
        </div>

        <!-- Bagian Data Rekening Bank (Auto-filled & Editable) -->
        <div class="pt-5 border-t border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-3">
                <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Informasi Rekening Pembayaran (Untuk Payroll Sub-Akun)</h4>
                <span class="text-[11px] text-slate-400">Terisi otomatis dari profil Anda, silakan edit jika ingin mengganti rekening</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="bank_name" class="block text-[11px] font-bold text-slate-500 uppercase mb-1.5">Nama Bank / E-Wallet</label>
                    <input type="text" id="bank_name" name="bank_name"
                           value="{{ $prefilledBankName }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-900 bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                           placeholder="BCA / BRI / Mandiri / GoPay">
                </div>
                <div>
                    <label for="bank_account_number" class="block text-[11px] font-bold text-slate-500 uppercase mb-1.5">Nomor Rekening</label>
                    <input type="text" id="bank_account_number" name="bank_account_number"
                           value="{{ $prefilledBankNumber }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-900 bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                           placeholder="Contoh: 1234567890">
                </div>
                <div>
                    <label for="bank_account_owner" class="block text-[11px] font-bold text-slate-500 uppercase mb-1.5">Nama Pemilik Rekening</label>
                    <input type="text" id="bank_account_owner" name="bank_account_owner"
                           value="{{ $prefilledBankOwner }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-900 bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                           placeholder="Sesuai buku tabungan">
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Seluruh pembayaran dari pekerjaan yang disetujui pada sub-akun Anda akan ditransfer ke rekening di atas.</p>
        </div>

        <!-- Catatan Tambahan (Opsional) -->
        <div>
            <label for="notes" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                Catatan Tambahan (Opsional)
            </label>
            <textarea id="notes" name="notes" rows="3"
                      class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-3 text-slate-900 bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                      placeholder="Tuliskan pengalaman atau catatan khusus jika ada...">{{ old('notes') }}</textarea>
        </div>

        <!-- Syarat & Ketentuan Centang -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-start gap-3">
            <input type="checkbox" id="terms" required checked
                   class="mt-0.5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
            <label for="terms" class="text-xs text-slate-600 leading-relaxed cursor-pointer select-none">
                Saya memahami bahwa akun ini akan menjadi <strong>Akun Induk (Main Account)</strong> dengan rate tetap Rp60.000/jam tanpa potongan, dan seluruh sub-akun di bawah akun ini akan menginduk pada data pembayaran di atas.
            </label>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="kk-btn-blue px-7 py-3.5 rounded-xl text-xs font-extrabold flex items-center gap-2">
                <span>Kirim Pengajuan Akun Induk</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>
</div>
@endif

<!-- Pertanyaan Umum (FAQ) -->
<div class="kk-form-shell p-6 sm:p-8 space-y-4">
    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Pertanyaan yang Sering Diajukan (FAQ)</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-600">
        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-1">
            <p class="font-bold text-slate-800">Apakah ada biaya untuk upgrade ke Akun Induk?</p>
            <p class="leading-relaxed text-slate-500">Gratis 100%. Fitur ini disediakan untuk mempermudah operasional worker yang produktif.</p>
        </div>
        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-1">
            <p class="font-bold text-slate-800">Berapa lama proses persetujuan oleh admin?</p>
            <p class="leading-relaxed text-slate-500">Admin akan mereview pengajuan dalam kurun waktu 1x24 jam kerja.</p>
        </div>
        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-1">
            <p class="font-bold text-slate-800">Bagaimana cara mendaftarkan sub-akun setelah disetujui?</p>
            <p class="leading-relaxed text-slate-500">Setelah disetujui, Anda akan mendapatkan menu "Sub-Akun Saya" di dashboard untuk menambahkan akun baru atau membagikan link pendaftaran Anda.</p>
        </div>
        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-1">
            <p class="font-bold text-slate-800">Apakah rate gaji sub-akun sama?</p>
            <p class="leading-relaxed text-slate-500">Ya, seluruh sub-akun di bawah Akun Induk mendapatkan rate utuh Rp60.000/jam tanpa potongan komisi mitra.</p>
        </div>
    </div>
</div>
