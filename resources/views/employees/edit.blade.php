<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                Edit Mitra: {{ $employee->full_name }} ({{ $employee->mitra_id }})
            </h2>
            <a href="{{ route('partners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none transition duration-150 ease-in-out">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('partners.update', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Section 1: Profil & Demografi -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                    <div class="border-b border-gray-100 pb-4 mb-6">
                        <h3 class="text-lg font-bold text-gray-900">1. Data Profil & Demografi</h3>
                        <p class="text-xs text-gray-400">Pastikan informasi diisi sesuai KTP asli mitra.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mitra_id" class="block text-sm font-medium text-gray-700 mb-1">ID Mitra (Tidak Dapat Diubah)</label>
                            <input type="text" name="mitra_id" id="mitra_id" value="{{ $employee->mitra_id }}" readonly class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-400 font-semibold focus:outline-none">
                        </div>

                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap Sesuai KTP <span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $employee->full_name) }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">NIK (16 Digit) <span class="text-red-500">*</span></label>
                            <input type="text" name="nik" id="nik" value="{{ old('nik', $employee->nik) }}" maxlength="16" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('nik') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="birth_place_date" class="block text-sm font-medium text-gray-700 mb-1">Tempat, Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="text" name="birth_place_date" id="birth_place_date" value="{{ old('birth_place_date', $employee->birth_place_date) }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('birth_place_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select name="gender" id="gender" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number', $employee->whatsapp_number) }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('whatsapp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Aktif <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Mitra <span class="text-red-500">*</span></label>
                                <select name="status" id="status" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ old('status', $employee->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="joined_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bergabung <span class="text-red-500">*</span></label>
                                <input type="date" name="joined_date" id="joined_date" value="{{ old('joined_date', $employee->joined_date?->format('Y-m-d')) }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('joined_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="full_address" class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                            <textarea name="full_address" id="full_address" rows="3" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('full_address', $employee->full_address) }}</textarea>
                            @error('full_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Operasional & Perangkat -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                    <div class="border-b border-gray-100 pb-4 mb-6">
                        <h3 class="text-lg font-bold text-gray-900">2. Data Operasional & Perangkat</h3>
                        <p class="text-xs text-gray-400">Spesifikasi smartphone pendukung perekaman video.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="smartphone_type" class="block text-sm font-medium text-gray-700 mb-1">Merk & Tipe Smartphone <span class="text-red-500">*</span></label>
                            <input type="text" name="smartphone_type" id="smartphone_type" value="{{ old('smartphone_type', $employee->equipment->smartphone_type ?? '') }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('smartphone_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="headstrap_status" class="block text-sm font-medium text-gray-700 mb-1">Status Headstrap <span class="text-red-500">*</span></label>
                            <select name="headstrap_status" id="headstrap_status" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="not_owned" {{ old('headstrap_status', $employee->equipment->headstrap_status ?? '') == 'not_owned' ? 'selected' : '' }}>Not Owned</option>
                                <option value="reimburse_process" {{ old('headstrap_status', $employee->equipment->headstrap_status ?? '') == 'reimburse_process' ? 'selected' : '' }}>Reimburse Process</option>
                                <option value="reimbursed" {{ old('headstrap_status', $employee->equipment->headstrap_status ?? '') == 'reimbursed' ? 'selected' : '' }}>Reimbursed</option>
                            </select>
                            @error('headstrap_status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="nominal_reimburse" class="block text-sm font-medium text-gray-700 mb-1">Nominal Reimburse Headstrap (Rp)</label>
                            <input type="number" name="nominal_reimburse" id="nominal_reimburse" value="{{ old('nominal_reimburse', $employee->equipment->nominal_reimburse ?? 0) }}" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('nominal_reimburse') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="headstrap_receipt_path" class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Nota Headstrap (Kosongkan jika tidak diganti)</label>
                            <input type="file" name="headstrap_receipt_path" id="headstrap_receipt_path" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('headstrap_receipt_path') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            @if($employee->equipment && $employee->equipment->headstrap_receipt_path)
                                <div class="mt-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <a href="{{ asset('storage/' . $employee->equipment->headstrap_receipt_path) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Lihat Nota Saat Ini</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Section 3: Keuangan & Pembayaran -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                    <div class="border-b border-gray-100 pb-4 mb-6">
                        <h3 class="text-lg font-bold text-gray-900">3. Data Keuangan & Pembayaran</h3>
                        <p class="text-xs text-gray-400">Detail bank atau e-wallet untuk keperluan transfer slip gaji bulanan.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Bank atau E-Wallet <span class="text-red-500">*</span></label>
                            <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $employee->finance->bank_name ?? '') }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening / E-Wallet ID <span class="text-red-500">*</span></label>
                            <input type="text" name="account_number" id="account_number" value="{{ old('account_number', $employee->finance->account_number ?? '') }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('account_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="account_owner_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Pemilik Rekening (Harus Sesuai Buku Rekening) <span class="text-red-500">*</span></label>
                            <input type="text" name="account_owner_name" id="account_owner_name" value="{{ old('account_owner_name', $employee->finance->account_owner_name ?? '') }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('account_owner_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="base_hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">Rate Pendapatan per Jam Approved (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="base_hourly_rate" id="base_hourly_rate" value="{{ old('base_hourly_rate', $employee->finance->base_hourly_rate ?? 54000) }}" required class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('base_hourly_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('partners.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        Batalkan
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-300 shadow-md shadow-orange-100">
                        Perbarui Data Mitra
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
