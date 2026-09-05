<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Invoice Summary') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('invoices.preview') }}" method="POST" target="_blank">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Optional overrides -->
                            <div>
                                <label for="invoice_no" class="block text-sm font-medium text-gray-700">Invoice No (Opsional)</label>
                                <input type="text" name="invoice_no" id="invoice_no" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Otomatis jika dikosongkan">
                            </div>
                            <div>
                                <label for="invoice_date" class="block text-sm font-medium text-gray-700">Invoice Date (Opsional)</label>
                                <input type="date" name="invoice_date" id="invoice_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <hr class="mb-6">

                        <div class="space-y-6">
                            <div>
                                <label for="total_workers" class="block text-sm font-medium text-gray-700">Total Workers <span class="text-red-500">*</span></label>
                                <input type="number" name="total_workers" id="total_workers" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: 9">
                            </div>

                            <div>
                                <label for="total_approved_hours" class="block text-sm font-medium text-gray-700">Total Approved Hours <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" name="total_approved_hours" id="total_approved_hours" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: 36.98">
                            </div>

                            <div>
                                <label for="total_amount" class="block text-sm font-medium text-gray-700">Total Amount (USD) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" name="total_amount" id="total_amount" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: 147.92">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Generate Invoice Preview
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
