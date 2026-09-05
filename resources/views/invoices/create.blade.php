<x-app-layout>
    <div class="min-h-screen bg-slate-50 py-8 font-sans" x-data="{
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
            
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Invoice Generator</h1>
                    <p class="text-sm text-slate-500 mt-1">Buat ringkasan tagihan (invoice) dengan mudah dan cepat.</p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <!-- Left Column: Form -->
                <div class="w-full lg:w-1/3 shrink-0">
                    <form action="{{ route('invoices.preview') }}" method="POST" target="_blank" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/60 sticky top-8">
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
                            <button type="submit" class="w-full flex items-center justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-indigo-500/30">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Cetak / Simpan PDF
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
                                <!-- Header -->
                                <div class="flex justify-between items-end pb-4 border-b-2 border-blue-600 mb-8">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ asset('vendor-assets/kamerakita/logo-mark.svg') }}" alt="Logo" class="h-10">
                                        <h1 class="text-3xl font-bold text-blue-600">KameraKita AI</h1>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-slate-700 uppercase tracking-wide">Invoice Summary</h2>
                                    </div>
                                </div>

                                <!-- Meta info -->
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

                                <!-- Billed To -->
                                <div class="mb-12 text-sm text-slate-800">
                                    <p class="font-bold mb-1 text-slate-500 uppercase tracking-wider text-xs">Billed To:</p>
                                    <p class="font-bold text-base">MyTron Labs Inc.</p>
                                    <p>8 The Green, STE A, Dover City,</p>
                                    <p>Kent County, DE 19901</p>
                                </div>

                                <!-- Table -->
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

                                <!-- Disclaimer -->
                                <div class="text-xs italic text-slate-500 mt-auto mb-16">
                                    * This summary represents the total workload without individual email details. The applied rate is 3.50 per billable hour.
                                </div>

                                <!-- Footer -->
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
    </div>
</x-app-layout>
