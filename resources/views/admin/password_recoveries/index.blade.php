<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Password Recovery Requests') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola dan verifikasi permohonan reset kata sandi Mitra via WhatsApp.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Active Generated Reset Link Banner -->
            @if(session('generated_reset_url'))
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border-2 border-emerald-400 p-6 rounded-2xl shadow-sm">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold uppercase tracking-wider">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Permintaan Disetujui!
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mt-1">Link Reset Password Siap Digunakan</h3>
                        <p class="text-xs text-gray-600">
                            Untuk Mitra: <strong>{{ session('target_name') }}</strong> ({{ session('target_email') }})
                            @if(session('target_phone'))
                                • WhatsApp: <strong class="text-emerald-700">{{ session('target_phone') }}</strong>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-white rounded-xl border border-emerald-200">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tautan Reset Password (Berlaku 60 Menit)</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="generated-reset-url" value="{{ session('generated_reset_url') }}" readonly
                               class="w-full text-xs font-mono bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-800 select-all focus:ring-0">
                        <button type="button" onclick="copyResetUrl('generated-reset-url', 'copy-btn-banner')" id="copy-btn-banner"
                                class="px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-lg text-xs font-bold whitespace-nowrap transition shadow-sm">
                            Salin Link
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    @if(session('manual_wa_url'))
                        <a href="{{ session('manual_wa_url') }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm"
                           style="background-color: #059669;">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span>Buka Chat WhatsApp Mitra</span>
                        </a>
                    @endif

                    @if(session('target_phone') && session('recovery_request_id'))
                        <form action="{{ route('admin.password-recoveries.send-wa', session('recovery_request_id')) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                <span>Kirim Link ke WA Otomatis (Gateway)</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            <!-- Standard Session Alerts -->
            @if(session('success') && !session('generated_reset_url'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            <!-- Main Table Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50/80 rounded-lg">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold rounded-l-lg">Target Email / Akun</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Data User & WhatsApp</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Waktu Request</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                <th scope="col" class="px-6 py-4 font-semibold rounded-r-lg">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($requests as $req)
                                @php
                                    $partner = $req->user?->partner;
                                    $phone = $partner?->whatsapp_number;
                                    $activeCachedLink = \Illuminate\Support\Facades\Cache::get("pwd_reset_link_{$req->id}");
                                    $formattedPhone = $phone ? \App\Helpers\PhoneHelper::formatForGateway($phone) : null;
                                    $manualChatUrl = ($activeCachedLink && $formattedPhone) 
                                        ? "https://wa.me/{$formattedPhone}?text=" . rawurlencode("Halo *{$req->user?->name}*,\n\nPermintaan reset kata sandi akun KameraKita Anda telah *DISETUJUI* oleh Admin.\n\nSilakan klik tautan di bawah ini untuk membuat kata sandi baru:\n{$activeCachedLink}\n\n(Berlaku 60 menit)\nTerima kasih!")
                                        : null;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <div class="font-mono text-sm">{{ $req->identifier }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($req->user)
                                            <div class="font-bold text-gray-800">{{ $req->user->name }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $req->user->id }} • Role: <span class="capitalize font-semibold">{{ $req->user->role }}</span></div>
                                            
                                            @if($phone)
                                                <div class="mt-1.5 flex items-center gap-1.5 text-xs text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-1 rounded-md w-fit">
                                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                    <span>{{ $phone }}</span>
                                                </div>
                                            @else
                                                <div class="mt-1 text-[11px] text-amber-600 font-medium italic">⚠️ Belum ada nomor WA</div>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                Unknown User
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-semibold text-gray-700">{{ $req->requested_at->format('d M Y, H:i') }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono mt-0.5">IP: {{ $req->request_ip }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($req->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                Pending
                                            </span>
                                        @elseif($req->status === 'approved')
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                    Approved
                                                </span>
                                                @if($activeCachedLink)
                                                    <span class="block text-[10px] font-bold text-emerald-600">Link Aktif</span>
                                                @endif
                                            </div>
                                        @elseif($req->status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-800">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($req->status === 'pending')
                                            <div class="flex items-center gap-1.5">
                                                <!-- Simple Approve Button -->
                                                <form action="{{ route('admin.password-recoveries.approve', $req) }}" method="POST" onsubmit="return confirm('Setujui permohonan ini dan buat link reset password?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider transition shadow-sm">
                                                        Approve
                                                    </button>
                                                </form>

                                                @if($phone)
                                                <!-- Approve & Auto Send WhatsApp Directly -->
                                                <form action="{{ route('admin.password-recoveries.approve', $req) }}" method="POST" onsubmit="return confirm('Setujui permohonan ini dan LANGSUNG kirim link via WhatsApp ke {{ $phone }}?');">
                                                    @csrf
                                                    <input type="hidden" name="send_wa_now" value="1">
                                                    <button type="submit" title="Approve & Kirim Link via WA Langsung" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-800 hover:bg-emerald-900 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider transition shadow-sm">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                        <span>+ WA</span>
                                                    </button>
                                                </form>
                                                @endif
                                                
                                                <form action="{{ route('admin.password-recoveries.reject', $req) }}" method="POST" onsubmit="return confirm('Tolak permohonan ini?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-gray-500 hover:bg-gray-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider transition shadow-sm">
                                                        Reject
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.password-recoveries.block', $req) }}" method="POST" onsubmit="return confirm('Tolak dan blokir request berikutnya dari user ini?');">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider transition shadow-sm" title="Tolak & Blokir">
                                                        Block
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-1.5">
                                                @if($activeCachedLink)
                                                    <input type="hidden" id="link-{{ $req->id }}" value="{{ $activeCachedLink }}">
                                                    <button type="button" onclick="copyResetUrl('link-{{ $req->id }}', 'btn-cp-{{ $req->id }}')" id="btn-cp-{{ $req->id }}"
                                                            class="inline-flex items-center px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                                        Salin Link
                                                    </button>

                                                    @if($manualChatUrl)
                                                        <a href="{{ $manualChatUrl }}" target="_blank"
                                                           class="inline-flex items-center px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition"
                                                           style="background-color: #059669;" title="Chat WhatsApp">
                                                            Chat WA
                                                        </a>
                                                    @endif

                                                    @if($phone)
                                                        <form action="{{ route('admin.password-recoveries.send-wa', $req) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition" title="Kirim Otomatis via Gateway WA">
                                                                Kirim WA
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 text-xs italic">Selesai / Expired</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Tidak ada permohonan reset kata sandi.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($requests->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyResetUrl(inputId, buttonId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            navigator.clipboard.writeText(input.value).then(() => {
                const btn = document.getElementById(buttonId);
                if (btn) {
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '✓ Tersalin!';
                    btn.classList.add('bg-emerald-600');
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.classList.remove('bg-emerald-600');
                    }, 2000);
                }
            }).catch(err => {
                input.select();
                document.execCommand('copy');
                alert('Tautan berhasil disalin ke clipboard!');
            });
        }
    </script>
</x-app-layout>
