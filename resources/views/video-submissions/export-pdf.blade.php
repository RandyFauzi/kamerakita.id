<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Laporan Kerja - {{ $periodLabel ?: ($startDate . ' - ' . $endDate) }}</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
                padding: 1cm !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-break {
                page-break-after: always !important;
                break-after: page !important;
            }
            .no-print-last:last-child {
                display: none !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
        @page {
            size: A4;
            margin: 0;
        }
        .page-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans p-6 antialiased">

    <!-- Floating navigation bar (Hidden in Print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white border border-gray-200 rounded-3xl p-4 flex justify-between items-center shadow-sm">
        <div class="space-y-0.5">
            <h4 class="font-bold text-gray-800 text-sm">Mode Pratinjau Cetak A4</h4>
            <p class="text-xs text-gray-400">Tekan tombol cetak untuk menyimpan laporan sebagai file PDF.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('video-submissions.qc-room') }}" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-50 transition">
                Kembali
            </a>
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Printable container -->
    <div class="max-w-5xl mx-auto">
        
        @forelse($partners as $partnerIndex => $partner)
            <!-- Recap Card per Partner -->
            <div class="page-card bg-white border border-gray-200 rounded-3xl p-6 shadow-sm mb-6">
                <!-- Recap Header -->
                <div class="border-b border-gray-150 pb-4 mb-4 flex justify-between items-start">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/Logo.webp') }}" style="width: 45px; height: 45px; object-fit: contain; flex-shrink: 0;" alt="Logo">
                        <div>
                            <h2 class="text-base font-black tracking-tight text-gray-900">KAMERAKITA <span class="text-indigo-600">AI</span></h2>
                            <p class="text-[9px] font-bold uppercase tracking-wider text-indigo-600 font-mono">Laporan Rekapitulasi Video QC - {{ $periodLabel ?: 'Periode Khusus' }}</p>
                        </div>
                    </div>
                    <div class="text-right text-[10px] text-gray-500 font-mono space-y-0.5">
                        <div>Tanggal Cetak: <span class="text-gray-800 font-bold">{{ date('d F Y H:i') }}</span></div>
                        <div>Rentang: <span class="text-gray-800 font-bold">{{ $startDate }} - {{ $endDate }}</span></div>
                    </div>
                </div>

                <!-- Partner Profile Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-2xl mb-5 text-xs font-semibold text-slate-700">
                    <div>
                        <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Mitra (Worker)</span>
                        <span class="block font-black text-slate-900 text-sm mt-0.5">{{ $partner->full_name }}</span>
                        <span class="block text-[10px] text-gray-400 font-mono font-normal">ID: {{ $partner->mitra_id }}</span>
                    </div>
                    <div>
                        <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Email Terdaftar</span>
                        <span class="block font-bold text-indigo-700 font-mono break-all mt-1" style="font-size: 11px;">{{ $partner->email ?: ($partner->user?->email ?: '-') }}</span>
                    </div>
                    <div>
                        <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Durasi Verifikasi</span>
                        <span class="block font-bold text-slate-900 font-mono text-sm mt-0.5">
                            @if($partner->approval_status !== 'none' && $partner->period_approval)
                                {{ $partner->period_approval->approved_minutes }} menit
                            @else
                                Belum diperiksa
                            @endif
                        </span>
                        <span class="block text-[10px] text-gray-400 font-normal">Dari {{ floor($partner->total_reported_minutes / 60) }}j {{ $partner->total_reported_minutes % 60 }}m laporan</span>
                    </div>
                    <div>
                        <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Status Periode</span>
                        <div class="mt-1">
                            @if($partner->approval_status === 'paid')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">PAID (Lunas)</span>
                            @elseif($partner->approval_status === 'approved')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">APPROVED (Rilis)</span>
                            @elseif($partner->approval_status === 'draft')
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">DRAF (Admin)</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-50 text-gray-500 border border-gray-200">Belum Diperiksa</span>
                            @endif
                        </div>
                    </div>
                    @if($partner->period_approval && $partner->period_approval->verifier_notes)
                        <div class="col-span-2 md:col-span-4 border-t border-gray-200 pt-2 mt-1">
                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Catatan Masukan Admin</span>
                            <p class="block text-slate-800 italic mt-0.5 leading-normal">"{{ $partner->period_approval->verifier_notes }}"</p>
                        </div>
                    @endif
                </div>

                <!-- Daily Reports List under this Partner -->
                <div class="space-y-4 pt-2">
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider font-mono">Daftar Laporan Harian (Evidence)</h3>
                    
                    <div class="space-y-3">
                        @forelse($partner->period_reports as $reportIndex => $report)
                            <div class="border border-slate-100 bg-slate-50/30 rounded-2xl p-4 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center page-card">
                                <!-- Report Metadata (Left) -->
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9px] font-black bg-indigo-50 border border-indigo-100 text-indigo-750 px-1.5 py-0.5 rounded-md font-mono">NO. {{ $reportIndex + 1 }}</span>
                                        <span class="text-[10px] font-mono text-gray-400">ID: {{ $report->id }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 pt-1.5 text-[11px] font-semibold text-slate-700">
                                        <div>
                                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Tanggal Kerja</span>
                                            <span class="font-bold">{{ $report->submission_date->translatedFormat('d F Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Durasi Kirim</span>
                                            <span class="font-bold text-indigo-755 font-mono">{{ $report->submitted_duration_formatted }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] text-gray-400 font-normal uppercase font-mono">Status Harian</span>
                                            @if($report->qc_status === 'approved')
                                                <span class="text-emerald-600 font-bold uppercase" style="font-size: 10px;">Approved ({{ $report->approved_duration_minutes }}m)</span>
                                            @elseif($report->qc_status === 'rejected')
                                                <span class="text-rose-600 font-bold uppercase" style="font-size: 10px;">Revisi (Ditolak)</span>
                                            @else
                                                <span class="text-amber-600 font-bold uppercase" style="font-size: 10px;">Review</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($report->verifier_notes)
                                        <div class="text-[10px] text-gray-500 italic mt-1 leading-normal bg-white p-2 rounded-lg border border-slate-100">
                                            Masukan harian: "{{ $report->verifier_notes }}"
                                        </div>
                                    @endif
                                </div>

                                <!-- Evidence Images (Right) -->
                                <div class="flex gap-3 shrink-0 self-end md:self-center">
                                    <div class="text-center">
                                        <span class="block text-[8px] font-black text-gray-400 uppercase font-mono mb-1">1. Bukti Email</span>
                                        <div style="width: 100px; height: 100px;" class="bg-white rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center shrink-0">
                                            @if($report->evidence_email_image_url)
                                                <img src="{{ $report->evidence_email_image_url }}" style="width: 100%; height: 100%; object-fit: contain;" class="bg-white" alt="Email Evidence">
                                            @else
                                                <span class="text-[9px] text-gray-300 font-mono">Tidak ada</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-[8px] font-black text-gray-400 uppercase font-mono mb-1">2. Bukti Kualitas</span>
                                        <div style="width: 100px; height: 100px;" class="bg-white rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center shrink-0">
                                            @if($report->evidence_app_quality_image_url)
                                                <img src="{{ $report->evidence_app_quality_image_url }}" style="width: 100%; height: 100%; object-fit: contain;" class="bg-white" alt="Quality Evidence">
                                            @else
                                                <span class="text-[9px] text-gray-300 font-mono">Tidak ada</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-400 text-xs">
                                Tidak ada laporan harian dalam periode ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Page Break after each Partner (except last one in print) -->
            <div class="page-break no-print-last"></div>
        @empty
            <div class="bg-white border border-gray-200 rounded-3xl p-12 text-center text-gray-400 shadow-sm">
                Tidak ada data laporan mitra yang cocok untuk periode terpilih.
            </div>
        @endforelse

    </div>

    <!-- Auto print trigger -->
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 800);
        });
    </script>
</body>
</html>
