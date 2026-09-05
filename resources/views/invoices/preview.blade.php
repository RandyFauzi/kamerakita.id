<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-150">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('invoices.index', ['page' => 1]) }}" class="text-gray-500 hover:text-indigo-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Invoice {{ $invoice->invoice_no }}</h2>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $invoice->status == 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $invoice->status == 'ISSUED' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $invoice->status == 'SENT' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $invoice->status == 'PAID' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $invoice->status == 'VOID' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $invoice->status }}
                        </span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="window.print()" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Print PDF
                    </button>
                    
                    @if($invoice->status === 'DRAFT')
                        <form action="{{ route('invoices.issue', $invoice->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Issue Invoice</button>
                        </form>
                        <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus DRAFT ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Delete</button>
                        </form>
                    @endif

                    @if($invoice->status === 'ISSUED')
                        <form action="{{ route('invoices.send', $invoice->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-yellow-500 text-white hover:bg-yellow-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Mark as Sent</button>
                        </form>
                    @endif

                    @if(in_array($invoice->status, ['ISSUED', 'SENT']))
                        <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white hover:bg-green-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Record Payment</button>
                        </form>
                    @endif

                    @if(!in_array($invoice->status, ['DRAFT', 'VOID']))
                        <div x-data="{ openVoid: false }" class="inline-block relative">
                            <button @click="openVoid = true" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Void</button>
                            <div x-show="openVoid" x-cloak class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-xl border p-4 z-50">
                                <form action="{{ route('invoices.void', $invoice->id) }}" method="POST">
                                    @csrf
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Void</label>
                                    <input type="text" name="reason" required class="w-full border-gray-300 rounded-md shadow-sm mb-3 text-sm">
                                    <div class="flex justify-end space-x-2">
                                        <button type="button" @click="openVoid = false" class="px-3 py-1 bg-gray-100 rounded text-sm">Cancel</button>
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-sm">Confirm Void</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($invoice->status === 'VOID')
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <p class="text-red-700 font-bold text-sm">This invoice is VOID.</p>
                    <p class="text-red-600 text-sm mt-1">Reason: {{ $invoice->void_reason }}</p>
                    <p class="text-red-500 text-xs mt-1">Voided by {{ $invoice->voided_by }} at {{ $invoice->voided_at->format('d M Y H:i') }}</p>
                </div>
            @endif

            <!-- Invoice Document -->
            <div id="printable-invoice" class="bg-white shadow-2xl relative overflow-hidden print-friendly" style="padding: 4rem; min-height: 1056px;">
                <div class="flex flex-col h-full">
                    <!-- Header -->
                    <div class="flex justify-between items-start border-b-2 border-slate-200 pb-8 mb-10">
                        <div>
                            <h1 class="text-5xl font-extrabold text-slate-800 tracking-tight mb-2">INVOICE</h1>
                            <p class="text-lg text-slate-500 font-medium">#{{ $invoice->invoice_no }}</p>
                        </div>
                        <div class="text-right">
                            <div class="w-16 h-16 bg-indigo-600 rounded-xl flex items-center justify-center ml-auto mb-4 shadow-lg">
                                <span class="text-white font-bold text-2xl">KK</span>
                            </div>
                            <h2 class="text-2xl font-bold text-slate-800">KameraKita</h2>
                            <p class="text-slate-500 mt-1">Jakarta, Indonesia</p>
                            <p class="text-slate-500">contact@kamerakita.id</p>
                        </div>
                    </div>

                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-12 mb-12">
                        <div>
                            <p class="font-bold mb-3 text-slate-400 uppercase tracking-wider text-xs">Bill To:</p>
                            <h3 class="font-bold text-xl text-slate-800 mb-1">{{ $invoice->client_name }}</h3>
                            <p class="text-slate-600">{{ $invoice->client_address }}</p>
                            <p class="text-slate-600 mt-1">{{ $invoice->client_email }}</p>
                            @if($invoice->client_tax_id)
                                <p class="text-slate-500 mt-2 text-sm">Tax ID: {{ $invoice->client_tax_id }}</p>
                            @endif
                        </div>
                        <div class="text-right flex flex-col justify-between">
                            <div>
                                <p class="font-bold text-slate-400 uppercase tracking-wider text-xs mb-1">Invoice Date:</p>
                                <p class="text-lg font-medium text-slate-800 mb-4">{{ $invoice->invoice_date->format('F d, Y') }}</p>
                            </div>
                            @if($invoice->period_start && $invoice->period_end)
                            <div>
                                <p class="font-bold text-slate-400 uppercase tracking-wider text-xs mb-1">Billing Period:</p>
                                <p class="text-md text-slate-700">{{ $invoice->period_start->format('M d') }} - {{ $invoice->period_end->format('M d, Y') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-12 flex-grow">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-y-2 border-slate-200">
                                    <th class="py-4 px-6 font-bold uppercase tracking-wider text-xs text-slate-500 w-1/2">Description</th>
                                    <th class="py-4 px-6 font-bold uppercase tracking-wider text-xs text-slate-500 text-center">Qty</th>
                                    <th class="py-4 px-6 font-bold uppercase tracking-wider text-xs text-slate-500 text-right">Rate</th>
                                    <th class="py-4 px-6 font-bold uppercase tracking-wider text-xs text-slate-500 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td class="py-5 px-6">
                                        <p class="font-bold text-slate-800">{{ $item->description }}</p>
                                        @if($invoice->adjustment_hours != 0)
                                            <p class="text-xs text-slate-500 mt-1">
                                                Source: {{ number_format($invoice->source_approved_hours, 2) }} {{ $item->unit }} 
                                                (Adj: {{ number_format($invoice->adjustment_hours, 2) }} - {{ $invoice->adjustment_reason }})
                                            </p>
                                        @endif
                                    </td>
                                    <td class="py-5 px-6 text-center text-slate-700">{{ number_format($item->quantity, 2) }} <span class="text-xs text-slate-400">{{ $item->unit }}</span></td>
                                    <td class="py-5 px-6 text-right text-slate-700">{{ $invoice->currency }} {{ number_format($item->unit_rate, 2) }}</td>
                                    <td class="py-5 px-6 text-right font-bold text-slate-800">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Totals -->
                    <div class="flex justify-end mb-16">
                        <div class="w-1/2 lg:w-1/3">
                            <div class="flex justify-between py-4 border-t-2 border-slate-800">
                                <span class="font-bold text-xl text-slate-800">TOTAL DUE</span>
                                <span class="font-black text-2xl text-indigo-600">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-auto pt-8 border-t border-slate-200 text-center">
                        <p class="text-slate-500 font-medium mb-1">Thank you for your business!</p>
                        <p class="text-slate-400 text-sm">Please make payments within 15 days of receiving this invoice.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <style>
        @media print {
            body { background-color: white !important; }
            nav, header, .flex.space-x-2, .mb-6.flex { display: none !important; }
            .py-12 { padding: 0 !important; }
            #printable-invoice { box-shadow: none !important; border: none !important; padding: 2rem !important; }
        }
    </style>
</x-app-layout>
