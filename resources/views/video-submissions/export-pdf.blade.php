<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KameraKita.id - {{ date('d-m-Y') }}</title>
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
                padding: 1.2cm !important;
                margin: 0 !important;
            }
            .page-card {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
                border: 1px solid #e2e8f0 !important;
                margin-bottom: 1rem !important;
                background-color: white !important;
            }
        }
        @page {
            size: A4;
            margin: 0; /* Hides native browser header (date, title) and footer (URL, page) */
        }
        .page-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans p-6 antialiased">

    <!-- Top floating navigation bar (Hidden in Print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white border border-gray-200 rounded-3xl p-4 flex justify-between items-center shadow-sm">
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
    <div class="max-w-5xl mx-auto space-y-4">
        <!-- Print Header -->
        <div class="border-b-2 border-gray-900 pb-3 flex justify-between items-end">
            <div class="space-y-0.5">
                <h2 class="text-xl font-black tracking-tight text-gray-900">KAMERAKITA<span class="text-indigo-650">.AI</span></h2>
                <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-650 font-mono">Laporan Rekapitulasi Verifikasi QC Video</p>
                <p class="text-[9px] text-gray-400">Dicetak pada: {{ date('d F Y H:i:s') }}</p>
            </div>
            <div class="text-right text-[10px] text-gray-500 font-medium space-y-0.5 font-mono">
                <div>Status Filter: <strong class="text-gray-900 uppercase">{{ $status }}</strong></div>
                @if($startDate || $endDate)
                    <div>Periode: <strong>{{ $startDate ?? 'Mulai Awal' }}</strong> s/d <strong>{{ $endDate ?? 'Hari Ini' }}</strong></div>
                @endif
                <div>Total Laporan: <strong class="text-gray-900">{{ $reports->count() }} Laporan</strong></div>
            </div>
        </div>

        <!-- Reports list -->
        <div class="space-y-3 pt-2">
            @forelse($reports as $index => $report)
                <div class="page-card bg-white border border-gray-200 rounded-2xl p-4 flex gap-4 items-center shadow-sm">
                    <!-- Left: Metadata (compact) -->
                    <div class="flex-1 min-w-[280px] space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-black uppercase bg-indigo-50 border border-indigo-100 text-indigo-700 px-2 py-0.5 rounded-md font-mono">NO. {{ $index + 1 }}</span>
                            <span class="text-[10px] font-mono text-gray-400">ID: {{ substr($report->id, 0, 18) }}...</span>
                        </div>
                        <div class="text-xs font-bold text-gray-900">
                            {{ $report->partner->full_name }} 
                            <span class="text-[10px] font-normal text-gray-400">({{ $report->partner->mitra_id }})</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 text-[10px] font-mono text-gray-600">
                            <div>Tanggal: <span class="font-bold text-gray-800">{{ $report->submission_date->format('d/m/Y') }}</span></div>
                            <div>Kirim: <span class="font-bold text-gray-800">{{ $report->submitted_duration_formatted }}</span></div>
                            <div class="col-span-2">
                                QC Status: 
                                @if($report->qc_status === 'pending')
                                    <span class="text-yellow-600 font-bold uppercase">Pending</span>
                                @elseif($report->qc_status === 'approved')
                                    <span class="text-emerald-600 font-bold uppercase">Approved ({{ $report->approved_duration_minutes }}m)</span>
                                @else
                                    <span class="text-rose-600 font-bold uppercase">Rejected</span>
                                @endif
                            </div>
                        </div>

                        @if($report->verifier_notes)
                            <div class="text-[9px] text-gray-500 italic bg-gray-50 border border-gray-150 rounded-lg p-1.5 mt-1 leading-normal">
                                notes: "{{ $report->verifier_notes }}"
                            </div>
                        @endif
                    </div>

                    <!-- Right: Compact Images (side-by-side) with strict inline dimensions -->
                    <div class="flex gap-3 shrink-0">
                        <div class="text-center">
                            <span class="block text-[8px] font-black text-gray-400 uppercase font-mono mb-1">1. Bukti Email</span>
                            <div style="width: 110px; height: 150px;" class="bg-white rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center shrink-0">
                                @if($report->evidence_email_image_url)
                                    <img src="{{ $report->evidence_email_image_url }}" style="width: 100%; height: 100%; object-fit: contain;" class="bg-white" alt="Bukti Email">
                                @else
                                    <span class="text-[9px] text-gray-300 font-mono">Tidak ada</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="block text-[8px] font-black text-gray-400 uppercase font-mono mb-1">2. Bukti Kualitas</span>
                            <div style="width: 110px; height: 150px;" class="bg-white rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center shrink-0">
                                @if($report->evidence_app_quality_image_url)
                                    <img src="{{ $report->evidence_app_quality_image_url }}" style="width: 100%; height: 100%; object-fit: contain;" class="bg-white" alt="Bukti Kualitas">
                                @else
                                    <span class="text-[9px] text-gray-300 font-mono">Tidak ada</span>
                                @endif
                            </div>
                        </div>
                    </div>
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
            }, 800);
        });
    </script>
</body>
</html>
