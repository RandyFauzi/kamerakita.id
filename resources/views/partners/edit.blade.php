<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                Edit Anggota: {{ $partner->full_name }} ({{ $partner->mitra_id }})
            </h2>
            <a href="{{ route('partners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-150 border border-gray-200 rounded-xl font-semibold text-xs text-gray-700 uppercase hover:bg-gray-200 transition">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ role: '{{ $partner->partner_role }}' }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('partners.update', $partner) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- BAGIAN 1: Informasi Identitas & Kontak -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-[32px] border border-gray-150 p-8 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <span class="block text-xs font-black tracking-widest text-indigo-650 uppercase font-mono mb-1">BAGIAN 1</span>
                        <h3 class="text-lg font-bold text-gray-900">Informasi Identitas & Kontak</h3>
                        <p class="text-xs text-gray-400">Data profil demografi utama perekam data.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Peran -->
                        <div>
                            <label for="partner_role" class="block text-sm font-semibold text-gray-700 mb-1">Peran Kemitraan <span class="text-red-500">*</span></label>
                            <select name="partner_role" id="partner_role" x-model="role" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="worker" {{ $partner->partner_role === 'worker' ? 'selected' : '' }}>Worker (Perekam Video)</option>
                                <option value="mitra" {{ $partner->partner_role === 'mitra' ? 'selected' : '' }}>Mitra (Koordinator)</option>
                            </select>
                            @error('partner_role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- ID Mitra -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">ID Mitra (Tidak dapat diubah)</label>
                            <input type="text" value="{{ $partner->mitra_id }}" readonly class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-400 font-semibold focus:outline-none">
                        </div>

                        <!-- NIK -->
                        <div>
                            <label for="nik" class="block text-sm font-semibold text-gray-700 mb-1">NIK (Nomor Induk Kependudukan)</label>
                            <input type="text" name="nik" id="nik" value="{{ old('nik', $partner->nik) }}" placeholder="Contoh: 327301XXXXXXXXXX" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('nik') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $partner->full_name) }}" required placeholder="Sesuai KTP" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- WhatsApp -->
                        <div>
                            <label for="whatsapp_number" class="block text-sm font-semibold text-gray-700 mb-1">No. WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number', $partner->whatsapp_number) }}" required placeholder="Contoh: 6281234567890" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('whatsapp_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email Login -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email Login <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $partner->email ?? $partner->user?->email) }}" required placeholder="Contoh: worker@kamerakita.id" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">
                                {{ $partner->user_id ? 'Password Baru' : 'Password Login' }}
                                @unless($partner->user_id)<span class="text-red-500">*</span>@endunless
                            </label>
                            <input type="password" name="password" id="password" {{ $partner->user_id ? '' : 'required' }} class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">{{ $partner->user_id ? 'Kosongkan jika tidak ingin mengganti password.' : 'Wajib diisi karena data ini belum punya akun login.' }}</p>
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">
                                Konfirmasi Password
                                @unless($partner->user_id)<span class="text-red-500">*</span>@endunless
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" {{ $partner->user_id ? '' : 'required' }} class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Alamat Lengkap -->
                    <div>
                        <label for="full_address" class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap</label>
                        <textarea name="full_address" id="full_address" rows="3" placeholder="Jalan, RT/RW, Kecamatan, Kota, Kode Pos" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('full_address', $partner->full_address) }}</textarea>
                        @error('full_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- BAGIAN 2: Informasi Finansial -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-[32px] border border-gray-150 p-8 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <span class="block text-xs font-black tracking-widest text-indigo-650 uppercase font-mono mb-1">BAGIAN 2</span>
                        <h3 class="text-lg font-bold text-gray-900">Informasi Finansial</h3>
                        <p class="text-xs text-gray-400">Akun bank resmi mitra untuk penyaluran dana payroll bulanan.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Nama Bank -->
                        <div>
                            <label for="bank_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $partner->bank_name) }}" placeholder="Contoh: BCA, Mandiri, BRI" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Nomor Rekening -->
                        <div>
                            <label for="bank_account_number" class="block text-sm font-semibold text-gray-700 mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $partner->bank_account_number ?? $partner->account_number) }}" placeholder="Contoh: 7012345678" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('bank_account_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Nama Pemilik Rekening -->
                        <div>
                            <label for="bank_account_owner" class="block text-sm font-semibold text-gray-700 mb-1">Nama Pemilik Rekening</label>
                            <input type="text" name="bank_account_owner" id="bank_account_owner" value="{{ old('bank_account_owner', $partner->bank_account_owner ?? $partner->account_owner_name) }}" placeholder="Contoh: Randy Fauzi" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('bank_account_owner') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: Operasional & Hierarki -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-[32px] border border-gray-150 p-8 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <span class="block text-xs font-black tracking-widest text-indigo-650 uppercase font-mono mb-1">BAGIAN 3</span>
                        <h3 class="text-lg font-bold text-gray-900">Operasional & Hierarki</h3>
                        <p class="text-xs text-gray-400">Atribut kerja lapangan, relasi Mitra atasan, dan rate penggajian.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pilih Mitra Atasan (Shown only if role is worker) -->
                        <div x-show="role === 'worker'" class="animate-in fade-in duration-200">
                            <label for="mitra_parent_id" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Mitra Atasan (Koordinator)</label>
                            <select name="mitra_parent_id" id="mitra_parent_id" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih Mitra...</option>
                                @foreach($mitraList as $mitra)
                                    <option value="{{ $mitra->id }}" {{ old('mitra_parent_id', $partner->mitra_parent_id) == $mitra->id ? 'selected' : '' }}>
                                        {{ $mitra->full_name }} ({{ $mitra->mitra_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('mitra_parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tipe Smartphone -->
                        <div>
                            <label for="smartphone_type" class="block text-sm font-semibold text-gray-700 mb-1">Tipe Smartphone</label>
                            <input type="text" name="smartphone_type" id="smartphone_type" value="{{ old('smartphone_type', $partner->smartphone_type) }}" placeholder="Contoh: iPhone 15 Pro, Samsung S24" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('smartphone_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Rate Pendapatan per Jam -->
                        <div>
                            <label for="base_hourly_rate" class="block text-sm font-semibold text-gray-700 mb-1">Rate per Jam (Rupiah) <span class="text-red-500">*</span></label>
                            <input type="number" step="1000" min="0" name="base_hourly_rate" id="base_hourly_rate" value="{{ old('base_hourly_rate', $partner->base_hourly_rate) }}" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('base_hourly_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Status Kemitraan <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="active" {{ old('status', $partner->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ old('status', $partner->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Actions -->
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('partners.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-200 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-amber-600 hover:to-orange-700 transition shadow-md shadow-orange-100">
                        Perbarui Data Anggota
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
