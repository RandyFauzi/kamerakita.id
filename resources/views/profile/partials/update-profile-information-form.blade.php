<section>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-8" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Tampilan <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('name', $user->name) }}" required autocomplete="name">
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="md:col-span-2">
                <label for="avatar" class="block text-sm font-semibold text-gray-700 mb-1">Foto Profil (Max 200KB)</label>
                <input id="avatar" name="avatar" type="file" accept="image/*" class="block w-full px-3.5 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>

            <div>
                <label for="email_locked" class="block text-sm font-semibold text-gray-700 mb-1">Email Login</label>
                <input id="email_locked" type="email" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-500 cursor-not-allowed" value="{{ $user->email }}" disabled>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Role Akun</label>
                <div class="flex items-center h-[42px]">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border bg-indigo-50 text-indigo-700 border-indigo-100 uppercase">
                        @if($partner && $partner->partner_role === 'worker')
                            Kontributor
                        @elseif($partner && $partner->partner_role === 'mitra')
                            Mitra (Koordinator)
                        @else
                            {{ $user->role }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        @if($partner)
            <div class="border-t border-gray-100 pt-6 space-y-6">
                <div>
                    <span class="block text-xs font-black tracking-widest text-slate-400 uppercase font-mono mb-1">
                        DATA DIRI {{ $partner->partner_role === 'worker' ? 'KONTRIBUTOR' : strtoupper($partner->partner_role) }}
                    </span>
                    <h4 class="text-base font-bold text-gray-900">{{ $partner->mitra_id }} - {{ $partner->full_name }}</h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input id="full_name" name="full_name" type="text" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('full_name', $partner->full_name) }}" required>
                        <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
                    </div>

                    <div>
                        <label for="nik" class="block text-sm font-semibold text-gray-700 mb-1">NIK</label>
                        <input id="nik" name="nik" type="text" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('nik', $partner->nik) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('nik')" />
                    </div>

                    <div>
                        <label for="whatsapp_number" class="block text-sm font-semibold text-gray-700 mb-1">No. WhatsApp <span class="text-red-500">*</span></label>
                        <input id="whatsapp_number" name="whatsapp_number" type="text" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('whatsapp_number', $partner->whatsapp_number) }}" required>
                        <x-input-error class="mt-2" :messages="$errors->get('whatsapp_number')" />
                    </div>

                    <div>
                        <label for="smartphone_type" class="block text-sm font-semibold text-gray-700 mb-1">Tipe Smartphone</label>
                        <input id="smartphone_type" name="smartphone_type" type="text" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('smartphone_type', $partner->smartphone_type) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('smartphone_type')" />
                    </div>

                    <div>
                        <label for="has_headstrap" class="block text-sm font-semibold text-gray-700 mb-1">Memiliki Headstrap <span class="text-red-500">*</span></label>
                        <select id="has_headstrap" name="has_headstrap" required class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="0" {{ old('has_headstrap', $partner->has_headstrap ? '1' : '0') === '0' ? 'selected' : '' }}>Belum</option>
                            <option value="1" {{ old('has_headstrap', $partner->has_headstrap ? '1' : '0') === '1' ? 'selected' : '' }}>Sudah</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('has_headstrap')" />
                    </div>
                </div>

                <div>
                    <label for="full_address" class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea id="full_address" name="full_address" rows="3" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('full_address', $partner->full_address) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('full_address')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="bank_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Bank</label>
                        <input id="bank_name" name="bank_name" type="text" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('bank_name', $partner->bank_name) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
                    </div>

                    <div>
                        <label for="bank_account_number" class="block text-sm font-semibold text-gray-700 mb-1">Nomor Rekening</label>
                        <input id="bank_account_number" name="bank_account_number" type="text" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('bank_account_number', $partner->bank_account_number ) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('bank_account_number')" />
                    </div>

                    <div>
                        <label for="bank_account_owner" class="block text-sm font-semibold text-gray-700 mb-1">Nama Pemilik Rekening</label>
                        <input id="bank_account_owner" name="bank_account_owner" type="text" class="block w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('bank_account_owner', $partner->bank_account_owner ) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('bank_account_owner')" />
                    </div>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-650 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 transition shadow-md shadow-indigo-100">
                Simpan Profile
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-green-700"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
