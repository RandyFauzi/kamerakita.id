<x-app-layout>
    <x-slot:title>Dashboard Rekruter</x-slot:title>
    <x-slot:metaDescription>Pantau metrik rekrutmen, komisi reward, tracking video approved per worker, dan penagihan komisi Rekruter Kamerakita.</x-slot:metaDescription>

    @php
        $adminWa = config('services.whatsapp.admin_number', env('ADMIN_WHATSAPP_NUMBER', '6287886272647'));

        // Serialize workers data cleanly for Alpine.js
        $workersList = collect($metrics['workers_data'] ?? [])->map(function ($item) {
            $w = $item['worker'];
            $cleanPhone = preg_replace('/[^0-9]/', '', $w->whatsapp_number ?? '');
            if (str_starts_with($cleanPhone, '08')) {
                $cleanPhone = '628' . substr($cleanPhone, 2);
            }
            $progress = min(100, (int) round(($item['approved_hours'] / 20) * 100));
            $remaining = max(0, round(20 - $item['approved_hours'], 1));
            $regDate = $w->registration_date ?? $w->created_at ?? $w->user?->created_at;
            $regDateFormatted = $regDate ? \Carbon\Carbon::parse($regDate)->translatedFormat('d M Y') : '-';

            return [
                'id' => $w->id,
                'mitra_id' => $w->mitra_id ?? '-',
                'full_name' => $w->full_name ?? '-',
                'email' => $w->email ?? '-',
                'whatsapp_number' => $w->whatsapp_number ?? '-',
                'whatsapp_clean' => $cleanPhone,
                'registered_at' => $regDateFormatted,
                'bank_name' => $w->bank_name ?? '-',
                'bank_account_number' => $w->bank_account_number ?? '-',
                'bank_account_owner' => $w->bank_account_owner ?? '-',
                'reported_hours' => $item['reported_hours'],
                'reported_hours_formatted' => $item['reported_hours_formatted'],
                'reported_videos_count' => $item['reported_videos_count'],
                'approved_hours' => $item['approved_hours'],
                'approved_hours_formatted' => $item['approved_hours_formatted'],
                'approved_videos_count' => $item['approved_videos_count'],
                'approved_minutes' => $item['approved_minutes'],
                'milestone_reached' => (bool) $item['milestone_reached'],
                'milestone_status' => $item['milestone_status'] ?? 'none',
                'commission_amount' => $item['commission_amount'],
                'progress_percent' => $progress,
                'remaining_hours' => $remaining,
            ];
        })->values();

        // Build WhatsApp & Clipboard Billing Message
        $claimable = $metrics['claimable_workers'] ?? collect([]);
        $billingLines = [];
        $idx = 1;
        foreach ($claimable as $c) {
            $w = $c['worker'];
            $billingLines[] = "{$idx}. {$w->full_name} ({$w->mitra_id}) | {$c['approved_videos_count']} Video Approved ({$c['approved_hours']} Jam) -> Rp 100.000";
            $idx++;
        }
        $claimListStr = !empty($billingLines) ? implode("\n", $billingLines) : "- Belum ada worker yang siap ditagihkan -";
        $nominalPendingStr = number_format($metrics['pending_commission_amount'], 0, ',', '.');
        $countPendingStr = $metrics['pending_commission_count'];

        $billingMessage = "*PENAGIHAN REWARD REKRUTER - KAMERAKITA.ID*\n"
            . "----------------------------------------\n"
            . "Halo Tim Finance KameraKita, saya ingin mengajukan penagihan reward bonus rekrutmen:\n\n"
            . "👤 *Data Rekruter:*\n"
            . "• Nama: {$partner->full_name}\n"
            . "• ID Rekruter: {$partner->mitra_id}\n"
            . "• No. WhatsApp: " . ($partner->whatsapp_number ?? '-') . "\n\n"
            . "🏦 *Rekening Tujuan Pencairan:*\n"
            . "• Bank: " . ($partner->bank_name ?? 'Belum Diisi') . "\n"
            . "• No. Rekening: " . ($partner->bank_account_number ?? 'Belum Diisi') . "\n"
            . "• Atas Nama: " . ($partner->bank_account_owner ?? 'Belum Diisi') . "\n\n"
            . "💰 *Total Klaim Reward:* Rp {$nominalPendingStr} ({$countPendingStr} Worker)\n\n"
            . "📋 *Rincian Worker Yang Mencapai Target (≥ 20 Jam Approved):*\n"
            . "{$claimListStr}\n\n"
            . "Mohon dicek dan diproses pencairannya ke rekening saya. Terima kasih!";

        $waBillingUrl = 'https://wa.me/' . $adminWa . '?text=' . rawurlencode($billingMessage);
        $hasBankInfo = !empty($partner->bank_name) && !empty($partner->bank_account_number) && !empty($partner->bank_account_owner);
    @endphp

    <div class="space-y-4 sm:space-y-6"
         x-data="{
             showBanner: true,
             searchQuery: '',
             statusFilter: 'all',
             sortBy: 'default',
             copiedBilling: false,
             showCopyModal: false,
             copyFormat: 'email',
             copyData: '',
             copyFeedback: false,
             copyDropdown: false,
             workers: {{ Js::from($workersList) }},
             get filteredWorkers() {
                 let list = this.workers;

                 // Status Filter
                 if (this.statusFilter === 'pending') {
                     list = list.filter(w => w.milestone_reached && w.milestone_status === 'pending');
                 } else if (this.statusFilter === 'paid') {
                     list = list.filter(w => w.milestone_reached && w.milestone_status === 'paid');
                 } else if (this.statusFilter === 'in_progress') {
                     list = list.filter(w => !w.milestone_reached);
                 }

                 // Search Query
                 if (this.searchQuery.trim() !== '') {
                     const q = this.searchQuery.toLowerCase();
                     list = list.filter(w =>
                         (w.full_name && w.full_name.toLowerCase().includes(q)) ||
                         (w.mitra_id && w.mitra_id.toLowerCase().includes(q)) ||
                         (w.email && w.email.toLowerCase().includes(q)) ||
                         (w.whatsapp_number && w.whatsapp_number.toLowerCase().includes(q))
                     );
                 }

                 // Sorting
                 list = [...list];
                 if (this.sortBy === 'approved_desc') {
                     list.sort((a, b) => (b.approved_minutes || 0) - (a.approved_minutes || 0));
                 } else if (this.sortBy === 'videos_desc') {
                     list.sort((a, b) => (b.approved_videos_count || 0) - (a.approved_videos_count || 0));
                 } else if (this.sortBy === 'name_asc') {
                     list.sort((a, b) => (a.full_name || '').localeCompare(b.full_name || ''));
                 }

                 return list;
             },
             openCopyModal(format = 'email') {
                 this.copyFormat = format;
                 this.copyFeedback = false;
                 this.copyDropdown = false;
                 this.renderCopyData();
                 this.showCopyModal = true;
             },
             setCopyFormat(format) {
                 this.copyFormat = format;
                 this.copyFeedback = false;
                 this.renderCopyData();
             },
             renderCopyData() {
                 const currentList = this.filteredWorkers;
                 if (!currentList || currentList.length === 0) {
                     this.copyData = 'Tidak ada data worker pada filter ini.';
                     return;
                 }

                 if (this.copyFormat === 'email') {
                     const emails = currentList
                         .map(w => w.email ? w.email.trim() : '')
                         .filter(e => e && e !== '-' && !e.toLowerCase().includes('belum'));
                     const uniqueEmails = [...new Set(emails)];
                     this.copyData = uniqueEmails.length > 0 ? uniqueEmails.join(', ') : 'Tidak ada email valid.';
                 } else if (this.copyFormat === 'whatsapp') {
                     const phones = currentList
                         .map(w => w.whatsapp_number ? w.whatsapp_number.trim() : '')
                         .filter(p => p && p !== '-');
                     this.copyData = phones.length > 0 ? phones.join('\n') : 'Tidak ada nomor WhatsApp.';
                 } else if (this.copyFormat === 'bank_account') {
                     const accounts = currentList
                         .map(w => (w.bank_name && w.bank_account_number) ? `${w.bank_name} - ${w.bank_account_number} a.n ${w.bank_account_owner}` : '')
                         .filter(a => a && a !== '-');
                     this.copyData = accounts.length > 0 ? accounts.join('\n') : 'Tidak ada nomor rekening.';
                 } else if (this.copyFormat === 'name') {
                     const names = currentList
                         .map(w => w.full_name ? w.full_name.trim() : '')
                         .filter(n => n && n !== '-');
                     this.copyData = names.length > 0 ? names.join('\n') : 'Tidak ada nama.';
                 } else if (this.copyFormat === 'summary') {
                     const summary = currentList.map(w => {
                         const status = w.milestone_status === 'paid' ? 'Lunas (Rp 100k)' : (w.milestone_status === 'pending' ? 'Siap Ditagihkan (Rp 100k)' : 'Dalam Proses');
                         return `${w.full_name} (${w.mitra_id}) | ${w.approved_videos_count} Video Approved (${w.approved_hours_formatted}) | Status: ${status}`;
                     });
                     this.copyData = summary.join('\n');
                 }
             },
             copyToClipboard() {
                 if (!this.copyData || this.copyData.startsWith('Tidak ada')) return;
                 if (navigator.clipboard && window.isSecureContext) {
                     navigator.clipboard.writeText(this.copyData).then(() => {
                         this.copyFeedback = true;
                         setTimeout(() => { this.copyFeedback = false; }, 2000);
                     });
                 } else {
                     const ta = document.createElement('textarea');
                     ta.value = this.copyData;
                     ta.style.position = 'fixed';
                     ta.style.opacity = '0';
                     document.body.appendChild(ta);
                     ta.select();
                     document.execCommand('copy');
                     document.body.removeChild(ta);
                     this.copyFeedback = true;
                     setTimeout(() => { this.copyFeedback = false; }, 2000);
                 }
             }
         }">

        {{-- Top Notification Banner --}}
        <template x-if="showBanner">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-150 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex gap-3 sm:gap-4 items-start pr-8 md:pr-0">
                    <div class="p-2.5 sm:p-3 bg-violet-50 text-violet-600 rounded-xl sm:rounded-2xl shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="block text-xs font-black tracking-widest text-violet-600 uppercase">REKRUTER — PROGRAM KOMISI REWARD</span>
                            @php
                                $rekruterRegDate = $partner->registration_date ?? $partner->created_at ?? Auth::user()->created_at;
                            @endphp
                            <span class="inline-flex items-center gap-1 text-[11px] text-gray-500 font-medium bg-gray-50 px-2 py-0.5 rounded-full border border-gray-200">
                                <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Terdaftar: <strong class="text-gray-700">{{ $rekruterRegDate ? \Carbon\Carbon::parse($rekruterRegDate)->translatedFormat('d M Y') : '-' }}</strong></span>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 max-w-xl">
                            Setiap Worker yang Anda rekrut berhasil mencapai <strong>20 jam video approved</strong>, Anda otomatis berhak mendapatkan bonus reward <strong>Rp 100.000</strong> per worker.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto mt-2 md:mt-0">
                    <span class="bg-emerald-50 text-emerald-800 text-[10px] font-black px-3 py-1.5 rounded-full uppercase border border-emerald-100">Status Aktif</span>
                    {{-- Upload Laporan di-hide sementara sesuai permintaan --}}
                    @if(false)
                    <a href="{{ route('reports.create') }}" class="min-h-9 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl text-xs font-bold text-white shadow-md shadow-blue-500/20 transition-all duration-300">
                        {{ __('dashboard.general.submit_new_report') }}
                    </a>
                    @endif
                    <button type="button" aria-label="{{ __('dashboard.general.close_notification') }}" @click="showBanner = false" class="absolute top-4 right-4 w-9 h-9 inline-flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        {{-- Referral Code Banner --}}
        @if($partner->referral_code)
        <div class="bg-gradient-to-r from-violet-600 via-indigo-600 to-blue-600 rounded-2xl sm:rounded-3xl p-5 sm:p-7 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 shadow-xl text-white">
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="p-3.5 sm:p-4 bg-white/20 rounded-2xl shrink-0 backdrop-blur-sm">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-[10px] font-black tracking-widest text-violet-200 uppercase mb-1">{{ __('dashboard.general.your_referral_code') }}</span>
                    <span class="block text-3xl sm:text-4xl font-black font-mono tracking-widest">{{ $partner->referral_code }}</span>
                    <span class="block text-xs text-violet-100 mt-1">Bagikan link atau kode ini ke calon worker agar terhubung otomatis ke akun Rekruter Anda.</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <input type="hidden" id="refLink" value="{{ route('register', ['ref' => $partner->referral_code]) }}">
                <button
                    type="button"
                    onclick="navigator.clipboard.writeText(document.getElementById('refLink').value).then(() => { this.textContent = '✨ Link Tersalin!'; setTimeout(() => this.textContent = 'Salin Link Pendaftaran', 2000) })"
                    class="px-5 py-3 bg-white text-violet-700 font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-violet-50 transition shadow text-center whitespace-nowrap">
                    Salin Link Pendaftaran
                </button>
                <a href="https://wa.me/?text=Halo%21+Daftarkan+diri+kamu+sebagai+Worker+di+KameraKita.id+menggunakan+link+referral+saya%3A+{{ urlencode(route('register', ['ref' => $partner->referral_code])) }}"
                   target="_blank"
                   class="px-5 py-3 bg-white/20 hover:bg-white/30 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition text-center whitespace-nowrap backdrop-blur-sm inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.174.449.741.964 1.2.662.591 1.221.774 1.394.86.173.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                    </svg>
                    Bagikan via WA
                </a>
            </div>
        </div>
        @endif

        {{-- Summary Stats Overview --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Bonus Diterima (Lunas) --}}
            <div class="bg-gradient-to-br from-emerald-50 to-white rounded-2xl p-5 border border-emerald-200/80 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 uppercase tracking-wider">Lunas</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Total Bonus Diterima</span>
                    <span class="block text-2xl font-black text-emerald-700 mt-1">Rp {{ number_format($metrics['paid_commission_amount'], 0, ',', '.') }}</span>
                    <span class="block text-[11px] text-gray-500 mt-0.5">{{ $metrics['paid_commission_count'] }} milestone telah dicairkan</span>
                </div>
            </div>

            {{-- Bonus Siap Ditagihkan (Pending Claim) --}}
            <div class="bg-gradient-to-br from-amber-50 to-white rounded-2xl p-5 border border-amber-200/80 shadow-sm flex flex-col justify-between gap-3 relative overflow-hidden">
                @if($metrics['pending_commission_amount'] > 0)
                    <span class="absolute top-0 right-0 w-2 h-2 rounded-full bg-amber-500 m-3 animate-ping"></span>
                @endif
                <div class="flex items-center justify-between">
                    <div class="p-2.5 bg-amber-100 text-amber-700 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $metrics['pending_commission_amount'] > 0 ? 'bg-amber-500 text-white animate-pulse' : 'bg-gray-100 text-gray-600' }} uppercase tracking-wider">
                        {{ $metrics['pending_commission_amount'] > 0 ? 'Siap Ditagihkan' : 'Nihil' }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-amber-800 font-semibold uppercase tracking-wider">Bonus Siap Ditagihkan</span>
                    <span class="block text-2xl font-black text-amber-900 mt-1">Rp {{ number_format($metrics['pending_commission_amount'], 0, ',', '.') }}</span>
                    <span class="block text-[11px] text-amber-700 mt-0.5">{{ $metrics['pending_commission_count'] }} worker mencapai target 20 jam</span>
                </div>
            </div>

            {{-- Total Video Approved Tim --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2.5 bg-violet-50 text-violet-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-violet-50 text-violet-700">QC & Atlas</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Total Video Approved</span>
                    <span class="block text-2xl font-black text-gray-900 mt-1">{{ number_format($metrics['total_approved_videos_count']) }} <span class="text-xs font-semibold text-gray-400">Video</span></span>
                    <span class="block text-[11px] text-violet-600 font-medium mt-0.5">Total Durasi: {{ $metrics['total_approved_hours_formatted'] }}</span>
                </div>
            </div>

            {{-- Total Worker Direkrut --}}
            <div class="bg-white rounded-2xl p-5 border border-gray-150 shadow-sm flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">Tim Worker</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Worker Direkrut</span>
                    <span class="block text-2xl font-black text-gray-900 mt-1">{{ $metrics['recruited_workers_count'] }} <span class="text-xs font-semibold text-gray-400">Orang</span></span>
                    <span class="block text-[11px] text-gray-400 font-medium mt-0.5">Terdaftar via kode Anda</span>
                </div>
            </div>
        </div>

        {{-- Pusat Penagihan Reward (Billing Center) --}}
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
            <div class="p-5 sm:p-6 bg-gradient-to-r from-gray-900 to-indigo-950 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="p-3 bg-white/10 rounded-2xl shrink-0 backdrop-blur-sm text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-bold">Pusat Penagihan Reward Rekruter</h2>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 uppercase tracking-widest">Billing Claim</span>
                        </div>
                        <p class="text-xs text-gray-300 mt-0.5">
                            Klaim bonus Rp 100.000 per worker yang telah menembus target minimal 20 jam video approved langsung ke tim Finance KameraKita.
                        </p>
                    </div>
                </div>

                {{-- Action Quick Billing Button --}}
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <button
                        type="button"
                        @click="navigator.clipboard.writeText(`{{ addslashes($billingMessage) }}`).then(() => { copiedBilling = true; setTimeout(() => copiedBilling = false, 2500); })"
                        class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl transition border border-white/10 shadow-sm">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                        <span x-show="!copiedBilling">Salin Format Penagihan</span>
                        <span x-show="copiedBilling" class="text-emerald-300">✨ Format Tersalin!</span>
                    </button>

                    @if($metrics['pending_commission_amount'] > 0)
                        <a href="{{ $waBillingUrl }}" target="_blank" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-emerald-500/30 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.174.449.741.964 1.2.662.591 1.221.774 1.394.86.173.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                            </svg>
                            Kirim Tagihan ke WA Admin
                        </a>
                    @endif
                </div>
            </div>

            <div class="p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                {{-- Left: Rekening Tujuan Pencairan --}}
                <div class="lg:col-span-5 bg-gray-50/80 rounded-2xl p-4 sm:p-5 border border-gray-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Rekening Pencairan Anda
                        </span>
                        <a href="{{ route('profile.edit') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                            {{ $hasBankInfo ? 'Ubah' : '+ Lengkapi' }}
                        </a>
                    </div>

                    @if($hasBankInfo)
                        <div class="bg-white p-3.5 rounded-xl border border-gray-150 space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-400">Nama Bank:</span>
                                <span class="font-bold text-gray-800 uppercase">{{ $partner->bank_name }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-400">Nomor Rekening:</span>
                                <span class="font-mono font-bold text-gray-900 tracking-wider">{{ $partner->bank_account_number }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-400">Atas Nama:</span>
                                <span class="font-semibold text-gray-700">{{ $partner->bank_account_owner }}</span>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-bold text-amber-800">Data rekening belum lengkap</p>
                                    <p class="text-[11px] text-amber-700 mt-0.5">Silakan isi nomor rekening bank Anda di profil agar komisi dapat ditransfer tim finance saat klaim diajukan.</p>
                                    <a href="{{ route('profile.edit') }}" class="inline-block mt-2 text-xs font-bold text-amber-900 underline">Perbarui Data Rekening Sekarang &rarr;</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="text-[11px] text-gray-500 leading-relaxed">
                        Dana reward komisi rekrutmen akan langsung dicairkan ke rekening di atas setelah Anda mengajukan penagihan ke Tim Finance.
                    </div>
                </div>

                {{-- Right: Status Klaim & Rincian Worker Siap Diklaim --}}
                <div class="lg:col-span-7 space-y-4">
                    @if($metrics['pending_commission_amount'] > 0)
                        <div class="p-4 sm:p-5 bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-amber-200/60">
                                <div>
                                    <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Klaim Reward Siap Ditagihkan</span>
                                    <div class="text-2xl font-black text-amber-950 mt-0.5">
                                        Rp {{ number_format($metrics['pending_commission_amount'], 0, ',', '.') }}
                                        <span class="text-xs font-semibold text-amber-700 font-normal">({{ $metrics['pending_commission_count'] }} Worker Tercapai)</span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500 text-white self-start sm:self-auto shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    Siap Cair
                                </span>
                            </div>

                            {{-- List of claimable workers --}}
                            <div class="space-y-2">
                                <span class="text-[11px] font-bold text-amber-900 uppercase tracking-wider block">Worker yang Memenuhi Syarat:</span>
                                <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1">
                                    @foreach($claimable as $item)
                                        <div class="bg-white p-2.5 rounded-xl border border-amber-200/80 flex items-center justify-between text-xs">
                                            <div>
                                                <span class="font-bold text-gray-800">{{ $item['worker']->full_name }}</span>
                                                <span class="text-[10px] font-mono font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded ml-1">{{ $item['worker']->mitra_id }}</span>
                                                <div class="text-[11px] text-gray-500 mt-0.5">
                                                    {{ $item['approved_videos_count'] }} Video Approved • <span class="font-bold text-emerald-600">{{ $item['approved_hours_formatted'] }}</span>
                                                </div>
                                            </div>
                                            <span class="font-bold text-amber-900 bg-amber-100 px-2 py-1 rounded-lg shrink-0">
                                                Rp 100.000
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- CTA Buttons --}}
                            <div class="pt-2 flex flex-col sm:flex-row gap-2">
                                <a href="{{ $waBillingUrl }}" target="_blank" class="flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow text-center inline-flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.174.449.741.964 1.2.662.591 1.221.774 1.394.86.173.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                                    </svg>
                                    Ajukan Penagihan via WhatsApp
                                </a>
                                <button
                                    type="button"
                                    @click="navigator.clipboard.writeText(`{{ addslashes($billingMessage) }}`).then(() => { copiedBilling = true; setTimeout(() => copiedBilling = false, 2500); })"
                                    class="py-3 px-4 bg-white text-gray-700 hover:bg-gray-100 font-bold text-xs uppercase tracking-wider rounded-xl transition border border-gray-200 text-center">
                                    <span x-show="!copiedBilling">Salin Teks Penagihan</span>
                                    <span x-show="copiedBilling" class="text-emerald-600 font-bold">✓ Tersalin!</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="p-5 bg-gray-50 border border-gray-200 rounded-2xl flex flex-col items-center justify-center text-center space-y-2">
                            <div class="p-3 bg-white rounded-full text-gray-400 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="max-w-md">
                                <span class="block text-sm font-bold text-gray-800">Belum Ada Bonus yang Siap Ditagihkan</span>
                                <p class="text-xs text-gray-500 mt-1">
                                    Saat worker rekrutan Anda mencapai 20 jam video approved, klaim bonus Rp 100.000 per worker akan otomatis aktif di bagian ini dan siap diajukan ke finance.
                                </p>
                            </div>
                            @if($metrics['paid_commission_amount'] > 0)
                                <div class="mt-2 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                                    ✓ Anda telah mencairkan total Rp {{ number_format($metrics['paid_commission_amount'], 0, ',', '.') }} bonus rekrutmen.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cara Kerja Program Komisi --}}
        <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-gray-150 shadow-sm">
            <span class="block text-xs font-black tracking-widest text-gray-400 uppercase mb-4">Cara Kerja Program Komisi Rekruter</span>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex gap-3 items-start p-4 bg-violet-50/70 rounded-2xl border border-violet-100/60">
                    <span class="w-8 h-8 rounded-full bg-violet-600 text-white font-black text-sm flex items-center justify-center shrink-0">1</span>
                    <div>
                        <span class="block text-sm font-bold text-gray-800">Bagikan Link / Kode Referral</span>
                        <span class="block text-xs text-gray-500 mt-1">Calon worker mendaftar menggunakan kode <strong class="font-mono text-violet-700">{{ $partner->referral_code }}</strong>.</span>
                    </div>
                </div>
                <div class="flex gap-3 items-start p-4 bg-blue-50/70 rounded-2xl border border-blue-100/60">
                    <span class="w-8 h-8 rounded-full bg-blue-600 text-white font-black text-sm flex items-center justify-center shrink-0">2</span>
                    <div>
                        <span class="block text-sm font-bold text-gray-800">Worker Merekam & Lolos QC</span>
                        <span class="block text-xs text-gray-500 mt-1">Setiap video yang disetujui (QC / Atlas) dihitung otomatis secara transparan.</span>
                    </div>
                </div>
                <div class="flex gap-3 items-start p-4 bg-emerald-50/70 rounded-2xl border border-emerald-100/60">
                    <span class="w-8 h-8 rounded-full bg-emerald-600 text-white font-black text-sm flex items-center justify-center shrink-0">3</span>
                    <div>
                        <span class="block text-sm font-bold text-gray-800">20 Jam → Klaim Rp 100.000</span>
                        <span class="block text-xs text-gray-500 mt-1">Saat mencapai 20 jam approved, Anda langsung dapat menagih reward Rp 100.000 ke kami.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Tracking Worker Rekrutan & Video Approved --}}
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-150 shadow-sm space-y-4">
            {{-- Table Header & Actions --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Tracking Worker Rekrutan & Video Approved</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pantau jumlah video approved, total jam, dan progres milestone setiap worker yang Anda daftarkan.</p>
                </div>

                {{-- Action: Salin Data Dropdown --}}
                <div class="relative w-full md:w-auto" @click.outside="copyDropdown = false">
                    <button
                        type="button"
                        @click="copyDropdown = !copyDropdown"
                        class="w-full md:w-auto inline-flex items-center justify-between gap-2 px-3.5 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold rounded-xl border border-gray-200 transition">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span>Salin Data Worker</span>
                        </span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="copyDropdown"
                        x-transition
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-30">
                        <button type="button" @click="openCopyModal('email')" class="w-full text-left px-3.5 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <span>📧</span> Salin Email (Comma-separated)
                        </button>
                        <button type="button" @click="openCopyModal('whatsapp')" class="w-full text-left px-3.5 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <span>💬</span> Salin No WhatsApp (Berurutan)
                        </button>
                        <button type="button" @click="openCopyModal('bank_account')" class="w-full text-left px-3.5 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <span>🏦</span> Salin No Rekening (Berurutan)
                        </button>
                        <button type="button" @click="openCopyModal('name')" class="w-full text-left px-3.5 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <span>👤</span> Salin Nama Saja (Berurutan)
                        </button>
                        <div class="border-t border-gray-100 my-1"></div>
                        <button type="button" @click="openCopyModal('summary')" class="w-full text-left px-3.5 py-2 text-xs text-violet-700 font-bold hover:bg-violet-50 flex items-center gap-2">
                            <span>📋</span> Salin Rekap Lengkap (Jam & Video)
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search & Filter Controls --}}
            <div class="flex flex-col md:flex-row gap-3 justify-between items-stretch md:items-center">
                {{-- Status Tabs --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                    <button
                        type="button"
                        @click="statusFilter = 'all'"
                        :class="statusFilter === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 rounded-lg text-xs transition whitespace-nowrap">
                        Semua Worker (<span x-text="workers.length"></span>)
                    </button>
                    <button
                        type="button"
                        @click="statusFilter = 'pending'"
                        :class="statusFilter === 'pending' ? 'bg-amber-600 text-white font-bold' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200'"
                        class="px-3 py-1.5 rounded-lg text-xs transition whitespace-nowrap flex items-center gap-1">
                        <span>⚡ Siap Ditagihkan</span>
                        (<span x-text="workers.filter(w => w.milestone_reached && w.milestone_status === 'pending').length"></span>)
                    </button>
                    <button
                        type="button"
                        @click="statusFilter = 'paid'"
                        :class="statusFilter === 'paid' ? 'bg-emerald-600 text-white font-bold' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200'"
                        class="px-3 py-1.5 rounded-lg text-xs transition whitespace-nowrap flex items-center gap-1">
                        <span>✓ Lunas</span>
                        (<span x-text="workers.filter(w => w.milestone_reached && w.milestone_status === 'paid').length"></span>)
                    </button>
                    <button
                        type="button"
                        @click="statusFilter = 'in_progress'"
                        :class="statusFilter === 'in_progress' ? 'bg-blue-600 text-white font-bold' : 'bg-blue-50 text-blue-800 hover:bg-blue-100 border border-blue-200'"
                        class="px-3 py-1.5 rounded-lg text-xs transition whitespace-nowrap">
                        Sedang Berjalan (<span x-text="workers.filter(w => !w.milestone_reached).length"></span>)
                    </button>
                </div>

                {{-- Search & Sort Box --}}
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Cari nama, ID, email, WA..."
                            class="w-full pl-9 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    </div>

                    <select
                        x-model="sortBy"
                        class="text-xs bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 text-gray-700">
                        <option value="default">Urutkan: Default</option>
                        <option value="approved_desc">Jam Approved Terbanyak</option>
                        <option value="videos_desc">Video Approved Terbanyak</option>
                        <option value="name_asc">Nama (A-Z)</option>
                    </select>
                </div>
            </div>

            {{-- Empty State: No Workers Registered --}}
            @if(empty($metrics['workers_data']))
                <div class="py-12 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 text-gray-300 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-700">Belum ada Worker yang terdaftar</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                        Bagikan kode referral <strong class="font-mono text-violet-600">{{ $partner->referral_code }}</strong> kepada calon worker. Saat mereka mulai bekerja dan mencapai 20 jam approved, komisi otomatis dicatat di sini.
                    </p>
                </div>
            @else
                {{-- Workers Table --}}
                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50/75">
                            <tr class="text-gray-500 text-[11px] uppercase tracking-wider">
                                <th class="py-3 px-4 text-left font-bold">Worker & Kontak</th>
                                <th class="py-3 px-3 text-left font-bold">ID Worker</th>
                                <th class="py-3 px-3 text-center font-bold">Video Approved</th>
                                <th class="py-3 px-3 text-center font-bold">Video Disubmit</th>
                                <th class="py-3 px-3 text-center font-bold">Progres Target (20 Jam)</th>
                                <th class="py-3 px-4 text-center font-bold">Status Reward</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            <template x-for="item in filteredWorkers" :key="item.id">
                                <tr class="hover:bg-gray-50/60 transition">
                                    {{-- Worker & Contact --}}
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-gray-900" x-text="item.full_name"></div>
                                        <div class="text-xs text-gray-400" x-text="item.email"></div>
                                        <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>Daftar: <span class="font-medium text-gray-600" x-text="item.registered_at"></span></span>
                                        </div>
                                        <template x-if="item.whatsapp_clean">
                                            <a :href="'https://wa.me/' + item.whatsapp_clean" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 hover:text-emerald-800 font-medium mt-0.5">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.353.101.174.449.741.964 1.2.662.591 1.221.774 1.394.86.173.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/></svg>
                                                <span x-text="item.whatsapp_number"></span>
                                            </a>
                                        </template>
                                    </td>

                                    {{-- ID Worker --}}
                                    <td class="py-3.5 px-3">
                                        <span class="inline-block px-2 py-0.5 font-mono text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 rounded-md" x-text="item.mitra_id"></span>
                                    </td>

                                    {{-- Video Approved (Count + Hours) --}}
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="font-bold text-sm text-gray-900">
                                            <span x-text="item.approved_videos_count"></span> <span class="text-xs font-normal text-gray-500">Video</span>
                                        </div>
                                        <div class="text-xs font-bold text-emerald-600 mt-0.5" x-text="item.approved_hours_formatted"></div>
                                    </td>

                                    {{-- Video Disubmit (Count + Hours) --}}
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="font-semibold text-sm text-gray-700">
                                            <span x-text="item.reported_videos_count"></span> <span class="text-xs font-normal text-gray-400">Video</span>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5" x-text="item.reported_hours_formatted"></div>
                                    </td>

                                    {{-- Progress Bar (20 Hours) --}}
                                    <td class="py-3.5 px-3 text-center min-w-[140px]">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                                <div
                                                    class="h-full rounded-full transition-all duration-500"
                                                    :class="item.milestone_reached ? 'bg-emerald-500' : (item.progress_percent >= 75 ? 'bg-amber-500' : 'bg-indigo-500')"
                                                    :style="'width: ' + item.progress_percent + '%'"></div>
                                            </div>
                                            <span class="text-xs font-bold text-gray-700 w-9 text-right" x-text="item.progress_percent + '%'"></span>
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-1">
                                            <template x-if="item.milestone_reached">
                                                <span class="text-emerald-600 font-semibold">✓ Target 20 Jam Tercapai</span>
                                            </template>
                                            <template x-if="!item.milestone_reached">
                                                <span>Sisa <strong x-text="item.remaining_hours"></strong> jam lagi</span>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Status Reward / Komisi --}}
                                    <td class="py-3.5 px-4 text-center">
                                        <template x-if="item.milestone_reached && item.milestone_status === 'paid'">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                                <span>✓</span> Komisi Lunas (Rp 100k)
                                            </span>
                                        </template>
                                        <template x-if="item.milestone_reached && item.milestone_status === 'pending'">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-sm animate-pulse">
                                                <span>⚡</span> Siap Ditagihkan (Rp 100k)
                                            </span>
                                        </template>
                                        <template x-if="!item.milestone_reached">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                                Dalam Proses
                                            </span>
                                        </template>
                                    </td>
                                </tr>
                            </template>

                            {{-- No Search Match --}}
                            <tr x-show="filteredWorkers.length === 0">
                                <td colspan="6" class="py-8 text-center text-gray-400 text-xs">
                                    Tidak ada worker yang sesuai dengan filter atau pencarian Anda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Table Footer Summary --}}
                <div class="flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 pt-2 px-1">
                    <div>
                        Menampilkan <span class="font-bold text-gray-800" x-text="filteredWorkers.length"></span> dari <span class="font-bold text-gray-800" x-text="workers.length"></span> worker
                    </div>
                    <div class="mt-2 sm:mt-0 text-[11px] text-gray-400">
                        * Milestones 20 jam dihitung dari akumulasi jam video disetujui (Approved QC & Atlas).
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal Salin Data Worker --}}
        <div
            x-show="showCopyModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div
                    x-show="showCopyModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                    @click="showCopyModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    x-show="showCopyModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    
                    <div class="bg-gray-50 px-5 py-4 border-b border-gray-150 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span>📋</span> Salin Data Worker
                        </h3>
                        <button type="button" @click="showCopyModal = false" class="text-gray-400 hover:text-gray-600 rounded-lg p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- Tabs Format --}}
                        <div class="flex flex-wrap gap-1.5 p-1 bg-gray-100 rounded-xl text-xs">
                            <button type="button" @click="setCopyFormat('email')" :class="copyFormat === 'email' ? 'bg-white font-bold text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-2.5 py-1.5 rounded-lg transition">Email</button>
                            <button type="button" @click="setCopyFormat('whatsapp')" :class="copyFormat === 'whatsapp' ? 'bg-white font-bold text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-2.5 py-1.5 rounded-lg transition">No WA</button>
                            <button type="button" @click="setCopyFormat('bank_account')" :class="copyFormat === 'bank_account' ? 'bg-white font-bold text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-2.5 py-1.5 rounded-lg transition">No Rek</button>
                            <button type="button" @click="setCopyFormat('name')" :class="copyFormat === 'name' ? 'bg-white font-bold text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-2.5 py-1.5 rounded-lg transition">Nama</button>
                            <button type="button" @click="setCopyFormat('summary')" :class="copyFormat === 'summary' ? 'bg-white font-bold text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-2.5 py-1.5 rounded-lg transition">Rekap Video</button>
                        </div>

                        {{-- Textarea Preview --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Data yang akan disalin:</label>
                            <textarea
                                x-model="copyData"
                                readonly
                                rows="8"
                                class="w-full text-xs font-mono bg-gray-50 border border-gray-200 rounded-xl p-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-gray-800 leading-relaxed"></textarea>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-5 py-3 border-t border-gray-150 flex justify-end gap-2">
                        <button type="button" @click="showCopyModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                            Tutup
                        </button>
                        <button
                            type="button"
                            @click="copyToClipboard()"
                            class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-md shadow-indigo-500/20 flex items-center gap-1.5">
                            <span x-show="!copyFeedback">Salin ke Clipboard</span>
                            <span x-show="copyFeedback" class="text-emerald-200 font-bold">✓ Tersalin!</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
