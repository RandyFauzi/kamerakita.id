<x-app-layout>
    <div class="py-12" x-data="invoiceApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Client Invoices</h2>
                <div class="flex space-x-2">
                    <button @click="activeTab = 'generator'" :class="{ 'bg-indigo-600 text-white': activeTab === 'generator', 'bg-white text-gray-700 hover:bg-gray-50': activeTab !== 'generator' }" class="px-5 py-2.5 rounded-full text-sm font-medium transition-colors shadow-sm border border-gray-200">
                        Buat Invoice Baru
                    </button>
                    <button @click="activeTab = 'history'" :class="{ 'bg-indigo-600 text-white': activeTab === 'history', 'bg-white text-gray-700 hover:bg-gray-50': activeTab !== 'history' }" class="px-5 py-2.5 rounded-full text-sm font-medium transition-colors shadow-sm border border-gray-200">
                        Riwayat Invoice
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tab 1: Generator -->
            <div x-show="activeTab === 'generator'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Form Section -->
                    <div class="w-full lg:w-1/3">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-gray-150">
                            <div class="p-8">
                                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Invoice Data
                                </h3>
                                <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
                                    @csrf
                                    
                                    <div class="space-y-5">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Klien (Template)</label>
                                            <select name="client_id" x-model="selectedClient" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                <option value="">Pilih Client...</option>
                                                @foreach($clients as $c)
                                                    <option value="{{ $c->id }}" data-rate="{{ $c->default_rate }}" data-currency="{{ $c->default_currency }}" data-name="{{ $c->name }}" data-address="{{ $c->address }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div x-show="selectedClient" x-cloak class="space-y-4 p-4 bg-gray-50 border border-gray-200 rounded-xl mt-2">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Klien (Di Invoice)</label>
                                                <input type="text" name="client_name" x-model="clientName" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Penagihan</label>
                                                <textarea name="client_address" x-model="clientAddress" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Period Start</label>
                                                <input type="date" name="period_start" x-model="periodStart" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Period End</label>
                                                <input type="date" name="period_end" x-model="periodEnd" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Invoice Date</label>
                                            <input type="date" name="invoice_date" x-model="invoiceDate" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Billable Hours</label>
                                                <input type="number" step="0.01" name="billable_hours" x-model="billableHours" required class="mt-1 block w-full rounded-xl border-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-bold text-indigo-700 text-lg">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Rate (<span x-text="currency"></span>/hr)</label>
                                                <input type="number" step="0.01" name="unit_rate" x-model="rate" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-8">
                                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            Save as Draft
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Live Preview Section -->
                    <div class="w-full lg:w-2/3">
                        <div class="bg-gray-100 p-8 rounded-3xl h-full flex items-center justify-center border border-gray-200 shadow-inner">
                            <div class="bg-white w-full max-w-2xl min-h-[800px] shadow-2xl relative overflow-hidden" style="padding: 3rem;">
                                <!-- Simple modern invoice design -->
                                <div class="flex flex-col h-full">
                                    <div class="flex justify-between items-start border-b pb-8 mb-8">
                                        <div>
                                            <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight">INVOICE</h1>
                                            <p class="text-slate-500 mt-1">DRAFT PREVIEW</p>
                                        </div>
                                        <div class="text-right flex flex-col items-end">
                                            <img src="{{ asset('images/Logo.webp') }}" alt="KameraKita" class="h-10 w-auto mb-3">
                                            <h2 class="text-xl font-bold text-slate-800">KameraKita</h2>
                                            <p class="text-slate-500 text-xs mt-1">Jakarta, Indonesia</p>
                                            <p class="text-slate-500 text-xs">contact@kamerakita.id</p>
                                        </div>
                                    </div>

                                    <div class="flex justify-between mb-10">
                                        <div class="mb-12 text-sm text-slate-800">
                                            <p class="font-bold mb-1 text-slate-500 uppercase tracking-wider text-xs">Bill To:</p>
                                            <p class="font-bold text-base text-indigo-700" x-text="clientName || 'Select Client'"></p>
                                            <p class="text-slate-600 whitespace-pre-line" x-text="clientAddress"></p>
                                            <p x-show="periodStart && periodEnd" class="text-gray-500 mt-2">Billing Period: <br><span x-text="periodStart"></span> to <span x-text="periodEnd"></span></p>
                                        </div>
                                        <div class="text-right text-sm">
                                            <p class="font-bold text-slate-500 uppercase tracking-wider text-xs mb-1">Date:</p>
                                            <p x-text="invoiceDate || 'YYYY-MM-DD'"></p>
                                        </div>
                                    </div>

                                    <div class="mb-8">
                                        <table class="w-full text-sm text-left border-collapse">
                                            <thead class="bg-indigo-50 text-indigo-900 border-b-2 border-indigo-200">
                                                <tr>
                                                    <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs">Description</th>
                                                    <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-center">Qty (Hours)</th>
                                                    <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-right">Rate</th>
                                                    <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-right">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-b border-slate-200">
                                                <tr>
                                                    <td class="py-4 px-4 text-slate-800">
                                                        <span class="font-semibold">Video Data Collection</span>
                                                    </td>
                                                    <td class="py-4 px-4 text-center text-slate-800 font-medium" x-text="billableHours ? parseFloat(billableHours).toFixed(2) : '-'"></td>
                                                    <td class="py-4 px-4 text-right text-slate-800" x-text="rate ? currency + ' ' + parseFloat(rate).toFixed(2) : '-'"></td>
                                                    <td class="py-4 px-4 text-right text-slate-800 font-bold" x-text="totalAmount ? currency + ' ' + parseFloat(totalAmount).toFixed(2) : '-'"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="flex justify-end mt-4">
                                        <div class="w-1/2">
                                            <div class="flex justify-between py-3 border-t-2 border-slate-800 font-bold text-lg">
                                                <span>TOTAL DUE</span>
                                                <span class="text-indigo-700" x-text="totalAmount ? currency + ' ' + parseFloat(totalAmount).toFixed(2) : '-'"></span>
                                            </div>
                                        </div>
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
                    <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <form action="{{ route('invoices.index') }}" method="GET" class="flex space-x-2 w-full max-w-lg">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client or INV-..." class="rounded-lg border-gray-300 text-sm flex-1">
                            <select name="status" class="rounded-lg border-gray-300 text-sm">
                                <option value="">All Status</option>
                                <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                                <option value="ISSUED" {{ request('status') == 'ISSUED' ? 'selected' : '' }}>ISSUED</option>
                                <option value="SENT" {{ request('status') == 'SENT' ? 'selected' : '' }}>SENT</option>
                                <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>PAID</option>
                                <option value="VOID" {{ request('status') == 'VOID' ? 'selected' : '' }}>VOID</option>
                            </select>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date / Period</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Billable</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($invoices as $inv)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inv->invoice_no }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $inv->client_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $inv->invoice_date->format('d M Y') }}<br>
                                            <span class="text-xs text-gray-400">P: {{ $inv->period_start ? $inv->period_start->format('d M') . ' - ' . $inv->period_end->format('d M') : '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($inv->billable_hours, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">{{ $inv->currency }} {{ number_format($inv->total_amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $inv->status == 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $inv->status == 'ISSUED' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $inv->status == 'SENT' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $inv->status == 'PAID' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $inv->status == 'VOID' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ $inv->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium space-x-2">
                                            <a href="{{ route('invoices.show', $inv->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">No invoices found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="px-6 py-3 border-t border-gray-200">
                            {{ $invoices->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function invoiceApp() {
            return {
                activeTab: '{{ request()->has("page") || request()->has("search") || request()->has("status") ? "history" : "generator" }}',
                selectedClient: '',
                clientName: '',
                clientAddress: '',
                rate: 3.5,
                currency: 'USD',
                periodStart: '',
                periodEnd: '',
                invoiceDate: '{{ date("Y-m-d") }}',
                billableHours: 0,

                get totalAmount() {
                    let b = parseFloat(this.billableHours) || 0;
                    let r = parseFloat(this.rate) || 0;
                    return b * r;
                },

                init() {
                    this.$watch('selectedClient', value => {
                        if (!value) {
                            this.clientName = '';
                            this.clientAddress = '';
                            this.rate = 3.5;
                            return;
                        }
                        let select = document.querySelector('select[name="client_id"]');
                        let option = select.options[select.selectedIndex];
                        this.clientName = option.getAttribute('data-name');
                        this.clientAddress = option.getAttribute('data-address') || '';
                        this.rate = option.getAttribute('data-rate');
                        this.currency = option.getAttribute('data-currency');
                    });
                }
            }
        }
    </script>
</x-app-layout>
