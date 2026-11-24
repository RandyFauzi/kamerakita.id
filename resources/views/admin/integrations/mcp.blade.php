<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Integrasi MCP (AI Assistant)</h2>
                <p class="text-sm text-slate-500">Hubungkan ChatGPT, Gemini, Antigravity, Claude Code, atau Codex ke Kamerakita MCP Server.</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">
                    Role: {{ auth()->user()->role }}
                </span>
                <span class="px-2.5 py-1 rounded-full {{ $oauthEnabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }} font-semibold border">
                    OAuth 2.1: {{ $oauthEnabled ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="{ tab: 'connect', revealed: {}, copied: null }">
        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800 font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if (session('new_mcp_token'))
            <div class="rounded-2xl bg-indigo-50 border border-indigo-200 p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-indigo-900">Kunci Akses Baru Siap Digunakan</span>
                    <button type="button"
                        @click="navigator.clipboard.writeText('{{ session('new_mcp_token') }}'); copied = 'new'; setTimeout(() => copied = null, 2000)"
                        class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                        <span x-text="copied === 'new' ? 'Tersalin!' : 'Salin Token'"></span>
                    </button>
                </div>
                <code class="block w-full rounded-lg bg-white border border-indigo-200 px-3 py-2 text-xs text-slate-800 break-all">{{ session('new_mcp_token') }}</code>
            </div>
        @endif

        <!-- Endpoint Info -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Streamable HTTP Endpoint</div>
                <div class="mt-1 font-mono text-sm font-semibold text-slate-800 break-all">{{ $endpointUrl }}</div>
                <div class="mt-1 text-xs text-slate-500">
                    Kemampuan aktif untuk role Anda:
                    @forelse ($abilities as $ability)
                        <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[11px] mr-1">{{ $ability }}</span>
                    @empty
                        <span class="text-rose-600">Tidak ada</span>
                    @endforelse
                </div>
            </div>
            <button type="button"
                @click="navigator.clipboard.writeText('{{ $endpointUrl }}'); copied = 'url'; setTimeout(() => copied = null, 2000)"
                class="shrink-0 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800">
                <span x-text="copied === 'url' ? 'URL Tersalin!' : 'Salin URL Server'"></span>
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2">
            <button type="button" @click="tab = 'connect'"
                :class="tab === 'connect' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                Hubungkan Aplikasi
            </button>
            <button type="button" @click="tab = 'oauth'"
                :class="tab === 'oauth' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                Aplikasi Terhubung ({{ $oauthTokens->count() }})
            </button>
            <button type="button" @click="tab = 'keys'"
                :class="tab === 'keys' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                Kunci Akses ({{ $connections->count() }})
            </button>
        </div>

        <!-- Tab 1: Hubungkan Aplikasi -->
        <div x-show="tab === 'connect'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900">1. ChatGPT Web &amp; Gemini Web (OAuth 2.1)</h3>
                <div class="text-sm text-slate-600 space-y-2">
                    <p class="font-semibold text-slate-800">ChatGPT Custom MCP:</p>
                    <ol class="list-decimal list-inside space-y-1 text-xs">
                        <li>Buka <strong>chatgpt.com</strong> &rarr; <strong>Apps / Plugins</strong> &rarr; <strong>Add custom MCP server</strong>.</li>
                        <li>Masukkan URL: <code class="bg-slate-100 px-1.5 py-0.5 rounded">{{ $endpointUrl }}</code></li>
                        <li>Pilih autentikasi <strong>OAuth</strong> lalu klik <strong>Create</strong>.</li>
                        <li>Login dan klik <strong>Izinkan Akses</strong> pada halaman persetujuan Kamerakita.</li>
                    </ol>
                    <p class="font-semibold text-slate-800 pt-2">Gemini Web (gemini.google.com):</p>
                    <ol class="list-decimal list-inside space-y-1 text-xs">
                        <li>Buka <strong>Settings &rarr; Connected Apps &rarr; Add a custom app</strong>.</li>
                        <li>Masukkan nama <strong>Kamerakita MCP</strong> dan URL <code class="bg-slate-100 px-1.5 py-0.5 rounded">{{ $endpointUrl }}</code>.</li>
                        <li>Selesaikan autentikasi OAuth untuk sinkronisasi otomatis.</li>
                    </ol>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900">2. Antigravity / Claude Code / Gemini CLI / Codex</h3>
                <p class="text-xs text-slate-500">Buat kunci akses di tab <strong>Kunci Akses</strong>, lalu tempelkan ke <code class="bg-slate-100 px-1 rounded">mcp_config.json</code> / <code class="bg-slate-100 px-1 rounded">.mcp.json</code>:</p>
                <pre class="rounded-xl bg-slate-900 text-slate-100 p-4 text-xs overflow-x-auto">{
  "mcpServers": {
    "kamerakita": {
      "type": "http",
      "url": "{{ $endpointUrl }}",
      "headers": {
        "Authorization": "Bearer kkm_token_akses_anda"
      }
    }
  }
}</pre>
            </div>
        </div>

        <!-- Tab 2: Aplikasi Terhubung (OAuth) -->
        <div x-show="tab === 'oauth'" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900">Sesi OAuth 2.1 Aktif</h3>
            @if ($oauthTokens->isEmpty())
                <p class="text-sm text-slate-500">Belum ada aplikasi OAuth (ChatGPT / Gemini Web) yang terhubung.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($oauthTokens as $token)
                        <div class="py-3 flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $token->client?->name ?? 'OAuth Client' }}</div>
                                <div class="text-xs text-slate-500">
                                    Scopes: {{ implode(', ', $token->scopes ?? ['mcp:use']) }}
                                    &middot; Dibuat {{ $token->created_at?->diffForHumans() }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.integrations.mcp.oauth.revoke', $token->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100">
                                    Cabut Sesi
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tab 3: Kunci Akses -->
        <div x-show="tab === 'keys'" class="space-y-6">
            <form method="POST" action="{{ route('admin.integrations.mcp.store') }}" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Koneksi</label>
                    <input type="text" name="name" required placeholder="Misal: Antigravity Laptop"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Klien</label>
                    <select name="client_type" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="antigravity">Antigravity</option>
                        <option value="claude_code">Claude Code</option>
                        <option value="gemini_cli">Gemini CLI</option>
                        <option value="codex">Codex</option>
                        <option value="manual">Lainnya (Manual)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Masa Berlaku (Hari, opsional)</label>
                    <input type="number" name="expires_in_days" min="1" max="365" placeholder="Kosong = tanpa batas"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    + Buat Kunci Akses
                </button>
            </form>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-base font-bold text-slate-900 mb-3">Daftar Kunci Akses</h3>
                @if ($connections->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada kunci akses MCP yang dibuat.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($connections as $conn)
                            <div class="py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-slate-900">{{ $conn->name }}</span>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">{{ $conn->client_type }}</span>
                                        @if ($conn->isActive())
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700">Dicabut / Kedaluwarsa</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Akun: {{ $conn->user?->email }}
                                        &middot; Suffix: <code class="bg-slate-100 px-1 rounded">...{{ $conn->token_suffix ?? '******' }}</code>
                                        &middot; Terakhir dipakai: {{ $conn->last_used_at ? $conn->last_used_at->diffForHumans() : 'Belum pernah' }}
                                    </div>
                                    <div x-show="revealed['{{ $conn->id }}']" class="mt-2">
                                        <code class="block rounded-lg bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs text-slate-800 break-all" x-text="revealed['{{ $conn->id }}']"></code>
                                    </div>
                                </div>
                                @if ($conn->isActive())
                                    <div class="flex items-center gap-2">
                                        @if ($conn->token_encrypted)
                                            <button type="button"
                                                @click="
                                                    if (!revealed['{{ $conn->id }}']) {
                                                        const res = await fetch('{{ route('admin.integrations.mcp.reveal', $conn) }}', {
                                                            method: 'POST',
                                                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                                        });
                                                        if (res.ok) { const d = await res.json(); revealed['{{ $conn->id }}'] = d.token; }
                                                    } else {
                                                        delete revealed['{{ $conn->id }}'];
                                                    }
                                                "
                                                class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200">
                                                <span x-text="revealed['{{ $conn->id }}'] ? 'Sembunyikan' : 'Lihat'"></span>
                                            </button>
                                            <button type="button"
                                                @click="
                                                    let tok = revealed['{{ $conn->id }}'];
                                                    if (!tok) {
                                                        const res = await fetch('{{ route('admin.integrations.mcp.reveal', $conn) }}', {
                                                            method: 'POST',
                                                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                                        });
                                                        if (res.ok) { const d = await res.json(); tok = d.token; revealed['{{ $conn->id }}'] = tok; }
                                                    }
                                                    if (tok) { navigator.clipboard.writeText(tok); copied = '{{ $conn->id }}'; setTimeout(() => copied = null, 2000); }
                                                "
                                                class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100">
                                                <span x-text="copied === '{{ $conn->id }}' ? 'Tersalin!' : 'Salin'"></span>
                                            </button>
                                        @endif
                                        <form method="POST" action="{{ route('admin.integrations.mcp.destroy', $conn) }}" onsubmit="return confirm('Cabut kunci akses MCP ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100">
                                                Cabut
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Audit Trail -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 mb-3">Riwayat Panggilan Tool Terbaru</h3>
            @if ($auditLogs->isEmpty())
                <p class="text-sm text-slate-500">Belum ada aktivitas pemanggilan tool MCP.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-left">
                        <thead class="text-slate-400 uppercase border-b border-slate-100">
                            <tr>
                                <th class="py-2 pr-4">Waktu</th>
                                <th class="py-2 pr-4">Klien</th>
                                <th class="py-2 pr-4">Tool</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2">Durasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($auditLogs as $log)
                                <tr>
                                    <td class="py-2 pr-4 text-slate-500">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-2 pr-4 font-medium text-slate-800">{{ $log->client_name }}</td>
                                    <td class="py-2 pr-4 font-mono text-indigo-700">{{ $log->tool_name ?? $log->method }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="px-2 py-0.5 rounded {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} font-semibold">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-slate-500">{{ $log->execution_time_ms }} ms</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
