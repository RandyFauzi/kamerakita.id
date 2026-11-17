<!-- Session Notifications -->
@if(session('success'))
<div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border-2 border-emerald-400 p-6 rounded-2xl shadow-sm">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <h3 class="text-base font-bold text-gray-900">Pengajuan Berhasil Dikirim!</h3>
            <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ session('success') }}</p>
            <p class="text-[11px] text-gray-500 mt-2 font-medium">Tim admin kami akan memverifikasi dan mengaktifkan peran Main Account Anda. Notifikasi aktivasi akan kami kirimkan.</p>
        </div>
    </div>
</div>
@endif

@if(session('info'))
<div class="bg-blue-50 border border-blue-200 p-5 rounded-2xl shadow-sm flex items-start gap-3.5">
    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="text-xs text-blue-800 leading-relaxed font-medium">
        {{ session('info') }}
    </div>
</div>
@endif

@if(session('error'))
<div class="bg-rose-50 border border-rose-200 p-5 rounded-2xl shadow-sm flex items-start gap-3.5">
    <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="text-xs text-rose-800 leading-relaxed font-medium">
        {{ session('error') }}
    </div>
</div>
@endif

<!-- Active Main Account State -->
@if(isset($partner) && $partner && $partner->isMainAccount())
<div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 text-white p-8 rounded-3xl shadow-xl relative overflow-hidden">
    <div class="relative z-10 max-w-2xl space-y-3">
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full text-xs font-bold uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Status: Main Account Aktif
        </div>
        <h2 class="text-2xl font-black tracking-tight text-white">Akun Anda Sudah Berstatus Main Account!</h2>
        <p class="text-xs text-slate-300 leading-relaxed">
            Anda telah memiliki akses penuh untuk mendaftarkan dan mengelola seluruh sub-akun di bawah akun utama Anda dengan rate penuh Rp60.000/jam.
        </p>
        <div class="pt-4 flex flex-wrap gap-3">
            <a href="{{ route('vendor.workers.index') }}" class="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-indigo-500/25">
                Buka Kelola Sub-Akun Saya &rarr;
            </a>
            <a href="{{ route('vendor.recordings.index') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition border border-white/10">
                Lihat Recording Semua Akun
            </a>
        </div>
    </div>
</div>
@elseif(isset($pendingRequest) && $pendingRequest)
<!-- Waiting List State -->
<div class="bg-amber-50 border-2 border-amber-300 p-6 rounded-3xl shadow-sm">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="space-y-1.5 flex-1">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-amber-200/80 text-amber-900 rounded-full text-[11px] font-bold uppercase tracking-wider">
                Sedang Dalam Antrean Review
            </div>
            <h3 class="text-base font-bold text-gray-900">Pengajuan Akun Induk Anda Sedang Diproses</h3>
            <p class="text-xs text-gray-600 leading-relaxed">
                Pengajuan untuk email <strong>{{ $pendingRequest->email }}</strong> telah tercatat pada <strong>{{ $pendingRequest->created_at->format('d M Y, H:i') }}</strong>. Tim admin kami sedang memvalidasi data Anda. Begitu disetujui, role akun Anda akan langsung aktif sebagai Main Account!
            </p>
            @if($pendingRequest->notes)
            <div class="mt-2 p-3 bg-white/80 rounded-xl border border-amber-200/80 text-xs text-gray-600">
                <span class="font-bold text-gray-700">Catatan Pengajuan:</span> {{ $pendingRequest->notes }}
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Hero Header & Benefits Cards -->
<div class="space-y-6">
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
            Satu Akun Induk untuk Seluruh Email Atlas Anda
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
            Khusus worker produktif yang memiliki banyak email kerja. Kelola, pantau, dan cairkan seluruh penghasilan secara transparan tanpa potongan komisi.
        </p>
    </div>

    <!-- 4 Benefit Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Benefit 1 -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm hover:border-indigo-300 transition group">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base mb-3 group-hover:scale-105 transition-transform">
                💰
            </div>
            <h3 class="text-sm font-bold text-gray-900 mb-1">Rate Penuh Rp60.000/Jam</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Tidak ada potongan sepeser pun. 100% hasil kerja seluruh sub-akun adalah milik Anda sepenuhnya.
            </p>
        </div>

        <!-- Benefit 2 -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm hover:border-indigo-300 transition group">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base mb-3 group-hover:scale-105 transition-transform">
                📬
            </div>
            <h3 class="text-sm font-bold text-gray-900 mb-1">Kotak Masuk OTP Terpadu</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Seluruh kode OTP verifikasi email sub-akun langsung muncul di dashboard Anda. Nggak perlu buka banyak Gmail!
            </p>
        </div>

        <!-- Benefit 3 -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm hover:border-indigo-300 transition group">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-base mb-3 group-hover:scale-105 transition-transform">
                📊
            </div>
            <h3 class="text-sm font-bold text-gray-900 mb-1">Tracking Terpusat</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Pantau total jam kerja, recording, dan status QC seluruh email pengerjaan Anda dalam satu tampilan layar.
            </p>
        </div>

        <!-- Benefit 4 -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-sm hover:border-indigo-300 transition group">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base mb-3 group-hover:scale-105 transition-transform">
                💳
            </div>
            <h3 class="text-sm font-bold text-gray-900 mb-1">Pencairan 1 Rekening</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Penggajian otomatis ditransfer ke satu rekening utama yang Anda daftarkan, aman dan bebas risiko tertukar.
            </p>
        </div>
    </div>
