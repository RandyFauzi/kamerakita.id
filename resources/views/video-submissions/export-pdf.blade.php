<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QC_Video_Reports_Export_{{ date('Ymd_His') }}</title>
    <!-- Tailwind CSS for high fidelity print styling -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-card {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                border: 1px solid #e2e8f0 !important;
                margin-bottom: 2rem !important;
                background-color: white !important;
            }
        }
        @page {
            size: A4;
            margin: 1.5cm;
        }
        .page-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans p-6 antialiased">

    <!-- Top floating navigation bar (Hidden in Print) -->
    <div class="no-print max-w-5xl mx-auto mb-8 bg-white border border-gray-200 rounded-3xl p-4 flex justify-between items-center shadow-sm">
        <div class="space-y-0.5">
            <h4 class="font-bold text-gray-800 text-sm">Mode Pratinjau Cetak A4</h4>
            <p class="text-xs text-gray-400">Tekan cetak untuk menyimpan laporan sebagai file PDF.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('video-submissions.qc-room') }}" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-50 transition">
                Kembali
            </a>
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Printable container -->
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Print Header -->
        <div class="border-b-2 border-gray-900 pb-4 flex justify-between items-end">
            <div class="space-y-1">
                <h2 class="text-2xl font-black tracking-tight text-gray-900">KAMERAKITA<span class="text-indigo-650">.AI</span></h2>
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-650 font-mono">Laporan Rekapitulasi Verifikasi QC Video</p>
                <p class="text-[10px] text-gray-400">Dicetak pada: {{ date('d F Y H:i:s') }}</p>
            </div>
            <div class="text-right text-xs text-gray-550 font-medium space-y-1 font-mono">
                <div>Status Filter: <strong class="text-gray-900 uppercase">{{ $status }}</strong></div>
                @if($startDate || $endDate)
                    <div>Periode: <strong>{{ $startDate ?? 'Mulai Awal' }}</strong> s/d <strong>{{ $endDate ?? 'Hari Ini' }}</strong></div>
                @endif
                <div>Total Laporan: <strong class="text-gray-900">{{ $reports->count() }} Laporan</strong></div>
            </div>
        </div>

        <!-- Reports list -->
        <div class="space-y-8 pt-4">
            @forelse($reports as $index => $report)
                <div class="page-card bg-white border border-gray-150 rounded-[32px] p-6 shadow-sm flex flex-col space-y-4">
                    <!-- Report Header -->
                    <div class="flex justify-between items-start pb-4 border-b border-gray-100">
                        <div>
                            <span class="block text-[10px] font-black tracking-widest text-indigo-600 font-mono">LAPORAN NO. {{ $index + 1 }}</span>
                            <h3 class="text-base font-bold text-gray-900 mt-0.5">ID: {{ $report->id }}</h3>
                            <p class="text-xs text-gray-405 font-medium mt-1">Submitter: <strong class="text-gray-950">{{ $report->partner->full_name }}</strong> (ID: {{ $report->partner->mitra_id }})</p>
                        </div>
                        <div class="text-right font-mono space-y-1">
                            <div class="text-xs">Tanggal Kerja: <strong class="text-gray-900">{{ $report->submission_date->format('d/m/Y') }}</strong></div>
                            <div class="text-xs">Status QC: 
                                @if($report->qc_status === 'pending')
                                    <span class="text-yellow-600 font-bold uppercase">Pending</span>
                                @elseif($report->qc_status === 'approved')
                                    <span class="text-emerald-600 font-bold uppercase">Approved ({{ $report->approved_duration_minutes }}m)</span>
                                @else
                                    <span class="text-rose-600 font-bold uppercase">Rejected</span>
                                @endif
                            </div>
                            <div class="text-xs">Durasi Kirim: <strong class="text-gray-900">{{ $report->submitted_duration_formatted }}</strong></div>
                        </div>
                    </div>

                    <!-- Evidence Images side-by-side -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Evidence Email -->
                        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 flex flex-col justify-between">
                            <span class="block text-xs font-bold text-slate-800 border-b border-gray-100 pb-1.5">1. Bukti Gambar Email</span>
                            <div class="mt-2 aspect-video bg-white rounded-xl overflow-hidden border border-gray-200 relative flex items-center justify-center">
                                @if($report->evidence_email_image_path)
                                    <img src="{{ asset('storage/' . $report->evidence_email_image_path) }}" class="object-contain w-full h-full bg-white" alt="Bukti Email">
                                @else
                                    <span class="text-[10px] text-gray-400">Gambar tidak ditemukan</span>
                                @endif
                            </div>
                        </div>

                        <!-- Evidence App Quality -->
                        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 flex flex-col justify-between">
                            <span class="block text-xs font-bold text-slate-800 border-b border-gray-100 pb-1.5">2. Bukti Kualitas Aplikasi</span>
                            <div class="mt-2 aspect-video bg-white rounded-xl overflow-hidden border border-gray-200 relative flex items-center justify-center">
                                @if($report->evidence_app_quality_image_path)
                                    <img src="{{ asset('storage/' . $report->evidence_app_quality_image_path) }}" class="object-contain w-full h-full bg-white" alt="Bukti Kualitas Aplikasi">
                                @else
                                    <span class="text-[10px] text-gray-400">Gambar tidak ditemukan</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($report->verifier_notes)
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs">
                            <span class="block font-bold text-gray-400 uppercase tracking-wider text-[10px] mb-1">Catatan/Alasan Verifikator:</span>
                            <p class="text-gray-700 italic">"{{ $report->verifier_notes }}"</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-12 text-gray-400 text-sm">
                    Tidak ada data laporan video yang cocok dengan filter cetak ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Auto print trigger -->
    <script>
        window.addEventListener('load', () => {
            // Trigger browser print dialog after loading everything
            setTimeout(() => {
                window.print();
            }, 1000);
        });
    </script>
</body>
</html>
