<x-app-layout>
    <div class="min-h-screen bg-slate-50 py-8 font-sans" x-data="{
        activeTab: 'generator', // 'generator' or 'history'
        invoiceNo: '',
        invoiceDate: '',
        totalWorkers: '',
        totalHours: '',
        get totalAmount() {
            return this.totalHours ? (parseFloat(this.totalHours) * 3.5).toFixed(2) : '';
        },
        defaultInvoiceNo: 'INV-{{ date('Ymd') }}-XXX',
        get formattedDate() {
            if (this.invoiceDate) {
                const parts = this.invoiceDate.split('-');
                if(parts.length === 3) {
                    return new Date(parts[0], parts[1]-1, parts[2]).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                }
            }
            return new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Invoice</h1>
                    <p class="text-sm text-slate-500 mt-1">Buat ringkasan tagihan dan kelola data invoice.</p>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'generator'" 
                            :class="activeTab === 'generator' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Pembuat Invoice
                    </button>
                    <button @click="activeTab = 'history'" 
                            :class="activeTab === 'history' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Data Invoice
                    </button>
                </nav>
            </div>

            <!-- Flash Message -->
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Tab 1: Generator -->
            <div x-show="activeTab === 'generator'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    <!-- Left Column: Form -->
                    <div class="w-full lg:w-1/3 shrink-0">
                        <form action="{{ route('invoices.store') }}" method="POST" target="_blank" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/60 sticky top-8">
                            @csrf
                            
                            <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Detail Tagihan
                            </h2>

                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Invoice No (Opsional)</label>
                                    <input type="text" name="invoice_no" x-model="invoiceNo" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors" placeholder="Otomatis jika kosong">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Invoice Date (Opsional)</label>
                                    <input type="date" name="invoice_date" x-model="invoiceDate" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                                </div>

                                <div class="pt-4 border-t border-slate-100"></div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Total Workers <span class="text-red-500">*</span></label>
                                    <input type="number" name="total_workers" x-model="totalWorkers" required class="block w-full rounded-xl border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors" placeholder="Misal: 9">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Total Approved Hours <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="number" step="0.01" name="total_approved_hours" x-model="totalHours" required class="block w-full rounded-xl border-slate-200 pl-4 pr-12 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors" placeholder="0.00">
                                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                            <span class="text-slate-400 sm:text-sm font-medium">hrs</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Total Amount <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-slate-400 sm:text-sm font-bold">$</span>
                                        </div>
                                        <input type="number" step="0.01" name="total_amount" :value="totalAmount" readonly class="block w-full rounded-xl border-slate-200 pl-8 pr-12 text-sm bg-slate-100 text-slate-500 cursor-not-allowed focus:ring-0 focus:border-slate-200 transition-colors" placeholder="0.00">
                                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                            <span class="text-slate-400 sm:text-sm font-medium">USD</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8">
                                <button type="submit" @click="setTimeout(() => window.location.reload(), 1500)" class="w-full flex items-center justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-indigo-500/30">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Simpan & Buka PDF
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Live Preview -->
                    <div class="w-full lg:w-2/3">
                        <div class="bg-slate-200/50 p-4 sm:p-8 rounded-3xl border border-slate-200 flex justify-center overflow-x-auto">
                            <!-- PDF Paper Simulation -->
                            <div class="bg-white w-full max-w-[800px] shadow-lg shrink-0 relative overflow-hidden" style="aspect-ratio: 1/1.2; min-height: 800px;">
                                <!-- Invoice Content (Mirrors preview.blade.php visually) -->
                                <div class="p-8 sm:p-12 h-full flex flex-col relative text-slate-800">
                                    <div class="flex justify-between items-end pb-4 border-b-2 border-blue-600 mb-8">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ asset('vendor-assets/kamerakita/logo-mark.svg') }}" alt="Logo" class="h-10">
                                            <h1 class="text-3xl font-bold text-blue-600">KameraKita AI</h1>
                                        </div>
                                        <div>
                                            <h2 class="text-2xl font-bold text-slate-700 uppercase tracking-wide">Invoice Summary</h2>
                                        </div>
                                    </div>

                                    <div class="flex justify-end mb-12">
                                        <table class="text-sm text-slate-700">
                                            <tr>
                                                <td class="font-bold pr-4 py-1">Invoice No:</td>
                                                <td x-text="invoiceNo || defaultInvoiceNo"></td>
                                            </tr>
                                            <tr>
                                                <td class="font-bold pr-4 py-1">Date:</td>
                                                <td x-text="formattedDate"></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="mb-12 text-sm text-slate-800">
                                        <p class="font-bold mb-1 text-slate-500 uppercase tracking-wider text-xs">Billed To:</p>
                                        <p class="font-bold text-base">MyTron Labs Inc.</p>
                                        <p>8 The Green, STE A, Dover City,</p>
                                        <p>Kent County, DE 19901</p>
                                    </div>

                                    <div class="mb-8">
                                        <table class="w-full text-sm text-left border-collapse">
                                            <thead class="bg-blue-600 text-white">
                                                <tr>
                                                    <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs">Description</th>
                                                    <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-center">Total Workers</th>
                                                    <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-center">Total Approved Hours</th>
                                                    <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-center">Total Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-b border-slate-200">
                                                <tr>
                                                    <td class="py-4 px-4 text-slate-800">Kamera Kita Workers</td>
                                                    <td class="py-4 px-4 text-center text-slate-800 font-medium" x-text="totalWorkers || '-'"></td>
                                                    <td class="py-4 px-4 text-center text-slate-800 font-medium" x-text="totalHours ? parseFloat(totalHours).toFixed(2) : '-'"></td>
                                                    <td class="py-4 px-4 text-center text-slate-800 font-bold" x-text="totalAmount ? parseFloat(totalAmount).toFixed(2) : '-'"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-xs italic text-slate-500 mt-auto mb-16">
                                        * This summary represents the total workload without individual email details. The applied rate is 3.50 per billable hour.
                                    </div>

                                    <div class="absolute bottom-8 left-12 right-12 border-t border-slate-200 pt-4 text-center">
                                        <p class="text-sm text-slate-500 mb-1">Thank you for your business!</p>
                                        <p class="text-sm font-bold text-slate-400">KameraKita AI</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: History (CRUD) -->
            <div x-show="activeTab === 'history'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-150">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice No</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Workers</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($invoices ?? [] as $inv)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inv->invoice_no }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ date('d M Y', strtotime($inv->invoice_date)) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $inv->total_workers }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ number_format($inv->total_approved_hours, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">${{ number_format($inv->total_amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-3">
                                                <a href="{{ route('invoices.show', $inv->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Lihat PDF</a>
                                                <form action="{{ route('invoices.destroy', $inv->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus invoice ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                                Belum ada invoice yang dibuat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
