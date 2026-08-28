<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
            {{ __('Data Tim Worker') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="bg-white rounded-2xl sm:rounded-[32px] p-4 sm:p-6 border border-gray-150 shadow-sm" x-data="{ showAddWorkerModal: {{ $errors->any() ? 'true' : 'false' }} }">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 mb-4">
                    <div>
                        <span class="block text-sm font-bold text-gray-900">Manajemen Anggota Tim</span>
                        <span class="text-xs text-gray-400">Daftar worker di bawah naungan Anda</span>
                    </div>
                    <button @click="showAddWorkerModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Anggota
                    </button>
                </div>

                <!-- Modal Tambah Worker -->
                <div x-show="showAddWorkerModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="showAddWorkerModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div x-show="showAddWorkerModal" @click.away="showAddWorkerModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                            <form action="{{ route('vendor.workers.store') }}" method="POST">
                                @csrf
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Daftarkan Worker Baru</h3>
                                            <div class="mt-4 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                                    <input type="text" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Username Email</label>
                                                    <div class="mt-1 flex rounded-md shadow-sm">
                                                        <input type="text" name="username" required class="flex-1 block w-full min-w-0 border-gray-300 rounded-none rounded-l-md sm:text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="namapekerja">
                                                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                                            @kamerakitaid.site
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                                                    <input type="text" name="whatsapp_number" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="08123456789">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Password</label>
                                                    <input type="password" name="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                                                    <input type="password" name="password_confirmation" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Daftarkan</button>
                                    <button type="button" @click="showAddWorkerModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="text-gray-500">
                                <th class="py-3 text-left font-semibold">ID Worker</th>
                                <th class="py-3 text-left font-semibold">Nama Worker</th>
                                <th class="py-3 text-left font-semibold">All Time</th>
                                <th class="py-3 text-left font-semibold">Paid</th>
                                <th class="py-3 text-left font-semibold">Pending</th>
                                <th class="py-3 text-left font-semibold">Estimasi Pending Gaji</th>
                                <th class="py-3 text-left font-semibold">WhatsApp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($metrics['workers_data'] as $data)
                                <tr>
                                    <td class="py-3.5 font-bold text-indigo-650">{{ $data['worker']->mitra_id }}</td>
                                    <td class="py-3.5 font-medium text-gray-900">{{ $data['worker']->full_name }}</td>
                                    <td class="py-3.5 text-gray-600">{{ $data['metrics']['all_time_hours_formatted'] }}</td>
                                    <td class="py-3.5 text-emerald-800">{{ $data['metrics']['paid_hours_formatted'] }}</td>
                                    <td class="py-3.5 text-amber-800 font-bold">{{ $data['metrics']['pending_hours_formatted'] }}</td>
                                    <td class="py-3.5 font-extrabold text-slate-800">Rp{{ number_format($data['metrics']['pending_earnings'], 0, ',', '.') }}</td>
                                    <td class="py-3.5 text-indigo-600 font-medium">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $data['worker']->whatsapp_number) }}" target="_blank" class="hover:underline">
                                            {{ $data['worker']->whatsapp_number }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-450 text-xs">Belum ada Worker terdaftar di bawah naungan Anda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
