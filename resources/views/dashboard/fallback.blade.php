<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-8 text-center space-y-4">
                <div class="mx-auto w-16 h-16 bg-indigo-50 text-indigo-650 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Menunggu Tautan Profil Mitra</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto">
                    Akun Anda (<strong>{{ $user->email }}</strong>) terdaftar dengan peran <strong>{{ ucfirst($user->role) }}</strong>, namun belum ditautkan ke profil Mitra (Worker atau Contributor) dalam sistem dashboard Kamerakita.
                </p>
                <div class="pt-4">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition">
                        Kelola Profil Akun
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
