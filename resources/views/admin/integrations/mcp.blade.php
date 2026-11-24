<x-app-layout>
    @php
        $roleLabel = match ($role) {
            'superadmin' => 'Superadmin',
            'admin' => 'Admin Operasional',
            'finance' => 'Finance',
            default => ucfirst((string) $role),
        };
        $accessScopeLabel = in_array('*', $abilities, true) || in_array('mcp.write', $abilities, true)
            ? 'Analitik & Operasional'
            : 'Akses Baca';
        $clientLabel = $clients[$client]['label'] ?? 'Codex';
        $canConnect = count($abilities) > 0;
        $examples = [
            'Atlas' => 'Berapa jam rekaman Atlas approved minggu ini dan status sync terakhir?',
            'Payroll' => 'Berapa total payroll Atlas yang masih UNPAID beserta nominalnya?',
            'Operasional' => 'Ada berapa antrean Main Account dan Password Recovery yang pending?',
            'Keuangan' => 'Ringkasan pemasukan, pengeluaran, dan invoice klien bulan ini?',
        ];
        $defaultExample = $role === 'finance' ? $examples['Payroll'] : $examples['Atlas'];
        $codexCommand = 'codex mcp add kamerakita --url ' . json_encode($endpoint, JSON_UNESCAPED_SLASHES) . ' --bearer-token-env-var KAMERAKITA_MCP_TOKEN';
        $jsonConfig = json_encode([
            'mcpServers' => [
                'kamerakita' => [
                    'type' => 'http',
                    'url' => $endpoint,
                    'headers' => [
                        'Authorization' => 'Bearer ${KAMERAKITA_MCP_TOKEN}',
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $setupPrompt = "Bantu saya menambahkan server MCP Kamerakita ke {$clientLabel}. URL: {$endpoint}. Gunakan Streamable HTTP dengan Bearer token dari pengaturan rahasia (KAMERAKITA_MCP_TOKEN). Jangan meminta kunci di chat. Setelah terhubung, jalankan get_mcp_context.";

        $tabs = [
            'setup' => ['label' => 'Hubungkan Aplikasi'],
            'connected' => ['label' => 'Aplikasi Terhubung'],
            'tokens' => ['label' => 'Kunci Akses'],
        ];
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="{
        copied: '',
        timer: null,
        selectedExample: @js($defaultExample),
        revealedTokens: {},
        visibleTokens: {},
        async copyText(text, key) {
            if (!text) return;
            try {
                await navigator.clipboard.writeText(text);
                this.copied = key;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.copied = ''; }, 2200);
            } catch {}
        },
        async fetchConnectionToken(id, url) {
            if (this.revealedTokens[id]) return this.revealedTokens[id];
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                if (!response.ok) return null;
                const data = await response.json();
                if (!data.token) return null;
                this.revealedTokens[id] = data.token;
                return data.token;
            } catch {
                return null;
            }
        },
        async toggleRevealToken(id, url) {
            if (this.visibleTokens[id]) {
                this.visibleTokens[id] = false;
                return;
            }
            const token = await this.fetchConnectionToken(id, url);
            if (token) this.visibleTokens[id] = true;
        },
        async copyConnectionToken(id, url) {
            const token = await this.fetchConnectionToken(id, url);
            if (token) await this.copyText(token, 'conn-' + id);
        }
    }">

        {{-- Modern Hero Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-600 text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Integrasi MCP</h1>
                    <p class="mt-0.5 text-sm text-slate-500">Hubungkan asisten AI ke data rekaman Atlas, payroll, QC, dan keuangan Kamerakita secara real-time.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-xs ring-1 ring-indigo-200/80">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>{{ $roleLabel }} &middot; {{ $accessScopeLabel }}</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-xs ring-1 ring-slate-200/80">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Streamable HTTP</span>
                </span>
            </div>
        </div>

        {{-- Floating Segmented Pill Tabs --}}
        <div class="inline-flex flex-wrap gap-1 rounded-2xl bg-white/80 p-1.5 shadow-xs ring-1 ring-slate-200/80 backdrop-blur">
            @foreach ($tabs as $key => $meta)
                <a href="{{ route('admin.integrations.mcp.index', array_filter(['tab' => $key === 'setup' ? null : $key, 'client' => $client === 'codex' ? null : $client])) }}"
                   @class([
                       'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-150',
                       'bg-indigo-600 text-white shadow-sm shadow-indigo-500/25' => $tab === $key,
                       'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' => $tab !== $key,
                   ])>
                    <span>{{ $meta['label'] }}</span>
                </a>
            @endforeach
        </div>

        @if (session('status'))
            <div class="flex items-center gap-2.5 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
                <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 ring-1 ring-rose-200">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- One-time New Token Banner --}}
        @if ($newToken)
            <div class="rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 p-5 text-white shadow-lg shadow-indigo-500/20">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 backdrop-blur">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold">Kunci {{ $clientLabel }} Siap Disalin</h2>
                            <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-semibold">Baru dibuat</span>
                        </div>
                        <p class="text-xs text-indigo-100">
                            Simpan kunci ini di pengaturan rahasia (<code class="rounded bg-white/15 px-1.5 py-0.5 font-mono">KAMERAKITA_MCP_TOKEN</code>) aplikasi AI Anda.
                        </p>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input class="flex-1 rounded-xl border-0 bg-white/95 px-3.5 py-2.5 font-mono text-xs text-slate-900 shadow-inner focus:ring-2 focus:ring-white" readonly spellcheck="false" value="{{ $newToken }}">
                            <button type="button" @click="copyText(@js($newToken), 'new-token')"
                                class="shrink-0 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-slate-800">
                                <span x-text="copied === 'new-token' ? 'Tersalin!' : 'Salin Kunci'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($tab === 'setup')
            {{-- Client Selector Pills --}}
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($clients as $key => $option)
                    <a href="{{ route('admin.integrations.mcp.index', $key === 'codex' ? [] : ['client' => $key]) }}"
                       @class([
                           'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition-all duration-150',
                           'bg-white text-indigo-700 shadow-sm ring-2 ring-indigo-500/80' => $client === $key,
                           'bg-white/75 text-slate-600 ring-1 ring-slate-200/80 hover:bg-white hover:text-slate-900 hover:shadow-xs' => $client !== $key,
                       ])>
                        <span @class(['h-2 w-2 rounded-full', 'bg-indigo-600' => $client === $key, 'bg-slate-300' => $client !== $key])></span>
                        <span>{{ $option['label'] }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Quick Action Hero Card --}}
            @if ($canConnect && ! $newToken)
                <div class="rounded-2xl bg-gradient-to-r from-indigo-50/90 via-white to-blue-50/80 p-5 shadow-sm ring-1 ring-indigo-200/70">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg bg-indigo-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">1-Klik Setup</span>
                                <span class="text-xs font-medium text-slate-500">Berlaku {{ config('mcp.token_days', 90) }} hari &middot; 23 Tools aktif</span>
                            </div>
                            <h2 class="text-base font-bold text-slate-900">Hubungkan {{ $clientLabel }} ke Kamerakita</h2>
                            <p class="text-xs text-slate-600">
                                {{ collect($abilities)->map(fn (string $a) => config("mcp.ability_labels.$a", $a))->implode(' · ') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.integrations.mcp.store') }}" x-data="{ busy: false }" @submit="busy = true" class="shrink-0">
                            @csrf
                            <input type="hidden" name="mode" value="quick">
                            <input type="hidden" name="client" value="{{ $client }}">
                            <button type="submit" :disabled="busy"
                                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-md shadow-indigo-500/25 transition hover:from-indigo-700 hover:to-blue-700 disabled:opacity-60 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                <span x-text="busy ? 'Membuat kunci…' : 'Buat Kunci Akses'">Buat Kunci Akses</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Modern 2-Column Bento Grid --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                {{-- Left Column: Setup Guide --}}
                <div class="lg:col-span-7 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/80 space-y-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-slate-900">Cara Pasang di {{ $clientLabel }}</h3>
                        </div>
                        <span class="text-[11px] font-medium text-slate-400">Streamable HTTP</span>
                    </div>

                    @if ($client === 'codex')
                        <p class="text-xs leading-relaxed text-slate-600">
                            Simpan kunci di variabel lingkungan <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-indigo-700">KAMERAKITA_MCP_TOKEN</code>, lalu jalankan perintah berikut di terminal:
                        </p>
                        <div class="relative rounded-xl bg-slate-900 p-4 text-slate-100">
                            <button type="button" @click="copyText(@js($codexCommand), 'cmd')"
                                class="absolute right-3 top-3 rounded-lg bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-white/20">
                                <span x-text="copied === 'cmd' ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                            <pre class="pr-16 font-mono text-xs leading-relaxed overflow-x-auto">{{ $codexCommand }}</pre>
                        </div>
                    @elseif (in_array($client, ['chatgpt', 'gemini'], true))
                        <div class="rounded-xl bg-slate-50 p-3.5 ring-1 ring-slate-200/70 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Endpoint Server MCP (OAuth 2.1)</span>
                                <button type="button" @click="copyText(@js($endpoint), 'url')"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                    <span x-text="copied === 'url' ? 'URL Tersalin!' : 'Salin URL'"></span>
                                </button>
                            </div>
                            <code class="block font-mono text-xs text-slate-800 break-all">{{ $endpoint }}</code>
                        </div>

                        @if ($client === 'chatgpt')
                            <ol class="list-decimal space-y-2 pl-4 text-xs leading-relaxed text-slate-600">
                                <li>Buka <strong class="text-slate-900">chatgpt.com</strong> &rarr; menu <strong class="text-slate-900">Apps / Plugins</strong> &rarr; <strong class="text-slate-900">Add custom MCP server</strong>.</li>
                                <li>Tempel URL di atas, lalu pilih autentikasi <strong class="text-slate-900">OAuth</strong>.</li>
                                <li>Login dan klik <strong class="text-slate-900">Izinkan akses</strong> pada layar persetujuan Kamerakita.</li>
                            </ol>
                        @else
                            <ol class="list-decimal space-y-2 pl-4 text-xs leading-relaxed text-slate-600">
                                <li>Buka <strong class="text-slate-900">gemini.google.com</strong> &rarr; <strong class="text-slate-900">Settings</strong> &rarr; <strong class="text-slate-900">Connected Apps</strong> &rarr; <strong class="text-slate-900">Add a custom app</strong>.</li>
                                <li>Isi nama <code class="rounded bg-slate-100 px-1 font-mono text-indigo-700">Kamerakita</code> dan tempel URL di atas.</li>
                                <li>Selesaikan autentikasi OAuth 2.1 lalu klik <strong class="text-slate-900">Next</strong>.</li>
                            </ol>
                        @endif
                    @else
                        <p class="text-xs leading-relaxed text-slate-600">
                            Tempel konfigurasi ini ke <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-indigo-700">mcp_config.json</code> (Antigravity) atau <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-indigo-700">.mcp.json</code> (Claude Code):
                        </p>
                        <div class="relative rounded-xl bg-slate-900 p-4 text-slate-100">
                            <button type="button" @click="copyText(@js($jsonConfig), 'json')"
                                class="absolute right-3 top-3 rounded-lg bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-white/20">
                                <span x-text="copied === 'json' ? 'Tersalin!' : 'Salin JSON'"></span>
                            </button>
                            <pre class="font-mono text-xs leading-relaxed overflow-x-auto">{{ $jsonConfig }}</pre>
                        </div>
                    @endif

                    <div class="border-t border-slate-100 pt-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-700">Prompt Setup Otomatis untuk AI</span>
                            <button type="button" @click="copyText(@js($setupPrompt), 'setup-prompt')"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                <span x-text="copied === 'setup-prompt' ? 'Prompt Tersalin!' : 'Salin Prompt'"></span>
                            </button>
                        </div>
                        <p class="rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-600 ring-1 ring-slate-200/70">{{ $setupPrompt }}</p>
                    </div>
                </div>

                {{-- Right Column: Try Examples + Advanced --}}
                <div class="lg:col-span-5 space-y-5">
                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 space-y-3.5">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-slate-900">Coba Setelah Terhubung</h3>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($examples as $label => $text)
                                <button type="button" @click="selectedExample = @js($text)"
                                    :class="selectedExample === @js($text) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="rounded-lg px-2.5 py-1 text-[11px] font-semibold transition cursor-pointer">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-800 ring-1 ring-slate-200/70" x-text="selectedExample"></div>

                        <button type="button" @click="copyText(selectedExample, 'example')"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer">
                            <span x-text="copied === 'example' ? 'Pertanyaan Tersalin!' : 'Salin Pertanyaan'"></span>
                        </button>
                    </div>

                    @unless ($newToken)
                        <details class="group rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80" @if (old('mode') === 'custom') open @endif>
                            <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 text-xs font-bold text-slate-700 hover:text-slate-900">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                    <span>Opsi Lanjutan (Custom Kunci)</span>
                                </span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <form method="POST" action="{{ route('admin.integrations.mcp.store') }}" class="space-y-3 border-t border-slate-100 p-5">
                                @csrf
                                <input type="hidden" name="mode" value="custom">
                                <input type="hidden" name="client" value="{{ $client }}">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-700">Nama Koneksi</label>
                                    <input name="name" required maxlength="100" value="{{ old('name', $clientLabel) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-xs focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-700">Masa Berlaku (Hari)</label>
                                    <input type="number" name="expires_in_days" min="1" max="365" value="{{ old('expires_in_days', config('mcp.token_days', 90)) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-xs focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                                </div>
                                <div class="space-y-1.5">
                                    <span class="block text-xs font-semibold text-slate-700">Izin</span>
                                    @foreach ($abilities as $ability)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700 hover:bg-indigo-50/40">
                                            <input type="checkbox" name="abilities[]" value="{{ $ability }}" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span>{{ config("mcp.ability_labels.$ability", $ability) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold text-white hover:bg-slate-800 cursor-pointer">
                                    Buat Kunci Custom
                                </button>
                            </form>
                        </details>
                    @endunless
                </div>
            </div>
        @else
            {{-- Connected Apps & Access Tokens --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">{{ $tabs[$tab]['label'] }}</h2>
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-700 ring-1 ring-indigo-200">
                                {{ ($connections?->total() ?? 0) + ($oauthTokens?->count() ?? 0) }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $tab === 'connected' ? 'Sesi OAuth dan kunci akses yang sudah pernah aktif terhubung.' : 'Kelola kunci akses Bearer untuk pemasangan di IDE atau CLI.' }}
                        </p>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @if ($tab === 'connected')
                        @foreach ($oauthTokens as $oauth)
                            <div class="flex flex-col justify-between gap-3 px-6 py-4 transition hover:bg-slate-50/60 sm:flex-row sm:items-center">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-slate-900">{{ $oauth->client?->name ?? 'OAuth Client' }}</span>
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">OAuth 2.1 Aktif</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Scopes: {{ implode(', ', $oauth->scopes ?? ['mcp:use']) }} &middot; Dibuat {{ $oauth->created_at?->diffForHumans() }}
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('admin.integrations.mcp.oauth.revoke', $oauth->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl bg-rose-50 px-3.5 py-1.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
                                        Cabut Sesi
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    @endif

                    @forelse ($connections ?? [] as $conn)
                        <div class="flex flex-col justify-between gap-3 px-6 py-4 transition hover:bg-slate-50/60 sm:flex-row sm:items-center">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900">{{ $conn->name }}</span>
                                    <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">{{ $conn->client_type }}</span>
                                    @if ($conn->isActive())
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">Aktif</span>
                                    @else
                                        <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-rose-200">Dicabut / Kedaluwarsa</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $conn->user?->email }} &middot;
                                    Kunci: <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono">...{{ $conn->token_suffix ?? '******' }}</code> &middot;
                                    Terakhir dipakai: {{ $conn->last_used_at ? $conn->last_used_at->diffForHumans() : 'Belum pernah' }}
                                </p>
                                <div x-show="visibleTokens['{{ $conn->id }}']" class="mt-2">
                                    <code class="block rounded-xl bg-slate-900 px-3.5 py-2 font-mono text-xs text-slate-100 break-all" x-text="revealedTokens['{{ $conn->id }}']"></code>
                                </div>
                            </div>

                            @if ($conn->isActive())
                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($conn->token_encrypted)
                                        <button type="button"
                                            @click="toggleRevealToken('{{ $conn->id }}', '{{ route('admin.integrations.mcp.reveal', $conn) }}')"
                                            class="rounded-xl bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 cursor-pointer">
                                            <span x-text="visibleTokens['{{ $conn->id }}'] ? 'Sembunyikan' : 'Lihat'">Lihat</span>
                                        </button>
                                        <button type="button"
                                            @click="copyConnectionToken('{{ $conn->id }}', '{{ route('admin.integrations.mcp.reveal', $conn) }}')"
                                            class="rounded-xl bg-indigo-50 px-3.5 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200 hover:bg-indigo-100 cursor-pointer">
                                            <span x-text="copied === 'conn-{{ $conn->id }}' ? 'Tersalin!' : 'Salin'">Salin</span>
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.integrations.mcp.destroy', $conn) }}" onsubmit="return confirm('Cabut kunci akses MCP ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="tab" value="{{ $tab }}">
                                        <button type="submit" class="rounded-xl bg-rose-50 px-3.5 py-1.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100 cursor-pointer">
                                            Cabut
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        @if ($tab !== 'connected' || $oauthTokens->isEmpty())
                            <div class="px-6 py-10 text-center text-sm text-slate-500">
                                Belum ada {{ $tab === 'connected' ? 'aplikasi terhubung' : 'kunci akses' }}.
                            </div>
                        @endif
                    @endforelse
                </div>

                @if ($connections && $connections->hasPages())
                    <div class="border-t border-slate-100 px-6 py-3">
                        {{ $connections->links() }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Recent Audit Trail --}}
        @if ($auditLogs->isNotEmpty())
            <details class="group rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80">
                <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-4 text-xs font-bold text-slate-700 hover:text-slate-900">
                    <span>Riwayat Panggilan Tool Terbaru ({{ $auditLogs->count() }})</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="overflow-x-auto border-t border-slate-100 px-6 py-3">
                    <table class="min-w-full text-left text-xs">
                        <thead class="border-b border-slate-100 text-[11px] uppercase text-slate-400">
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
                                    <td class="py-2.5 pr-4 text-slate-500">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-2.5 pr-4 font-medium text-slate-800">{{ $log->client_name }}</td>
                                    <td class="py-2.5 pr-4 font-mono text-indigo-700">{{ $log->tool_name ?? $log->method }}</td>
                                    <td class="py-2.5 pr-4">
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-slate-500">{{ $log->execution_time_ms }} ms</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endif
    </div>
</x-app-layout>
