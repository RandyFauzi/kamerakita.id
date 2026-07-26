<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Report Summary - {{ $periodLabel ?: ($startDate . ' - ' . $endDate) }}</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Exact A4 Standard Professional Print CSS */
        @page {
            size: A4 portrait;
            margin: 1.5cm; /* Standard professional document margin */
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
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
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white border border-gray-200 rounded-3xl p-4 flex justify-between items-center shadow-sm">
        <div class="space-y-0.5">
            <h4 class="font-bold text-gray-800 text-sm">A4 Print Preview Mode</h4>
            <p class="text-xs text-gray-400">Click print button to save this report as a PDF file.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('video-submissions.qc-room') }}" class="px-4 py-2 border border-gray-200 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-50 transition">
                Back
            </a>
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print / Save PDF
            </button>
        </div>
    </div>

    <!-- Printable container -->
    <div class="max-w-4xl mx-auto">
        
        @forelse($partners as $partnerIndex => $partner)
            <!-- Recap Card per Partner -->
            <div class="page-card bg-white border border-gray-200 rounded-2xl p-5 shadow-sm mb-5">
                <!-- Recap Header -->
                <div class="border-b border-gray-150 pb-3 mb-3 flex justify-between items-start">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/Logo.webp') }}" style="width: 36px; height: 36px; object-fit: contain; flex-shrink: 0;" alt="Logo">
                        <div>
                            <h2 class="text-sm font-black tracking-tight text-gray-900">KAMERAKITA <span class="text-indigo-600">AI</span></h2>
                            <p class="text-[8px] font-bold uppercase tracking-wider text-indigo-600 font-mono">Video QC Verification Summary - {{ $periodLabel ?: 'Custom Period' }}</p>
                        </div>
                    </div>
                    <div class="text-right text-[8px] text-gray-550 font-mono space-y-0.5">
                        <div>Print Date: <span class="text-gray-800 font-bold">{{ date('d F Y H:i') }}</span></div>
                        <div>Date Range: <span class="text-gray-800 font-bold">{{ $startDate }} - {{ $endDate }}</span></div>
                    </div>
                </div>

                <!-- Partner Profile Info -->
                <div class="grid grid-cols-4 gap-3 bg-slate-50 p-3.5 rounded-xl mb-4 text-[10px] font-semibold text-slate-700">
                    <div>
                        <span class="block text-[8px] text-gray-400 font-normal uppercase font-mono">Partner (Worker)</span>
                        <span class="block font-black text-slate-900 text-xs mt-0.5">{{ $partner->full_name }}</span>
                        <span class="block text-[9px] text-gray-400 font-mono font-normal">ID: {{ $partner->mitra_id }}</span>
                        <span class="block font-normal text-indigo-700 font-mono break-all mt-0.5" style="font-size: 9px;">{{ $partner->email ?: ($partner->user?->email ?: '-') }}</span>
                    </div>
                    <div>
                        <span class="block text-[8px] text-gray-400 font-normal uppercase font-mono">Total Submitted</span>
                        <span class="block font-black text-slate-900 font-mono mt-0.5">
                            {{ $partner->total_reported_minutes }} min
                        </span>
                        <span class="block text-[9px] text-gray-500 font-normal">
                            ({{ floor($partner->total_reported_minutes / 60) }}h {{ $partner->total_reported_minutes % 60 }}m)
                        </span>
                    </div>
                    <div>
                        <span class="block text-[8px] text-gray-400 font-normal uppercase font-mono">Total Verified</span>
                        <span class="block font-black text-emerald-700 font-mono mt-0.5">
                            @if($partner->approval_status !== 'none' && $partner->period_approval)
                                {{ $partner->period_approval->approved_minutes }} min
                            @else
                                0 min
                            @endif
                        </span>
                        <span class="block text-[9px] text-gray-500 font-normal">
                            @if($partner->approval_status !== 'none' && $partner->period_approval)
                                ({{ floor($partner->period_approval->approved_minutes / 60) }}h {{ $partner->period_approval->approved_minutes % 60 }}m)
                            @else
                                (0h 0m)
                            @endif
                        </span>
                        
                        <!-- Difference / Unapproved Duration -->
                        @php
                            $verifiedMinutes = ($partner->approval_status !== 'none' && $partner->period_approval) ? $partner->period_approval->approved_minutes : 0;
                            $diffMinutes = max(0, $partner->total_reported_minutes - $verifiedMinutes);
                        @endphp
                        <span class="block text-[8px] text-rose-600 font-bold mt-1">
                            Not Approved: {{ $diffMinutes }} min ({{ floor($diffMinutes / 60) }}h {{ $diffMinutes % 60 }}m)
                        </span>
                    </div>
                    <div>
                        <span class="block text-[8px] text-gray-400 font-normal uppercase font-mono">Period Status</span>
                        <div class="mt-0.5">
                            @if($partner->approval_status === 'paid')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">PAID</span>
                            @elseif($partner->approval_status === 'approved')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">APPROVED</span>
                            @elseif($partner->approval_status === 'draft')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">DRAFT</span>
                            @else
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-gray-50 text-gray-500 border border-gray-200">PENDING</span>
                            @endif
                        </div>
                    </div>
                    @if($partner->period_approval && $partner->period_approval->verifier_notes)
                        <div class="col-span-4 border-t border-gray-200 pt-1.5 mt-1">
                            <span class="block text-[8px] text-gray-400 font-normal uppercase font-mono">Feedback Notes</span>
                            <p class="block text-slate-800 italic mt-0.5 leading-normal">"{{ $partner->period_approval->verifier_notes }}"</p>
                        </div>
                    @endif
                </div>

                <!-- Daily Reports List under this Partner -->
                <div class="space-y-2.5">
                    <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-wider font-mono">Daily Work Reports & Evidence</h3>
                    
                    <div class="space-y-2">
                        @forelse($partner->period_reports as $reportIndex => $report)
                            <!-- Table-like layout using Flexbox (Guaranteed no-wrap on Print) -->
                            <div class="border border-gray-150 bg-white rounded-xl p-3 flex justify-between items-center page-card" style="break-inside: avoid; page-break-inside: avoid;">
                                <!-- Left side: Metadata -->
                                <div class="flex-1 min-w-0 pr-4 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[8px] font-black bg-indigo-50 border border-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-mono">NO. {{ $reportIndex + 1 }}</span>
                                        <span class="text-[9px] font-mono text-gray-400">ID: {{ $report->id }}</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 pt-1 text-[10px] text-gray-700">
                                        <div>
                                            <span class="block text-[8px] text-gray-400 font-normal uppercase font-mono">Work Date</span>
                                            <span class="font-bold text-gray-900">{{ $report->submission_date->format('d M Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[8px] text-gray-400 font-normal uppercase font-mono">Submitted Duration</span>
                                            <span class="font-bold text-indigo-750 font-mono">{{ $report->submitted_duration_formatted }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[8px] text-gray-400 font-normal uppercase font-mono">Daily Status</span>
                                            @if($report->qc_status === 'approved')
                                                <span class="text-emerald-600 font-bold uppercase text-[9px]">Approved ({{ $report->approved_duration_minutes }}m)</span>
                                            @elseif($report->qc_status === 'rejected')
                                                <span class="text-rose-600 font-bold uppercase text-[9px]">Revision</span>
                                            @else
                                                <span class="text-amber-600 font-bold uppercase text-[9px]">Review</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($report->verifier_notes)
                                        <div class="text-[9px] text-gray-500 italic mt-1 leading-normal bg-slate-50 p-1.5 rounded border border-gray-100">
                                            Daily Notes: "{{ $report->verifier_notes }}"
                                        </div>
                                    @endif
                                </div>

                                <!-- Right side: Proof Images (Side-by-side) -->
                                <div class="flex gap-2 shrink-0">
                                    <div class="text-center">
                                        <span class="block text-[7px] font-bold text-gray-450 uppercase font-mono mb-0.5">1. Email</span>
                                        <div style="width: 70px; height: 70px;" class="bg-gray-50 rounded border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                                            @if($report->evidence_email_image_url)
                                                <img src="{{ $report->evidence_email_image_url }}" style="width: 100%; height: 100%; object-fit: contain;">
                                            @else
                                                <span class="text-[8px] text-gray-300">No Image</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-[7px] font-bold text-gray-450 uppercase font-mono mb-0.5">2. Quality</span>
                                        <div style="width: 70px; height: 70px;" class="bg-gray-50 rounded border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                                            @if($report->evidence_app_quality_image_url)
                                                <img src="{{ $report->evidence_app_quality_image_url }}" style="width: 100%; height: 100%; object-fit: contain;">
                                            @else
                                                <span class="text-[8px] text-gray-300">No Image</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-400 text-xs">
                                No daily work reports submitted for this period.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Page Break after each Partner (except last one in print) -->
            <div class="page-break no-print-last"></div>
        @empty
            <div class="bg-white border border-gray-200 rounded-3xl p-12 text-center text-gray-400 shadow-sm">
                No work report summaries match the selected period.
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
