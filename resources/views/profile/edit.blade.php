<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Profil Akun') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ activeMenu: null }">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[32px] shadow-sm border border-gray-150 p-6 relative">
                
                <!-- ================= PROFILE INFO ================= -->
                <div class="flex flex-col items-center mb-8 mt-2">
                    <!-- Avatar Foto -->
                    <div class="w-24 h-24 rounded-full overflow-hidden mb-4 border-[3px] border-white shadow-lg bg-gray-50 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    
                    <!-- Nama & Kontak -->
                    <h2 class="text-[22px] font-bold text-gray-900 mb-1">{{ $partner->full_name ?? $user->name }}</h2>
                    <p class="text-xs text-gray-500 mb-3 font-medium">{{ $user->email }} @if($partner) | {{ $partner->whatsapp_number }} @endif</p>
                    
                    <!-- Badge Status -->
                    <div class="flex items-center gap-1.5 text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span class="text-[11px] font-bold tracking-wide uppercase">
                            @if($partner && $partner->partner_role === 'worker')
                                Kontributor
                            @elseif($partner && $partner->partner_role === 'mitra')
                                Mitra
                            @else
                                {{ $user->role }}
                            @endif
                        </span>
                    </div>
                </div>

                @if($partner)
                <!-- ================= ACCOUNT DETAILS CARD ================= -->
                <div class="bg-white border border-gray-150 rounded-2xl p-5 mb-8 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)]">
                    <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-3">
                        <h3 class="text-[14px] font-bold text-gray-900">Detail Rekening & Alat</h3>
                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Tersimpan</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[11px] text-gray-400 font-medium mb-1">Nama Bank</p>
                            <p class="text-[13px] font-bold text-gray-900">{{ $partner->bank_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] text-gray-400 font-medium mb-1">Tipe Smartphone</p>
                            <p class="text-[13px] font-bold text-gray-900 truncate">{{ $partner->smartphone_type ?? '-' }}</p>
                        </div>
                        <div class="col-span-2 mt-1">
                            <p class="text-[11px] text-gray-400 font-medium mb-1">Atas Nama (No. Rek)</p>
                            <p class="text-[14px] font-bold text-gray-800">{{ $partner->bank_account_owner ?? $partner->account_owner_name ?? '-' }} <span class="text-gray-500 font-mono font-medium text-xs">({{ $partner->bank_account_number ?? $partner->account_number ?? '-' }})</span></p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- ================= SETTINGS LIST ================= -->
                <div class="flex flex-col gap-3">
                    
                    <!-- Item 1: Profile Info -->
                    <div class="border border-gray-150 rounded-2xl overflow-hidden shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)] bg-white transition-all duration-300" :class="{'ring-2 ring-blue-100 border-blue-200': activeMenu === 'profile'}">
                        <div @click="activeMenu = activeMenu === 'profile' ? null : 'profile'" class="px-4 py-4 flex items-center justify-between cursor-pointer group hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-bold text-gray-800 mb-0.5">Informasi Pribadi</h4>
                                    <p class="text-[11px] text-gray-400 font-medium">Ubah nama, kontak, & rekening bank.</p>
                                </div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300" :class="{'rotate-90': activeMenu === 'profile'}">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        
                        <!-- Form Area -->
                        <div x-show="activeMenu === 'profile'" x-collapse class="px-5 pb-6 pt-2 border-t border-gray-100 bg-gray-50/50">
                            <div class="mt-4">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>
                    </div>

                    <!-- Item 2: Password -->
                    <div class="border border-gray-150 rounded-2xl overflow-hidden shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)] bg-white transition-all duration-300" :class="{'ring-2 ring-indigo-100 border-indigo-200': activeMenu === 'password'}">
                        <div @click="activeMenu = activeMenu === 'password' ? null : 'password'" class="px-4 py-4 flex items-center justify-between cursor-pointer group hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 1c-3.31 0-6 2.69-6 6v3H5c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V12c0-1.1-.9-2-2-2h-1V7c0-3.31-2.69-6-6-6zm0 2c2.21 0 4 1.79 4 4v3H8V7c0-2.21 1.79-4 4-4zm0 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-bold text-gray-800 mb-0.5">Keamanan Sandi</h4>
                                    <p class="text-[11px] text-gray-400 font-medium">Ubah password login Anda di sini.</p>
                                </div>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300" :class="{'rotate-90': activeMenu === 'password'}">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        
                        <!-- Form Area -->
                        <div x-show="activeMenu === 'password'" x-collapse class="px-5 pb-6 pt-2 border-t border-gray-100 bg-gray-50/50">
                            <div class="mt-4">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>

                    <!-- Item 3: Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full border border-red-100 rounded-2xl overflow-hidden shadow-[0_2px_10px_-4px_rgba(0,0,0,0.02)] bg-red-50/50 px-4 py-4 flex items-center justify-between cursor-pointer group hover:bg-red-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform shadow-sm">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <h4 class="text-[13px] font-bold text-red-600 mb-0.5">Keluar Akun</h4>
                                    <p class="text-[11px] text-red-400 font-medium">Akhiri sesi dan keluar dari aplikasi.</p>
                                </div>
                            </div>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
