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
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 pb-4 border-b border-gray-100 mb-4">
                    <div>
                        <span class="block text-sm font-bold text-gray-900">Manajemen Anggota Tim</span>
                        <span class="text-xs text-gray-400">Daftar worker di bawah naungan Anda</span>
                    </div>
                    <button @click="showAddWorkerModal = true" class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Anggota Manual
                    </button>
                </div>

                <!-- Referral Info Box -->
                <div class="mb-6 bg-gradient-to-br from-indigo-50/80 to-blue-50/80 border border-indigo-100 rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-indigo-900 text-sm">Undang Worker secara Mandiri</h4>
                        <p class="text-xs text-indigo-700 mt-1">Bagikan link ini agar worker bisa mendaftar sendiri dan <b>langsung masuk</b> ke tim Anda.</p>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="text" readonly value="{{ route('register', ['ref' => $partner->referral_code]) }}" id="refLink" class="text-xs border-indigo-200 bg-white shadow-sm rounded-lg px-3 py-2 w-full sm:w-64 text-gray-600 focus:ring-0 focus:border-indigo-200" title="Link Pendaftaran Worker">
                        <button type="button" onclick="
                            const copyText = document.getElementById('refLink');
                            copyText.select();
                            copyText.setSelectionRange(0, 99999);
                            navigator.clipboard.writeText(copyText.value);
                            const btn = this;
                            const originalText = btn.innerText;
                            btn.innerText = 'Tersalin!';
                            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                            setTimeout(() => { 
                                btn.innerText = originalText; 
                                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                                btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                            }, 2000);
                        " class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-lg transition-colors shadow-sm whitespace-nowrap">
                            Salin Link
                        </button>
                    </div>
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

                <div class="overflow-x-auto pb-4">
                    <table class="w-full text-sm min-w-[700px]">
                        <thead>
                            <tr class="text-gray-500">
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">ID Worker</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">Nama Worker</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">All Time</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">Paid</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">Pending</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">Estimasi Pending Gaji</th>
                                <th class="py-3 text-left font-semibold whitespace-nowrap px-2">WhatsApp</th>
                                <th class="py-3 text-center font-semibold whitespace-nowrap px-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($metrics['workers_data'] as $data)
                                <tr x-data="{ showEditModal: false }">
                                    <td class="py-3.5 px-2 font-bold text-indigo-650 whitespace-nowrap">{{ $data['worker']->mitra_id }}</td>
                                    <td class="py-3.5 px-2 font-medium text-gray-900 whitespace-nowrap">{{ $data['worker']->full_name }}</td>
                                    <td class="py-3.5 px-2 text-gray-600 whitespace-nowrap">{{ $data['metrics']['all_time_hours_formatted'] }}</td>
                                    <td class="py-3.5 px-2 text-emerald-800 whitespace-nowrap">{{ $data['metrics']['paid_hours_formatted'] }}</td>
                                    <td class="py-3.5 px-2 text-amber-800 font-bold whitespace-nowrap">{{ $data['metrics']['pending_hours_formatted'] }}</td>
                                    <td class="py-3.5 px-2 font-extrabold text-slate-800 whitespace-nowrap">Rp{{ number_format($data['metrics']['pending_earnings'], 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-2 text-indigo-600 font-medium whitespace-nowrap">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $data['worker']->whatsapp_number) }}" target="_blank" class="hover:underline">
                                            {{ $data['worker']->whatsapp_number }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-2 text-center whitespace-nowrap">
                                        <button @click="showEditModal = true" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</button>

                                        <!-- Edit Modal -->
                                        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                <div x-show="showEditModal" @click="showEditModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                                <div x-show="showEditModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                    <form action="{{ route('vendor.workers.update', $data['worker']->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                            <div class="sm:flex sm:items-start">
                                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Edit Worker: {{ $data['worker']->full_name }}</h3>
                                                                    <div class="mt-4 space-y-4">
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                                                            <input type="text" name="name" value="{{ $data['worker']->full_name }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                                                                            <input type="text" name="whatsapp_number" value="{{ $data['worker']->whatsapp_number }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                                        </div>
                                                                        
                                                                        <hr class="my-4 border-gray-200">
                                                                        <h4 class="text-sm font-bold text-gray-900 mb-2">Informasi Pembayaran (Opsional)</h4>
                                                                        
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700">Nama Bank / e-Wallet</label>
                                                                            <input type="text" name="bank_name" value="{{ $data['worker']->bank_name }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Contoh: BCA, Mandiri, DANA, dll">
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700">Nomor Rekening</label>
                                                                            <input type="text" name="bank_account_number" value="{{ $data['worker']->bank_account_number }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700">Atas Nama Rekening</label>
                                                                            <input type="text" name="bank_account_owner" value="{{ $data['worker']->bank_account_owner }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                                        </div>

                                                                        <hr class="my-4 border-gray-200">
                                                                        
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700">Password Baru (Opsional)</label>
                                                                            <input type="password" name="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                                                                            <input type="password" name="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                                Simpan Perubahan
                                                            </button>
                                                            <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                                Batal
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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