</div>

@if(!isset($partner) || !$partner || (!$partner->isMainAccount() && (!isset($pendingRequest) || !$pendingRequest)))
<!-- Application Form Card -->
<div class="bg-white border border-gray-200/80 rounded-3xl shadow-sm overflow-hidden">
    <div class="p-6 sm:p-8 border-b border-gray-100 bg-gradient-to-r from-gray-50/50 to-white">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-gray-900 tracking-tight">Formulir Pengajuan Akun Induk</h3>
                <p class="text-xs text-gray-500 mt-0.5">Lengkapi data verifikasi berikut untuk mengajukan akun Anda.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('main-account.request.store') }}" class="p-6 sm:p-8 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Email Akun Terdaftar -->
            <div>
                <label for="email" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Email Akun Terdaftar <span class="text-rose-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                       {{ isset($user) && $user ? 'readonly' : '' }}
                       class="w-full text-xs rounded-xl border border-gray-200 px-3.5 py-3 text-gray-800 {{ isset($user) && $user ? 'bg-gray-100 cursor-not-allowed' : 'focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500' }}"
                       placeholder="emailanda@kamerakitaid.site">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                <p class="text-[11px] text-gray-400 mt-1">Akun ini yang nantinya akan ditingkatkan menjadi Akun Induk utama.</p>
            </div>

            <!-- Password Konfirmasi Keamanan -->
            <div>
                <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Kata Sandi Akun <span class="text-rose-500">*</span>
                </label>
                <input type="password" id="password" name="password" required
                       class="w-full text-xs rounded-xl border border-gray-200 px-3.5 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Masukkan kata sandi akun Anda">
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
                <p class="text-[11px] text-gray-400 mt-1">Verifikasi keamanan untuk memastikan Anda adalah pemilik sah akun ini.</p>
            </div>

            <!-- WhatsApp Number -->
            <div>
                <label for="phone_number" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Nomor WhatsApp Aktif
                </label>
                <input type="text" id="phone_number" name="phone_number"
                       value="{{ old('phone_number', $partner->whatsapp_number ?? '') }}"
                       class="w-full text-xs rounded-xl border border-gray-200 px-3.5 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Contoh: 081234567890">
                <p class="text-[11px] text-gray-400 mt-1">Digunakan untuk notifikasi WhatsApp dari admin saat pengajuan disetujui.</p>
            </div>

            <!-- Estimasi Akun yang Dikelola -->
            <div>
                <label for="estimated_accounts" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Perkiraan Jumlah Akun yang Dikelola
                </label>
                <select id="estimated_accounts" name="estimated_accounts"
                        class="w-full text-xs rounded-xl border border-gray-200 px-3.5 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="2-5 Akun" {{ old('estimated_accounts') == '2-5 Akun' ? 'selected' : '' }}>2 – 5 Akun Email</option>
                    <option value="6-10 Akun" {{ old('estimated_accounts') == '6-10 Akun' ? 'selected' : '' }}>6 – 10 Akun Email</option>
                    <option value="11-20 Akun" {{ old('estimated_accounts') == '11-20 Akun' ? 'selected' : '' }}>11 – 20 Akun Email</option>
                    <option value="> 20 Akun" {{ old('estimated_accounts') == '> 20 Akun' ? 'selected' : '' }}>Lebih dari 20 Akun</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Membantu admin memetakan kebutuhan server dan mailbox Anda.</p>
            </div>
        </div>

        <!-- Bagian Data Rekening Bank untuk Penggajian Terpusat -->
        <div class="pt-4 border-t border-gray-100">
            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">Informasi Rekening Pembayaran (Untuk Payroll Sub-Akun)</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="bank_name" class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Nama Bank / E-Wallet</label>
                    <input type="text" id="bank_name" name="bank_name"
                           value="{{ old('bank_name', $partner->bank_name ?? '') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 px-3.5 py-2.5 text-gray-800"
                           placeholder="BCA / BRI / Mandiri / GoPay">
                </div>
                <div>
                    <label for="bank_account_number" class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Nomor Rekening</label>
                    <input type="text" id="bank_account_number" name="bank_account_number"
                           value="{{ old('bank_account_number', $partner->bank_account_number ?? '') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 px-3.5 py-2.5 text-gray-800"
                           placeholder="Contoh: 1234567890">
                </div>
                <div>
                    <label for="bank_account_owner" class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Nama Pemilik Rekening</label>
                    <input type="text" id="bank_account_owner" name="bank_account_owner"
                           value="{{ old('bank_account_owner', $partner->bank_account_owner ?? '') }}"
                           class="w-full text-xs rounded-xl border border-gray-200 px-3.5 py-2.5 text-gray-800"
                           placeholder="Sesuai buku tabungan">
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Seluruh pembayaran dari video yang disetujui pada seluruh sub-akun Anda akan ditransfer ke rekening di atas.</p>
        </div>

        <!-- Catatan Tambahan (Opsional) -->
        <div>
            <label for="notes" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                Catatan Tambahan (Opsional)
            </label>
            <textarea id="notes" name="notes" rows="3"
                      class="w-full text-xs rounded-xl border border-gray-200 px-3.5 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Tuliskan pengalaman atau catatan khusus jika ada...">{{ old('notes') }}</textarea>
        </div>

        <!-- Syarat & Ketentuan Centang -->
        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-start gap-3">
            <input type="checkbox" id="terms" required checked
                   class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="terms" class="text-xs text-gray-600 leading-relaxed cursor-pointer select-none">
                Saya memahami bahwa akun ini akan menjadi <strong>Akun Induk (Main Account)</strong> dengan rate tetap Rp60.000/jam tanpa potongan, dan seluruh sub-akun di bawah akun ini akan menginduk pada data pembayaran di atas.
            </label>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-indigo-600/25 flex items-center gap-2">
                <span>Kirim Pengajuan Akun Induk</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>
