<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold uppercase tracking-wider mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Modul Admin
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Pengajuan Akun Induk (Main Account)
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Review dan setujui permohonan worker yang ingin mengelola banyak akun email dalam satu dashboard.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('partners.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-50 transition shadow-sm">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Data Mitra & Pekerja
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .kk-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 60;
            background-color: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .kk-modal-card {
            background: #ffffff;
            border-radius: 24px;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 25px 60px -12px rgba(15, 23, 42, 0.35);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .kk-modal-btn {
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            transition: all 150ms ease;
        }
        .kk-modal-btn--cancel {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .kk-modal-btn--cancel:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .kk-modal-btn--approve {
            background: #059669;
            color: #ffffff;
            border: 1px solid #059669;
            box-shadow: 0 8px 18px -4px rgba(5, 150, 105, 0.4);
        }
        .kk-modal-btn--approve:hover:not(:disabled) {
            background: #047857;
        }
        .kk-modal-btn--reject {
            background: #e11d48;
            color: #ffffff;
            border: 1px solid #e11d48;
            box-shadow: 0 8px 18px -4px rgba(225, 29, 72, 0.4);
        }
        .kk-modal-btn--reject:hover:not(:disabled) {
            background: #be123c;
        }
        .kk-modal-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
    </style>

    <div class="py-6 sm:py-8 pb-24 md:pb-8"
         x-data="{
            approveOpen: false,
            rejectOpen: false,
            submitting: false,
            target: {
                id: null,
                name: '',
                email: '',
                phone: '',
                bank: '',
                hasBank: false,
                approveUrl: '',
                rejectUrl: ''
            },
            approveNotes: '',
            sendWaNow: false,
            rejectNotes: '',
            openApprove(payload) {
                this.target = payload;
                this.approveNotes = '';
                this.sendWaNow = false;
                this.submitting = false;
                this.approveOpen = true;
            },
            openReject(payload) {
                this.target = payload;
                this.rejectNotes = '';
                this.submitting = false;
                this.rejectOpen = true;
            },
            closeAll() {
                if (this.submitting) return;
                this.approveOpen = false;
                this.rejectOpen = false;
            }
         }"
         @keydown.escape.window="closeAll()">

        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            <!-- Active Approval Success Banner with WhatsApp Action -->
            @if(session('manual_wa_url'))
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border-2 border-emerald-400 p-5 sm:p-6 rounded-2xl shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold uppercase tracking-wider">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Pengajuan Berhasil Disetujui
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mt-1">Akun Resmi Menjadi Main Account (Akun Induk)</h3>
                        <p class="text-xs text-gray-600">
                            Untuk Worker: <strong>{{ session('target_name') }}</strong> ({{ session('target_email') }})
                            @if(session('target_phone'))
                                • WhatsApp: <strong class="text-emerald-700">{{ session('target_phone') }}</strong>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ session('manual_wa_url') }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm"
                           style="background-color: #059669;">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span>Kirim Ucapan Selamat via WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Standard Session Alerts -->
            @if(session('success') && !session('manual_wa_url'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-500 mr-3 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-500 mr-3 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <p class="text-sm text-blue-800 font-medium">{{ session('info') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-3 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            <!-- Navigation Tabs & Search Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <!-- Status Filter Tabs -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <a href="{{ route('admin.main-account-requests.index', ['status' => 'pending', 'search' => $search]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $status === 'pending' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span>Menunggu Validasi</span>
                        @if($counts['pending'] > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'pending' ? 'bg-white text-indigo-700 font-black' : 'bg-amber-100 text-amber-800 font-black' }}">
                                {{ $counts['pending'] }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('admin.main-account-requests.index', ['status' => 'approved', 'search' => $search]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $status === 'approved' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span>Disetujui</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'approved' ? 'bg-white text-indigo-700' : 'bg-gray-200 text-gray-700' }}">
                            {{ $counts['approved'] }}
                        </span>
                    </a>

                    <a href="{{ route('admin.main-account-requests.index', ['status' => 'rejected', 'search' => $search]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $status === 'rejected' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span>Ditolak</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'rejected' ? 'bg-white text-indigo-700' : 'bg-gray-200 text-gray-700' }}">
                            {{ $counts['rejected'] }}
                        </span>
                    </a>

                    <a href="{{ route('admin.main-account-requests.index', ['status' => '', 'search' => $search]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ empty($status) ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span>Semua ({{ $counts['all'] }})</span>
                    </a>
                </div>

                <!-- Search Input -->
                <form method="GET" action="{{ route('admin.main-account-requests.index') }}" class="w-full md:w-72">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Cari email, nama, nomor WA..."
                               class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </form>
            </div>

            <!-- Mobile Cards (< 1024px) -->
            <div class="lg:hidden space-y-3">
                @forelse($requests as $req)
                    @php
                        $partner = $req->partner;
                        $phone = $req->phone_number ?: $partner?->whatsapp_number;
                        $formattedPhone = $phone ? \App\Helpers\PhoneHelper::formatForGateway($phone) : null;
                        $bankName = $req->bank_name ?: $partner?->bank_name;
                        $bankNum = $req->bank_account_number ?: $partner?->bank_account_number;
                        $bankOwner = $req->bank_account_owner ?: $partner?->bank_account_owner;
                        $bankSummary = $bankName ? trim("{$bankName} - {$bankNum} (a/n {$bankOwner})") : 'Rekening belum terisi';
                        $workerName = $req->user->name ?? $partner?->full_name ?? 'Tanpa Nama';
                        $payloadJson = [
                            'id' => $req->id,
                            'name' => $workerName,
                            'email' => $req->email,
                            'phone' => $phone ?: '-',
                            'bank' => $bankSummary,
                            'hasBank' => (bool) ($bankName && $bankNum),
                            'approveUrl' => route('admin.main-account-requests.approve', $req),
                            'rejectUrl' => route('admin.main-account-requests.reject', $req),
                        ];
                    @endphp
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-gray-900 text-sm truncate">{{ $workerName }}</div>
                                <div class="font-mono text-xs text-indigo-600 font-semibold truncate">{{ $req->email }}</div>
                                <div class="text-[11px] text-gray-400 mt-0.5">{{ $req->created_at->format('d M Y, H:i') }}</div>
                            </div>
                            <div class="shrink-0">
                                @if($req->isPending())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                                @elseif($req->isApproved())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Disetujui</span>
                                @elseif($req->isRejected())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                @endif
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-3 space-y-1 text-xs text-gray-600">
                            <div><span class="font-semibold text-gray-700">WhatsApp:</span> {{ $phone ?: '-' }}</div>
                            <div><span class="font-semibold text-gray-700">Rekening:</span> {{ $bankSummary }}</div>
                            @if($req->notes)
                                <div class="pt-1 text-gray-500 italic">"{{ $req->notes }}"</div>
                            @endif
                        </div>

                        @if($req->isPending())
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <button type="button" @click='openApprove(@json($payloadJson))' class="kk-modal-btn kk-modal-btn--approve w-full">
                                    Setujui
                                </button>
                                <button type="button" @click='openReject(@json($payloadJson))' class="kk-modal-btn kk-modal-btn--cancel w-full text-rose-700">
                                    Tolak
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-xs text-gray-500">
                        Tidak ada pengajuan Akun Induk yang sesuai dengan filter ini.
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table Card (>= 1024px) -->
            <div class="hidden lg:block bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50/80 rounded-lg">
                            <tr>
                                <th scope="col" class="px-5 py-4 font-semibold rounded-l-lg">Worker &amp; Akun</th>
                                <th scope="col" class="px-5 py-4 font-semibold">Kontak &amp; Rekening Bank</th>
                                <th scope="col" class="px-5 py-4 font-semibold">Catatan Pengajuan</th>
                                <th scope="col" class="px-5 py-4 font-semibold">Waktu Pengajuan</th>
                                <th scope="col" class="px-5 py-4 font-semibold">Status</th>
                                <th scope="col" class="px-5 py-4 font-semibold rounded-r-lg text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($requests as $req)
                                @php
                                    $partner = $req->partner;
                                    $phone = $req->phone_number ?: $partner?->whatsapp_number;
                                    $formattedPhone = $phone ? \App\Helpers\PhoneHelper::formatForGateway($phone) : null;
                                    $bankName = $req->bank_name ?: $partner?->bank_name;
                                    $bankNum = $req->bank_account_number ?: $partner?->bank_account_number;
                                    $bankOwner = $req->bank_account_owner ?: $partner?->bank_account_owner;
                                    $bankSummary = $bankName ? trim("{$bankName} - {$bankNum} (a/n {$bankOwner})") : 'Rekening belum terisi';
                                    $workerName = $req->user->name ?? $partner?->full_name ?? 'Tanpa Nama';
                                    $payloadJson = [
                                        'id' => $req->id,
                                        'name' => $workerName,
                                        'email' => $req->email,
                                        'phone' => $phone ?: '-',
                                        'bank' => $bankSummary,
                                        'hasBank' => (bool) ($bankName && $bankNum),
                                        'approveUrl' => route('admin.main-account-requests.approve', $req),
                                        'rejectUrl' => route('admin.main-account-requests.reject', $req),
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                                    <!-- Worker & Akun -->
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-900">{{ $workerName }}</div>
                                        <div class="font-mono text-xs text-indigo-600 font-semibold mt-0.5">{{ $req->email }}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            ID: {{ $partner?->mitra_id ?? '-' }} • Role Saat Ini: 
                                            <span class="inline-block px-1.5 py-0.2 bg-gray-100 text-gray-700 rounded font-medium">{{ $partner?->roleLabel() ?? 'Worker' }}</span>
                                        </div>
                                    </td>

                                    <!-- Kontak & Bank -->
                                    <td class="px-5 py-4">
                                        @if($phone)
                                            <a href="https://wa.me/{{ $formattedPhone }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-emerald-700 font-bold hover:underline">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                {{ $phone }}
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No WhatsApp kosong</span>
                                        @endif

                                        <div class="mt-1 text-xs text-gray-600">
                                            @if($bankName)
                                                <span class="font-bold text-gray-800">{{ $bankName }}:</span> 
                                                <span class="font-mono text-gray-700">{{ $bankNum }}</span>
                                                <div class="text-[11px] text-gray-400">a/n {{ $bankOwner }}</div>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-xs text-amber-600 font-medium">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    Rekening belum terisi
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Catatan -->
                                    <td class="px-5 py-4">
                                        @if($req->estimated_accounts)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700">
                                                {{ $req->estimated_accounts }}
                                            </span>
                                        @endif
                                        @if($req->notes)
                                            <div class="text-xs text-gray-600 mt-1 max-w-xs line-clamp-2" title="{{ $req->notes }}">
                                                "{{ $req->notes }}"
                                            </div>
                                        @endif
                                        @if(!$req->estimated_accounts && !$req->notes)
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    <!-- Waktu Pengajuan -->
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500">
                                        <div>{{ $req->created_at->format('d M Y') }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $req->created_at->format('H:i') }} ({{ $req->created_at->diffForHumans() }})</div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if($req->isPending())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Menunggu Validasi
                                            </span>
                                        @elseif($req->isApproved())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Disetujui
                                            </span>
                                            @if($req->approvedBy)
                                                <div class="text-[10px] text-gray-400 mt-0.5">Oleh: {{ $req->approvedBy->name }}</div>
                                            @endif
                                        @elseif($req->isRejected())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Ditolak
                                            </span>
                                            @if($req->admin_notes)
                                                <div class="text-[10px] text-rose-600 mt-0.5 max-w-xs truncate" title="{{ $req->admin_notes }}">
                                                    {{ $req->admin_notes }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        @if($req->isPending())
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Tombol Setujui (Opens Custom Modal) -->
                                                <button type="button"
                                                        @click='openApprove(@json($payloadJson))'
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Setujui</span>
                                                </button>

                                                <!-- Tombol Tolak (Opens Custom Modal) -->
                                                <button type="button"
                                                        @click='openReject(@json($payloadJson))'
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-200 hover:bg-rose-50 hover:border-rose-200 text-gray-700 hover:text-rose-700 rounded-lg text-xs font-bold transition shadow-sm cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    <span>Tolak</span>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 font-medium">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="max-w-xs mx-auto space-y-2">
                                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto text-gray-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                            </div>
                                            <p class="text-sm font-bold text-gray-800">Tidak ada pengajuan ditemukan</p>
                                            <p class="text-xs text-gray-500">Belum ada pengajuan Akun Induk yang sesuai dengan filter ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($requests->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $requests->links() }}
                </div>
                @endif
            </div>

        </div>

        <!-- Approve Confirmation Modal (Custom UI/UX) -->
        <div x-show="approveOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="closeAll()"
             class="kk-modal-backdrop"
             style="display: none;"
             role="dialog"
             aria-modal="true">
            <div x-show="approveOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="kk-modal-card">

                <!-- Modal Header -->
                <div class="p-5 sm:p-6 border-b border-slate-100 flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 leading-tight">Setujui Pengajuan Akun Induk?</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Role akun akan langsung diubah menjadi <strong>Main Account</strong>.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body & Form -->
                <form :action="target.approveUrl" method="POST" @submit="submitting = true" class="p-5 sm:p-6 space-y-4">
                    @csrf

                    <!-- Worker Summary Card -->
                    <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-400 font-semibold">Nama Worker</span>
                            <span class="font-bold text-slate-900 truncate" x-text="target.name"></span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-400 font-semibold">Email</span>
                            <span class="font-mono font-semibold text-indigo-600 truncate" x-text="target.email"></span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-400 font-semibold">WhatsApp</span>
                            <span class="font-semibold text-slate-700" x-text="target.phone"></span>
                        </div>
                        <div class="pt-1.5 border-t border-slate-200/70 flex items-start justify-between gap-2">
                            <span class="text-slate-400 font-semibold shrink-0">Rekening</span>
                            <span class="font-semibold text-right" :class="target.hasBank ? 'text-emerald-700' : 'text-amber-600'" x-text="target.bank"></span>
                        </div>
                    </div>

                    <!-- Optional Admin Note -->
                    <div>
                        <label for="approve_admin_notes" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Catatan Admin <span class="font-normal normal-case text-slate-400">(Opsional)</span>
                        </label>
                        <input type="text" id="approve_admin_notes" name="admin_notes" x-model="approveNotes"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Contoh: Data tervalidasi lengkap.">
                    </div>

                    <!-- Auto WA Toggle -->
                    <label class="flex items-start gap-2.5 p-3 rounded-xl bg-emerald-50/70 border border-emerald-100 cursor-pointer select-none">
                        <input type="checkbox" name="send_wa_now" value="1" x-model="sendWaNow" class="mt-0.5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-emerald-900 leading-snug">
                            <strong>Kirim notifikasi WhatsApp otomatis</strong> melalui gateway begitu akun disetujui.
                        </span>
                    </label>

                    <!-- Modal Footer Buttons -->
                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="closeAll()" :disabled="submitting" class="kk-modal-btn kk-modal-btn--cancel">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting" class="kk-modal-btn kk-modal-btn--approve">
                            <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="submitting ? 'Memproses...' : 'Ya, Setujui &amp; Aktifkan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reject Confirmation Modal (Custom UI/UX) -->
        <div x-show="rejectOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="closeAll()"
             class="kk-modal-backdrop"
             style="display: none;"
             role="dialog"
             aria-modal="true">
            <div x-show="rejectOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="kk-modal-card">

                <!-- Modal Header -->
                <div class="p-5 sm:p-6 border-b border-slate-100 flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 leading-tight">Tolak Pengajuan</h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate max-w-[260px]" x-text="target.name + ' (' + target.email + ')'"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Body & Form -->
                <form :action="target.rejectUrl" method="POST" @submit="submitting = true" class="p-5 sm:p-6 space-y-4">
                    @csrf

                    <!-- Quick Reason Chips -->
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pilihan Alasan Cepat:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="rejectNotes = 'Rekening bank belum dilengkapi.'" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                + Rekening belum lengkap
                            </button>
                            <button type="button" @click="rejectNotes = 'Nomor WhatsApp tidak aktif / belum terisi.'" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                + No WA belum valid
                            </button>
                            <button type="button" @click="rejectNotes = 'Akun belum memenuhi kriteria verifikasi.'" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold transition cursor-pointer">
                                + Belum memenuhi syarat
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="reject_admin_notes" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="reject_admin_notes" name="admin_notes" x-model="rejectNotes" rows="3" required
                                  class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                  placeholder="Tuliskan alasan penolakan..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="closeAll()" :disabled="submitting" class="kk-modal-btn kk-modal-btn--cancel">
                            Batal
                        </button>
                        <button type="submit" :disabled="submitting" class="kk-modal-btn kk-modal-btn--reject">
                            <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Tolak'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
