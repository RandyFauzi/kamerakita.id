<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                Detail Mitra: {{ $partner->nama_lengkap }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('partners.edit', $partner) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest transition duration-150 shadow-md shadow-amber-100">
                    Edit Profil
                </a>
                <a href="{{ route('partners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none transition duration-150">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Top Summary Banner -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-sm p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="text-3xl font-extrabold tracking-tight">{{ $partner->nama_lengkap }}</span>
                        <span class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                            {{ $partner->id_mitra }}
                        </span>
                    </div>
                    <p class="text-sm text-blue-100 mt-1">Bergabung sejak {{ $partner->tanggal_bergabung?->translatedFormat('d F Y') }} ({{ $partner->tanggal_bergabung?->diffForHumans() }})</p>
                </div>
                <div class="flex gap-4">
                    <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl">
                        <span class="block text-xs text-blue-100 uppercase font-semibold">Status Mitra</span>
                        <span class="block text-lg font-bold uppercase mt-0.5">{{ $partner->status_mitra }}</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl">
                        <span class="block text-xs text-blue-100 uppercase font-semibold">Rate per Jam</span>
                        <span class="block text-lg font-bold mt-0.5">Rp{{ number_format($partner->finance->rate_per_jam ?? 54000, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Two-Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left: Profil & Demografi -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 lg:col-span-2 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">Profil Lengkap & Demografi</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6 mt-4">
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">NIK (No. KTP)</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">{{ $partner->nik }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Jenis Kelamin</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">
                                    {{ $partner->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Tempat, Tanggal Lahir</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">
                                    {{ $partner->tempat_lahir }}, {{ $partner->tanggal_lahir?->translatedFormat('d F Y') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Nomor WhatsApp</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $partner->nomor_whatsapp) }}" target="_blank" class="text-indigo-600 hover:underline flex items-center gap-1">
                                        {{ $partner->nomor_whatsapp }}
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Email Aktif</dt>
                                <dd class="text-sm font-medium text-gray-900 mt-1">{{ $partner->email }}</dd>
                            </div>
                            <div class="md:col-span-2">
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Alamat Lengkap</dt>
                                <dd class="text-sm font-medium text-gray-950 mt-1 bg-gray-50 rounded-xl p-3 border border-gray-100">
                                    {{ $partner->alamat_lengkap }}<br>
                                    <span class="text-xs text-gray-500">Kec. {{ $partner->kecamatan }}, Kota {{ $partner->kota }}, {{ $partner->kode_pos }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Recent Submissions -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">Riwayat Video Terakhir</h3>
                        <div class="overflow-x-auto mt-4">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID Video</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Durasi Kirim</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Durasi Setuju</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($partner->videoSubmissions as $submission)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $submission->id_video }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $submission->kategori_tugas }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $submission->durasi_dikirim_formatted }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $submission->durasi_disetujui_formatted }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'bg-gray-100 text-gray-800',
                                                        'approved' => 'bg-green-100 text-green-800',
                                                        'rejected' => 'bg-red-100 text-red-800',
                                                        'need_retake' => 'bg-amber-100 text-amber-800',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClasses[$submission->status_video] ?? 'bg-gray-100' }}">
                                                    {{ ucfirst($submission->status_video) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-6 text-center text-xs text-gray-400">Belum ada riwayat video untuk mitra ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: Equipment & Finance -->
                <div class="space-y-6">
                    
                    <!-- Perangkat -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 space-y-4">
                        <h3 class="text-md font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Spesifikasi Perangkat
                        </h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Smartphone</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $partner->equipment->tipe_smartphone ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Status Headstrap</dt>
                                <dd class="mt-1">
                                    @php
                                        $headstrapStatus = $partner->equipment->status_headstrap ?? 'belum_punya';
                                        $headstrapClasses = [
                                            'belum_punya' => 'bg-gray-100 text-gray-800',
                                            'proses_reimburse' => 'bg-yellow-100 text-yellow-800',
                                            'lunas' => 'bg-green-100 text-green-800',
                                        ];
                                        $headstrapLabels = [
                                            'belum_punya' => 'Belum Punya',
                                            'proses_reimburse' => 'Proses Reimburse',
                                            'lunas' => 'Lunas / Diganti',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $headstrapClasses[$headstrapStatus] }}">
                                        {{ $headstrapLabels[$headstrapStatus] }}
                                    </span>
                                </dd>
                            </div>
                            @if($partner->equipment && $partner->equipment->nominal_reimburse > 0)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-400 uppercase">Nominal Reimburse</dt>
                                    <dd class="text-sm font-bold text-gray-900">Rp{{ number_format($partner->equipment->nominal_reimburse, 0, ',', '.') }}</dd>
                                </div>
                            @endif
                            @if($partner->equipment && $partner->equipment->bukti_nota_headstrap)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-400 uppercase">Nota Pembelian</dt>
                                    <dd class="text-sm font-medium text-indigo-600 hover:underline mt-1">
                                        <a href="{{ asset('storage/' . $partner->equipment->bukti_nota_headstrap) }}" target="_blank" class="flex items-center gap-1">
                                            Unduh Nota
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Rekening Keuangan -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6 space-y-4">
                        <h3 class="text-md font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Rekening & Keuangan
                        </h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Bank / E-Wallet</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $partner->finance->nama_bank_atau_ewallet ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Nomor Rekening</dt>
                                <dd class="text-sm font-bold text-gray-900 tracking-wider">{{ $partner->finance->nomor_rekening ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 uppercase">Atas Nama Pemilik</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $partner->finance->nama_pemilik_rekening ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