</div>
@endif

<!-- Pertanyaan Umum (FAQ) -->
<div class="bg-white border border-gray-200/80 rounded-3xl p-6 sm:p-8 space-y-4">
    <h3 class="text-base font-bold text-gray-900 tracking-tight">Pertanyaan yang Sering Diajukan (FAQ)</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600">
        <div class="p-4 bg-gray-50 rounded-2xl space-y-1">
            <p class="font-bold text-gray-800">Apakah ada biaya untuk upgrade ke Akun Induk?</p>
            <p class="leading-relaxed text-gray-500">Gratis 100%. Fitur ini disediakan untuk mempermudah operasional worker yang produktif.</p>
        </div>
        <div class="p-4 bg-gray-50 rounded-2xl space-y-1">
            <p class="font-bold text-gray-800">Berapa lama proses persetujuan oleh admin?</p>
            <p class="leading-relaxed text-gray-500">Admin akan mereview pengajuan dalam kurun waktu 1x24 jam kerja.</p>
        </div>
        <div class="p-4 bg-gray-50 rounded-2xl space-y-1">
            <p class="font-bold text-gray-800">Bagaimana cara mendaftarkan sub-akun setelah disetujui?</p>
            <p class="leading-relaxed text-gray-500">Setelah disetujui, Anda akan mendapatkan menu "Sub-Akun Saya" di dashboard untuk menambahkan akun baru atau membagikan link pendaftaran Anda.</p>
        </div>
        <div class="p-4 bg-gray-50 rounded-2xl space-y-1">
            <p class="font-bold text-gray-800">Apakah rate gaji sub-akun sama?</p>
            <p class="leading-relaxed text-gray-500">Ya, seluruh sub-akun di bawah Akun Induk mendapatkan rate utuh Rp60.000/jam tanpa potongan komisi mitra.</p>
        </div>
    </div>
</div>
