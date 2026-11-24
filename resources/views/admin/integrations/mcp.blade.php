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
        $example = match ($role) {
            'finance' => 'Berapa total payroll Atlas yang masih UNPAID dan ringkasan invoice bulan ini?',
            default => 'Berapa jam rekaman Atlas approved minggu ini dan ada berapa antrean Main Account pending?',
        };
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
            'setup' => ['label' => 'Hubungkan aplikasi'],
            'connected' => ['label' => 'Aplikasi terhubung'],
            'tokens' => ['label' => 'Kunci akses'],
        ];

        $primaryButton = 'inline-flex h-[40px] cursor-pointer items-center justify-center gap-2 rounded-[10px] bg-indigo-600 px-4 text-[12px] font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60';
        $secondaryButton = 'inline-flex h-[38px] cursor-pointer items-center justify-center gap-2 rounded-[10px] border border-slate-200 bg-white px-4 text-[12px] font-semibold text-slate-800 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700';
        $field = 'w-full min-w-0 rounded-[10px] border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-[13px] text-slate-900 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/15';
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5" x-data="{
        message: '',
        copied: '',
        timer: null,
        revealedTokens: {},
        visibleTokens: {},
        loadingToken: '',
        async copy(field, label) {
            const input = this.$refs[field];
            if (!input) return;
            try {
                await navigator.clipboard.writeText(input.value);
                this.copied = field;
                this.message = label + ' disalin ke clipboard.';
                clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.copied = ''; }, 2200);
            } catch {
                input.focus();
                input.select();
                this.message = 'Teks dipilih. Tekan Ctrl+C untuk menyalin.';
            }
        },
        async fetchConnectionToken(id, url) {
            if (this.revealedTokens[id]) return this.revealedTokens[id];
            this.loadingToken = id;
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
            } finally {
                this.loadingToken = '';
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
            if (!token) return;
            try {
                await navigator.clipboard.writeText(token);
                this.copied = 'conn-' + id;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.copied = ''; }, 2200);
            } catch {
                this.visibleTokens[id] = true;
            }
        }
    }">
        <p class="sr-only" role="status" aria-live="polite" x-text="message"></p>

        {{-- Page Header --}}
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-[24px] font-bold leading-tight tracking-tight text-slate-900">Integrasi MCP</h1>
                <p class="mt-0.5 text-[13px] text-slate-500">Tanya rekaman Atlas, payroll, QC, atau keuangan Kamerakita langsung dari aplikasi AI Anda.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-[11px] font-semibold text-indigo-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>{{ $roleLabel }} &middot; {{ $accessScopeLabel }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span>Streamable HTTP</span>
                </span>
            </div>
        </header>

        {{-- Main Navigation Tabs --}}
        <nav aria-label="Bagian integrasi MCP" class="flex flex-wrap gap-2 border-b border-slate-200 pb-px">
            @foreach ($tabs as $key => $meta)
                <a href="{{ route('admin.integrations.mcp.index', array_filter(['tab' => $key === 'setup' ? null : $key, 'client' => $client === 'codex' ? null : $client])) }}"
                   @if ($tab === $key) aria-current="page" @endif
                   @class([
                       'inline-flex min-h-[42px] items-center gap-2 border-b-2 px-3 text-[13px] font-semibold transition',
                       'border-indigo-600 text-indigo-600' => $tab === $key,
                       'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-900' => $tab !== $key,
                   ])>
                    @if ($key === 'setup')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @elseif ($key === 'connected')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    @endif
                    <span>{{ $meta['label'] }}</span>
                </a>
            @endforeach
        </nav>

        @if (session('status'))
            <div class="rounded-[12px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-[12px] border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] font-medium text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- One-time New Token Banner --}}
        @if ($newToken)
            <section id="mcp-new-key" class="rounded-[14px] border border-indigo-200 bg-indigo-50/70 p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[12px] bg-indigo-600 text-white shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </span>
                    <div class="min-w-0 flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-[15px] font-bold text-slate-900">Kunci {{ $clientLabel }} siap disalin</h2>
                            <span class="rounded-full border border-indigo-200 bg-white px-2.5 py-0.5 text-[10px] font-semibold text-indigo-700">
                                Ditampilkan satu kali
                            </span>
                        </div>
                        <p class="text-[13px] text-slate-600">
                            Simpan kunci ini di pengaturan rahasia (<code class="rounded bg-white px-1.5 py-0.5 font-mono text-[12px] text-indigo-700">KAMERAKITA_MCP_TOKEN</code>) aplikasi AI Anda. Anda juga dapat melihatnya kembali di tab <strong>Kunci akses</strong>.
                        </p>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input class="{{ $field }} flex-1 border-indigo-200 bg-white font-mono" readonly spellcheck="false" x-ref="token" value="{{ $newToken }}">
                            <button class="{{ $primaryButton }} shrink-0" type="button" @click="copy('token', 'Kunci')">
                                <span x-text="copied === 'token' ? 'Kunci tersalin' : 'Salin kunci'">Salin kunci</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if ($tab === 'setup')
            <section class="overflow-hidden rounded-[14px] border border-slate-200 bg-white shadow-sm">
                {{-- Client Selector Bar --}}
                <nav class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50/80 p-3 sm:px-5 sm:py-3.5" aria-label="Pilih aplikasi">
                    @foreach ($clients as $key => $option)
                        <a href="{{ route('admin.integrations.mcp.index', $key === 'codex' ? [] : ['client' => $key]) }}"
                           @if ($client === $key) aria-current="page" @endif
                           @class([
                               'inline-flex h-[38px] items-center gap-2 rounded-[10px] border px-3.5 text-[13px] font-semibold transition',
                               'border-indigo-200 bg-indigo-50 text-indigo-700 shadow-2xs' => $client === $key,
                               'border-transparent bg-transparent text-slate-600 hover:border-slate-200 hover:bg-white hover:text-slate-900' => $client !== $key,
                           ])>
                            <span>{{ $option['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="space-y-5 p-5 sm:p-6">
                    {{-- Client Heading --}}
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[12px] bg-indigo-50 text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-[17px] font-bold text-slate-900">{{ $client === 'other' ? 'Hubungkan aplikasi lain' : 'Hubungkan ' . $clientLabel }}</h2>
                            <p class="mt-0.5 text-[13px] text-slate-500">
                                {{ in_array($client, ['chatgpt', 'gemini'], true) ? 'Hubungkan dengan OAuth 2.1 atau buat kunci akses, lalu coba pertanyaan di bawah.' : 'Buat satu kunci akses, pasang di aplikasi AI, lalu coba pertanyaan di bawah.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Quick Key Creation Card --}}
                    @if ($canConnect && ! $newToken)
                        <div class="flex flex-col justify-between gap-4 rounded-[12px] border border-indigo-200/80 bg-indigo-50/45 p-4 sm:flex-row sm:items-center">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] border border-indigo-200 bg-white text-indigo-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </span>
                                <div>
                                    <p class="text-[14px] font-bold text-slate-900">
                                        {{ collect($abilities)->map(fn (string $a) => config("mcp.ability_labels.$a", $a))->implode(' · ') }}
                                    </p>
                                    <p class="mt-0.5 text-[12px] text-slate-500">
                                        23 MCP tools tersedia &middot; Berlaku {{ config('mcp.token_days', 90) }} hari
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.integrations.mcp.store') }}" x-data="{ busy: false }" @submit="busy = true" class="shrink-0">
                                @csrf
                                <input type="hidden" name="mode" value="quick">
                                <input type="hidden" name="client" value="{{ $client }}">
                                <button type="submit" class="{{ $primaryButton }} w-full sm:w-auto" :disabled="busy">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    <span x-text="busy ? 'Membuat kunci…' : 'Buat kunci akses'">Buat kunci akses</span>
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Step-by-step Setup Accordion --}}
                    @if ($canConnect)
                        <details class="group rounded-[12px] border border-slate-200 bg-white transition open:border-indigo-200" @if ($newToken || in_array($client, ['chatgpt', 'gemini'], true)) open @endif>
                            <summary class="flex min-h-[46px] cursor-pointer list-none items-center justify-between gap-3 rounded-[12px] px-4 py-3 text-[13px] font-semibold text-slate-900 transition hover:bg-slate-50">
                                <span class="flex items-center gap-2.5">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </span>
                                    <span>Cara pasang di {{ $clientLabel }}</span>
                                </span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>

                            <div class="space-y-4 border-t border-slate-200 p-4 sm:p-5">
                                @if ($client === 'codex')
                                    <p class="text-[13px] text-slate-600">Simpan kunci sebagai variabel lingkungan <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[12px] text-indigo-700">KAMERAKITA_MCP_TOKEN</code>. Jalankan perintah ini di terminal:</p>
                                    <textarea x-ref="command" class="{{ $field }} font-mono text-[12px]" readonly rows="2">{{ $codexCommand }}</textarea>
                                    <button type="button" class="{{ $secondaryButton }}" @click="copy('command', 'Perintah')">
                                        <span x-text="copied === 'command' ? 'Perintah tersalin' : 'Salin perintah'">Salin perintah</span>
                                    </button>
                                @elseif ($client === 'chatgpt')
                                    <div class="space-y-3 rounded-[12px] border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p class="text-[13px] font-bold text-slate-900">Kamerakita &middot; Custom MCP Server</p>
                                                <p class="text-[12px] text-slate-500">Salin URL ini untuk dipasang di menu Apps / Plugins ChatGPT.</p>
                                            </div>
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
                                                <span>Streamable HTTP</span>
                                            </span>
                                        </div>
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <input x-ref="endpoint" class="{{ $field }} flex-1 bg-white font-mono text-[12px]" readonly value="{{ $endpoint }}">
                                            <button type="button" class="{{ $secondaryButton }} shrink-0" @click="copy('endpoint', 'URL')">
                                                <span x-text="copied === 'endpoint' ? 'URL tersalin' : 'Salin URL'">Salin URL</span>
                                            </button>
                                        </div>
                                    </div>
                                    <ol class="list-decimal space-y-1.5 pl-5 text-[13px] text-slate-600">
                                        <li>Buka <strong class="text-slate-900">chatgpt.com</strong> &rarr; menu <strong class="text-slate-900">Apps / Plugins</strong> &rarr; <strong class="text-slate-900">Add custom MCP server</strong>.</li>
                                        <li>Tempel URL server di atas, lalu pilih autentikasi <strong class="text-slate-900">OAuth</strong>.</li>
                                        <li>Login dan klik <strong class="text-slate-900">Izinkan akses</strong> pada layar persetujuan Kamerakita.</li>
                                    </ol>
                                @elseif ($client === 'gemini')
                                    <div class="space-y-3 rounded-[12px] border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <p class="text-[13px] font-bold text-slate-900">Kamerakita &middot; Custom MCP Server</p>
                                                <p class="text-[12px] text-slate-500">Salin URL ini untuk dipasang di Gemini Web (Connected Apps).</p>
                                            </div>
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
                                                <span>OAuth 2.1 + PKCE</span>
                                            </span>
                                        </div>
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <input x-ref="endpoint" class="{{ $field }} flex-1 bg-white font-mono text-[12px]" readonly value="{{ $endpoint }}">
                                            <button type="button" class="{{ $secondaryButton }} shrink-0" @click="copy('endpoint', 'URL')">
                                                <span x-text="copied === 'endpoint' ? 'URL tersalin' : 'Salin URL'">Salin URL</span>
                                            </button>
                                        </div>
                                    </div>
                                    <ol class="list-decimal space-y-1.5 pl-5 text-[13px] text-slate-600">
                                        <li>Buka <strong class="text-slate-900">gemini.google.com</strong> &rarr; <strong class="text-slate-900">Settings</strong> &rarr; <strong class="text-slate-900">Connected Apps</strong> &rarr; <strong class="text-slate-900">Add a custom app</strong>.</li>
                                        <li>Masukkan nama <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[12px] text-indigo-700">Kamerakita</code> dan tempel URL di atas.</li>
                                        <li>Selesaikan autentikasi OAuth 2.1 lalu klik <strong class="text-slate-900">Next</strong>.</li>
                                    </ol>
                                @else
                                    <p class="text-[13px] text-slate-600">
                                        Tambahkan konfigurasi berikut ke <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[12px]">mcp_config.json</code> (Antigravity) atau <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[12px]">.mcp.json</code> (Claude Code):
                                    </p>
                                    <textarea x-ref="command" class="{{ $field }} font-mono text-[12px]" readonly rows="9">{{ $jsonConfig }}</textarea>
                                    <button type="button" class="{{ $secondaryButton }}" @click="copy('command', 'Konfigurasi')">
                                        <span x-text="copied === 'command' ? 'Konfigurasi tersalin' : 'Salin konfigurasi'">Salin konfigurasi</span>
                                    </button>
                                @endif

                                <div class="border-t border-slate-100 pt-3 space-y-2">
                                    <p class="text-[12px] font-semibold text-slate-700">Atau minta asisten AI memasangkannya untuk Anda:</p>
                                    <textarea x-ref="prompt" class="{{ $field }} text-[12px] leading-relaxed" readonly rows="2">{{ $setupPrompt }}</textarea>
                                    <button type="button" class="{{ $secondaryButton }}" @click="copy('prompt', 'Prompt')">
                                        <span x-text="copied === 'prompt' ? 'Prompt tersalin' : 'Salin prompt'">Salin prompt</span>
                                    </button>
                                </div>
                            </div>
                        </details>

                        {{-- Example Query --}}
                        <div class="space-y-2 rounded-[12px] border border-slate-200/80 bg-slate-50/70 p-4">
                            <label class="flex items-center gap-2 text-[12px] font-semibold text-slate-900" for="mcp-example">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                <span>Coba setelah terhubung</span>
                            </label>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input id="mcp-example" x-ref="example" class="{{ $field }} flex-1 bg-white" readonly value="{{ $example }}">
                                <button type="button" class="{{ $secondaryButton }} shrink-0" @click="copy('example', 'Pertanyaan')">
                                    <span x-text="copied === 'example' ? 'Pertanyaan tersalin' : 'Salin pertanyaan'">Salin pertanyaan</span>
                                </button>
                            </div>
                        </div>

                        {{-- Advanced Custom Key Accordion --}}
                        @unless ($newToken)
                            <details class="group/adv rounded-[12px] border border-slate-200 bg-white" @if (old('mode') === 'custom') open @endif>
                                <summary class="flex min-h-[44px] cursor-pointer list-none items-center justify-between gap-3 rounded-[12px] px-4 py-3 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                        <span>Opsi lanjutan</span>
                                    </span>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 group-open/adv:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </summary>
                                <form method="POST" action="{{ route('admin.integrations.mcp.store') }}" class="space-y-4 border-t border-slate-200 p-4 sm:p-5">
                                    @csrf
                                    <input type="hidden" name="mode" value="custom">
                                    <input type="hidden" name="client" value="{{ $client }}">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="mcp-name" class="mb-1.5 block text-[12px] font-semibold text-slate-900">Nama koneksi</label>
                                            <input id="mcp-name" name="name" required maxlength="100" value="{{ old('name', $clientLabel) }}" class="{{ $field }} bg-white">
                                        </div>
                                        <div>
                                            <label for="mcp-days" class="mb-1.5 block text-[12px] font-semibold text-slate-900">Masa berlaku (hari)</label>
                                            <input id="mcp-days" type="number" name="expires_in_days" min="1" max="365" value="{{ old('expires_in_days', config('mcp.token_days', 90)) }}" class="{{ $field }} bg-white">
                                        </div>
                                    </div>
                                    <fieldset class="space-y-2">
                                        <legend class="mb-1.5 text-[12px] font-semibold text-slate-900">Data &amp; izin yang diizinkan</legend>
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            @foreach ($abilities as $ability)
                                                <label class="flex cursor-pointer items-center gap-2.5 rounded-[10px] border border-slate-200 bg-white px-3.5 py-2.5 text-[13px] text-slate-800 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                                    <input type="checkbox" name="abilities[]" value="{{ $ability }}" checked class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="font-medium">{{ config("mcp.ability_labels.$ability", $ability) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                    <button type="submit" class="{{ $secondaryButton }}">
                                        <span>Buat kunci dengan pengaturan ini</span>
                                    </button>
                                </form>
                            </details>
                        @endunless
                    @endif
                </div>
            </section>
        @else
            {{-- Connected Apps & Access Tokens --}}
            <section class="overflow-hidden rounded-[14px] border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-[16px] font-bold text-slate-900">{{ $tabs[$tab]['label'] }}</h2>
                            <span class="rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-[11px] font-bold text-indigo-700">
                                {{ ($connections?->total() ?? 0) + ($oauthTokens?->count() ?? 0) }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-[12px] text-slate-500">
                            {{ $tab === 'connected' ? 'Koneksi OAuth dan kunci akses yang sudah pernah menghubungi Kamerakita.' : 'Kelola kunci akses untuk pemasangan manual di IDE atau CLI.' }}
                        </p>
                    </div>
                </header>

                <div class="divide-y divide-slate-100">
                    @if ($tab === 'connected')
                        @foreach ($oauthTokens as $oauth)
                            <div class="flex flex-col justify-between gap-3 px-5 py-4 sm:flex-row sm:items-center">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[14px] font-semibold text-slate-900">{{ $oauth->client?->name ?? 'OAuth Client' }}</span>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-700">OAuth 2.1 Aktif</span>
                                    </div>
                                    <p class="mt-0.5 text-[12px] text-slate-500">
                                        Scopes: {{ implode(', ', $oauth->scopes ?? ['mcp:use']) }} &middot; Dibuat {{ $oauth->created_at?->diffForHumans() }}
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('admin.integrations.mcp.oauth.revoke', $oauth->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-[9px] border border-rose-200 bg-rose-50 px-3 py-1.5 text-[12px] font-semibold text-rose-700 hover:bg-rose-100">
                                        Cabut sesi
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    @endif

                    @forelse ($connections ?? [] as $conn)
                        <div class="flex flex-col justify-between gap-3 px-5 py-4 sm:flex-row sm:items-center">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[14px] font-semibold text-slate-900">{{ $conn->name }}</span>
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">{{ $conn->client_type }}</span>
                                    @if ($conn->isActive())
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-700">Aktif</span>
                                    @else
                                        <span class="rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-[10px] font-semibold text-rose-700">Dicabut / Kedaluwarsa</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-[12px] text-slate-500">
                                    {{ $conn->user?->email }} &middot;
                                    Kunci: <code class="rounded bg-slate-100 px-1">...{{ $conn->token_suffix ?? '******' }}</code> &middot;
                                    Terakhir dipakai: {{ $conn->last_used_at ? $conn->last_used_at->diffForHumans() : 'Belum pernah' }}
                                </p>
                                <div x-show="visibleTokens['{{ $conn->id }}']" class="mt-2">
                                    <code class="block rounded-[8px] border border-slate-200 bg-slate-50 px-3 py-1.5 font-mono text-[12px] text-slate-800 break-all" x-text="revealedTokens['{{ $conn->id }}']"></code>
                                </div>
                            </div>

                            @if ($conn->isActive())
                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($conn->token_encrypted)
                                        <button type="button"
                                            @click="toggleRevealToken('{{ $conn->id }}', '{{ route('admin.integrations.mcp.reveal', $conn) }}')"
                                            class="rounded-[9px] border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">
                                            <span x-text="visibleTokens['{{ $conn->id }}'] ? 'Sembunyikan' : 'Lihat'">Lihat</span>
                                        </button>
                                        <button type="button"
                                            @click="copyConnectionToken('{{ $conn->id }}', '{{ route('admin.integrations.mcp.reveal', $conn) }}')"
                                            class="rounded-[9px] border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-[12px] font-semibold text-indigo-700 hover:bg-indigo-100">
                                            <span x-text="copied === 'conn-{{ $conn->id }}' ? 'Tersalin' : 'Salin'">Salin</span>
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.integrations.mcp.destroy', $conn) }}" onsubmit="return confirm('Cabut kunci akses MCP ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="tab" value="{{ $tab }}">
                                        <button type="submit" class="rounded-[9px] border border-rose-200 bg-rose-50 px-3 py-1.5 text-[12px] font-semibold text-rose-700 hover:bg-rose-100">
                                            Cabut
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        @if ($tab !== 'connected' || $oauthTokens->isEmpty())
                            <div class="px-5 py-8 text-center text-[13px] text-slate-500">
                                Belum ada {{ $tab === 'connected' ? 'aplikasi terhubung' : 'kunci akses' }}.
                            </div>
                        @endif
                    @endforelse
                </div>

                @if ($connections && $connections->hasPages())
                    <div class="border-t border-slate-200 px-5 py-3">
                        {{ $connections->links() }}
                    </div>
                @endif
            </section>
        @endif

        {{-- Recent Audit Trail --}}
        @if ($auditLogs->isNotEmpty())
            <details class="group rounded-[14px] border border-slate-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-3.5 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">
                    <span>Riwayat Panggilan Tool Terbaru ({{ $auditLogs->count() }})</span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="overflow-x-auto border-t border-slate-200 px-5 py-3">
                    <table class="min-w-full text-left text-[12px]">
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
                                    <td class="py-2 pr-4 text-slate-500">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-2 pr-4 font-medium text-slate-800">{{ $log->client_name }}</td>
                                    <td class="py-2 pr-4 font-mono text-indigo-700">{{ $log->tool_name ?? $log->method }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="rounded px-2 py-0.5 text-[11px] font-semibold {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-slate-500">{{ $log->execution_time_ms }} ms</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endif
    </div>
</x-app-layout>
