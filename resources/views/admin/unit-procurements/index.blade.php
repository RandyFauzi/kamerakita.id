<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">Pengadaan Unit Baru</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Kelola usulan, anggaran pengadaan perangkat/kamera, dan alur aset KameraKita.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="window.dispatchEvent(new CustomEvent('open-procurement-modal'))" 
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl shadow-sm text-sm font-semibold transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Ajukan Unit Baru
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6" x-data="procurementManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alerts -->
            @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl shadow-sm">
                <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Terdapat kesalahan input:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- 1. Top Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Active -->
                <div class="bg-gradient-to-br from-blue-600 to-indigo-800 rounded-3xl p-6 text-white shadow-lg shadow-blue-500/20 relative overflow-hidden transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-white/10 blur-xl"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-bold tracking-wider text-blue-200">Pengadaan Aktif</span>
                        <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold tracking-tight">{{ number_format($stats['total_active']) }}</div>
                        <p class="text-xs text-blue-200 mt-1">Dalam alur approval & pembelian</p>
                    </div>
                </div>

                <!-- Menunggu Persetujuan -->
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-bold tracking-wider text-gray-400">Menunggu Review</span>
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-amber-600 tracking-tight">{{ number_format($stats['pending_approval']) }}</div>
                        <p class="text-xs text-gray-500 mt-1">Perlu persetujuan Superadmin</p>
                    </div>
                </div>

                <!-- Estimasi Anggaran -->
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-bold tracking-wider text-gray-400">Estimasi Anggaran</span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl font-extrabold text-gray-900 tracking-tight">
                            Rp {{ number_format($stats['total_estimated_budget'], 0, ',', '.') }}
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Total usulan & order aktif</p>
                    </div>
                </div>

                <!-- Unit Diterima -->
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-bold tracking-wider text-gray-400">Total Unit Sampai</span>
                        <div class="w-10 h-10 rounded-2xl bg-purple-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-purple-700 tracking-tight">{{ number_format($stats['total_received_units']) }} <span class="text-sm font-semibold text-gray-500">Unit</span></div>
                        <p class="text-xs text-gray-500 mt-1">Masuk inventaris operasional</p>
                    </div>
                </div>
            </div>

            <!-- 2. Filter & Search Panel -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
                <form method="GET" action="{{ route('admin.unit-procurements.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                    <!-- Search Input -->
                    <div class="lg:col-span-5 relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari kode PO, nama unit, vendor, serial number..." 
                               class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <!-- Category Filter -->
                    <div class="lg:col-span-2">
                        <select name="category" class="w-full py-2.5 px-3 text-sm bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kategori</option>
                            <option value="camera" {{ request('category') == 'camera' ? 'selected' : '' }}>Kamera</option>
                            <option value="lens" {{ request('category') == 'lens' ? 'selected' : '' }}>Lensa</option>
                            <option value="smartphone" {{ request('category') == 'smartphone' ? 'selected' : '' }}>Smartphone</option>
                            <option value="audio_mic" {{ request('category') == 'audio_mic' ? 'selected' : '' }}>Audio / Mic</option>
                            <option value="lighting_rig" {{ request('category') == 'lighting_rig' ? 'selected' : '' }}>Lighting & Rig</option>
                            <option value="storage_pc" {{ request('category') == 'storage_pc' ? 'selected' : '' }}>PC / Storage</option>
                            <option value="accessory" {{ request('category') == 'accessory' ? 'selected' : '' }}>Aksesoris</option>
                            <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="lg:col-span-2">
                        <select name="status" class="w-full py-2.5 px-3 text-sm bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Menunggu Approval</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="purchasing" {{ request('status') == 'purchasing' ? 'selected' : '' }}>Proses Pembelian</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Diterima / Selesai</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div class="lg:col-span-2">
                        <select name="priority" class="w-full py-2.5 px-3 text-sm bg-gray-50 border border-gray-200 rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Urgensi</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Mendesak (Urgent)</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                        </select>
                    </div>

                    <!-- Submit / Reset -->
                    <div class="lg:col-span-1 flex items-center gap-1">
                        <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white p-2.5 rounded-2xl flex items-center justify-center transition-colors shadow-sm" title="Terapkan Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        </button>
                        @if(request()->hasAny(['search', 'category', 'status', 'priority']))
                        <a href="{{ route('admin.unit-procurements.index') }}" class="p-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl flex items-center justify-center transition-colors" title="Reset Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- 3. Main Data Table -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-4 px-5">Kode & Unit</th>
                                <th class="py-4 px-4">Kategori</th>
                                <th class="py-4 px-4">Urgensi</th>
                                <th class="py-4 px-4">Qty & Estimasi Biaya</th>
                                <th class="py-4 px-4">Status Alur</th>
                                <th class="py-4 px-4">Pemohon</th>
                                <th class="py-4 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($procurements as $item)
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <!-- Kode & Unit -->
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded-lg bg-gray-100 text-gray-700">
                                            {{ $item->procurement_code }}
                                        </span>
                                        @if($item->purchase_url)
                                        <a href="{{ $item->purchase_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700 inline-flex items-center" title="Buka Link Toko">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                        @endif
                                    </div>
                                    <div class="font-bold text-gray-900 mt-1">{{ $item->item_name }}</div>
                                    @if($item->vendor_name)
                                    <div class="text-xs text-gray-500">Vendor: {{ $item->vendor_name }}</div>
                                    @endif
                                </td>

                                <!-- Kategori -->
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $item->category_label }}
                                    </span>
                                </td>

                                <!-- Urgensi -->
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border {{ $item->priority_badge['bg'] }}">
                                        {{ $item->priority_badge['label'] }}
                                    </span>
                                </td>

                                <!-- Qty & Biaya -->
                                <td class="py-4 px-4">
                                    <div class="font-extrabold text-gray-900">
                                        Rp {{ number_format($item->total_estimated_price, 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $item->quantity }} unit @ Rp {{ number_format($item->estimated_unit_price, 0, ',', '.') }}
                                    </div>
                                    @if($item->actual_total_price)
                                    <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">
                                        Riil: Rp {{ number_format($item->actual_total_price, 0, ',', '.') }}
                                    </div>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold border shadow-xs {{ $item->status_badge['bg'] }}">
                                        {{ $item->status_badge['label'] }}
                                    </span>
                                    @if($item->status === 'received' && $item->serial_number)
                                    <div class="text-[11px] text-gray-500 font-mono mt-1">SN: {{ $item->serial_number }}</div>
                                    @endif
                                </td>

                                <!-- Pemohon -->
                                <td class="py-4 px-4">
                                    <div class="font-medium text-gray-800 text-xs">{{ $item->requester->name ?? 'User' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $item->created_at->format('d M Y') }}</div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Update Status Button -->
                                        <button type="button" 
                                                @click="openStatusModal({{ json_encode($item) }})"
                                                class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1"
                                                title="Ubah Status / Realisasi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Status
                                        </button>

                                        <!-- Edit Data -->
                                        <button type="button" 
                                                @click="openEditModal({{ json_encode($item) }})"
                                                class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors"
                                                title="Edit Informasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('admin.unit-procurements.destroy', $item) }}" onsubmit="return confirm('Hapus pengadaan unit ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-3 text-gray-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    </div>
                                    <div class="text-base font-bold text-gray-700">Belum ada data pengadaan unit</div>
                                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Klik tombol "Ajukan Unit Baru" di atas untuk menambahkan usulan pengadaan unit atau peralatan kerja baru.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($procurements->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $procurements->links() }}
                </div>
                @endif
            </div>

            <!-- MODAL: Ajukan Unit Baru -->
            <div x-show="showCreateModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div @click.away="showCreateModal = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl relative">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Pengajuan Unit Baru</h3>
                            <p class="text-xs text-gray-500">Ajukan kebutuhan pembelian kamera, perangkat, atau alat kerja.</p>
                        </div>
                        <button type="button" @click="showCreateModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.unit-procurements.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Perangkat / Unit <span class="text-rose-500">*</span></label>
                            <input type="text" name="item_name" required placeholder="Contoh: Sony ZV-E10 Kit 16-50mm" 
                                   class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                                <select name="category" required class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="camera">Kamera</option>
                                    <option value="lens">Lensa</option>
                                    <option value="smartphone">Smartphone</option>
                                    <option value="audio_mic">Audio / Mic</option>
                                    <option value="lighting_rig">Lighting & Rig</option>
                                    <option value="storage_pc">PC / Laptop / SSD</option>
                                    <option value="accessory">Aksesoris</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Prioritas / Urgensi <span class="text-rose-500">*</span></label>
                                <select name="priority" required class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="medium">Sedang (Normal)</option>
                                    <option value="high">Tinggi</option>
                                    <option value="urgent">Mendesak (Urgent)</option>
                                    <option value="low">Rendah</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah (Qty) <span class="text-rose-500">*</span></label>
                                <input type="number" name="quantity" min="1" value="1" required 
                                       class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Estimasi Harga Satuan (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="estimated_unit_price" min="0" step="1000" placeholder="Contoh: 8500000" required 
                                       class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Supplier / Vendor</label>
                                <input type="text" name="vendor_name" placeholder="Misal: Tokopedia - Doss Camera" 
                                       class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Link Pembelian (URL)</label>
                                <input type="url" name="purchase_url" placeholder="https://..." 
                                       class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Kebutuhan / Catatan</label>
                            <textarea name="purpose_notes" rows="2" placeholder="Jelaskan kebutuhan pengadaan alat ini..." 
                                      class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">Kirim Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL: Update Status / Realisasi -->
            <div x-show="showStatusModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div @click.away="showStatusModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Update Status & Realisasi</h3>
                            <p class="text-xs text-gray-500" x-text="activeItem ? activeItem.procurement_code + ' - ' + activeItem.item_name : ''"></p>
                        </div>
                        <button type="button" @click="showStatusModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form :action="'/admin/unit-procurements/' + (activeItem ? activeItem.id : '') + '/status'" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Alur Pengadaan <span class="text-rose-500">*</span></label>
                            <select name="status" x-model="statusForm.status" required class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending_approval">Menunggu Approval</option>
                                <option value="approved">Disetujui (Approved)</option>
                                <option value="purchasing">Dalam Proses Pembelian / Pengiriman</option>
                                <option value="received">Unit Diterima & Masuk Inventaris</option>
                                <option value="rejected">Ditolak</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Total Biaya Riil / Realisasi (Rp)</label>
                            <input type="number" name="actual_total_price" x-model="statusForm.actual_total_price" min="0" step="1000" placeholder="Nominal riil pada nota/invoice" 
                                   class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-[11px] text-gray-400 mt-1">Isi jika pembelian sudah dilakukan.</p>
                        </div>

                        <div x-show="statusForm.status === 'received'">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Seri / Asset Tag Unit</label>
                            <input type="text" name="serial_number" x-model="statusForm.serial_number" placeholder="Contoh: SN-ZV-889102" 
                                   class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Upload Bukti Nota / Invoice / Resi</label>
                            <input type="file" name="receipt" accept="image/*,.pdf" 
                                   class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea name="admin_notes" x-model="statusForm.admin_notes" rows="2" placeholder="Catatan approval, nomor resi, atau kendala..." 
                                      class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button type="button" @click="showStatusModal = false" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL: Edit Data -->
            <div x-show="showEditModal" 
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div @click.away="showEditModal = false" class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl relative">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Edit Data Pengadaan</h3>
                            <p class="text-xs text-gray-500" x-text="editForm.procurement_code"></p>
                        </div>
                        <button type="button" @click="showEditModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form :action="'/admin/unit-procurements/' + editForm.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Perangkat / Unit <span class="text-rose-500">*</span></label>
                            <input type="text" name="item_name" x-model="editForm.item_name" required 
                                   class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                                <select name="category" x-model="editForm.category" required class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="camera">Kamera</option>
                                    <option value="lens">Lensa</option>
                                    <option value="smartphone">Smartphone</option>
                                    <option value="audio_mic">Audio / Mic</option>
                                    <option value="lighting_rig">Lighting & Rig</option>
                                    <option value="storage_pc">PC / Laptop / SSD</option>
                                    <option value="accessory">Aksesoris</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Prioritas / Urgensi <span class="text-rose-500">*</span></label>
                                <select name="priority" x-model="editForm.priority" required class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="medium">Sedang (Normal)</option>
                                    <option value="high">Tinggi</option>
                                    <option value="urgent">Mendesak (Urgent)</option>
                                    <option value="low">Rendah</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah (Qty) <span class="text-rose-500">*</span></label>
                                <input type="number" name="quantity" x-model="editForm.quantity" min="1" required 
                                       class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Estimasi Harga Satuan (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="estimated_unit_price" x-model="editForm.estimated_unit_price" min="0" step="1000" required 
                                       class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Supplier / Vendor</label>
                                <input type="text" name="vendor_name" x-model="editForm.vendor_name" 
                                       class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Link Pembelian (URL)</label>
                                <input type="url" name="purchase_url" x-model="editForm.purchase_url" 
                                       class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Kebutuhan / Catatan</label>
                            <textarea name="purpose_notes" x-model="editForm.purpose_notes" rows="2" 
                                      class="w-full text-sm rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function procurementManager() {
            return {
                showCreateModal: false,
                showStatusModal: false,
                showEditModal: false,
                activeItem: null,
                statusForm: {
                    status: 'pending_approval',
                    actual_total_price: '',
                    serial_number: '',
                    admin_notes: '',
                },
                editForm: {
                    id: '',
                    procurement_code: '',
                    item_name: '',
                    category: 'camera',
                    priority: 'medium',
                    quantity: 1,
                    estimated_unit_price: 0,
                    vendor_name: '',
                    purchase_url: '',
                    purpose_notes: '',
                },
                init() {
                    window.addEventListener('open-procurement-modal', () => {
                        this.showCreateModal = true;
                    });
                },
                openStatusModal(item) {
                    this.activeItem = item;
                    this.statusForm.status = item.status;
                    this.statusForm.actual_total_price = item.actual_total_price || '';
                    this.statusForm.serial_number = item.serial_number || '';
                    this.statusForm.admin_notes = item.admin_notes || '';
                    this.showStatusModal = true;
                },
                openEditModal(item) {
                    this.editForm.id = item.id;
                    this.editForm.procurement_code = item.procurement_code;
                    this.editForm.item_name = item.item_name;
                    this.editForm.category = item.category;
                    this.editForm.priority = item.priority;
                    this.editForm.quantity = item.quantity;
                    this.editForm.estimated_unit_price = item.estimated_unit_price;
                    this.editForm.vendor_name = item.vendor_name || '';
                    this.editForm.purchase_url = item.purchase_url || '';
                    this.editForm.purpose_notes = item.purpose_notes || '';
                    this.showEditModal = true;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
