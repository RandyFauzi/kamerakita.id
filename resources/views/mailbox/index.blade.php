<x-mailbox-layout>
    <div x-data="{ 
            selectedEmail: null,
            emails: {{ Js::from($emails) }},
            selectEmail(id) {
                this.selectedEmail = this.emails.find(e => e.id === id);
            },
            formatDate(dateStr) {
                return new Date(dateStr).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
                });
            }
        }" 
        class="flex w-full max-w-[1400px] mx-auto overflow-hidden shadow-2xl bg-white/70 backdrop-blur-xl ring-1 ring-slate-900/5 sm:rounded-3xl m-4 h-[calc(100vh-2rem)]">
        
        <!-- Sidebar (List) -->
        <div class="w-1/3 min-w-[320px] max-w-[400px] border-r border-slate-200/60 bg-white/40 flex flex-col">
            <div class="p-6 border-b border-slate-200/60 backdrop-blur-md flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight font-outfit">Internal Mailbox</h2>
                    <p class="text-xs text-slate-500 mt-1">{{ Auth::user()->email }}</p>
                </div>
                <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 text-slate-500 hover:text-slate-700 transition-colors" title="Kembali ke Dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                <template x-for="email in emails" :key="email.id">
                    <div @click="selectEmail(email.id)" 
                         :class="{'bg-indigo-50/80 ring-1 ring-indigo-500/20': selectedEmail && selectedEmail.id === email.id, 'hover:bg-white/60': !selectedEmail || selectedEmail.id !== email.id}"
                         class="p-4 rounded-2xl cursor-pointer transition-all duration-200 group">
                        <div class="flex justify-between items-baseline mb-1">
                            <h3 class="text-sm font-bold text-slate-800 truncate pr-2 group-hover:text-indigo-600 transition-colors" x-text="email.sender_address"></h3>
                            <span class="text-[10px] font-medium text-slate-400 whitespace-nowrap" x-text="formatDate(email.received_at)"></span>
                        </div>
                        <p class="text-xs font-semibold text-slate-700 mb-1 truncate" x-text="email.subject || '(Tanpa Subjek)'"></p>
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed" x-text="email.message_content.replace(/(<([^>]+)>)/gi, '')"></p>
                    </div>
                </template>
                
                <template x-if="emails.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center p-6">
                        <div class="w-16 h-16 mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-600">Belum ada pesan</p>
                        <p class="text-xs text-slate-400 mt-1">Pesan yang dikirim ke email internal Anda akan muncul di sini.</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Main Content (Reader) -->
        <div class="flex-1 bg-white/80 flex flex-col relative overflow-hidden">
            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#6366f1 1px, transparent 1px); background-size: 24px 24px;"></div>
            
            <template x-if="selectedEmail">
                <div class="h-full flex flex-col relative z-10">
                    <div class="p-8 border-b border-slate-100 bg-white/50 backdrop-blur-md shrink-0">
                        <h1 class="text-2xl font-bold text-slate-900 font-outfit mb-4" x-text="selectedEmail.subject || '(Tanpa Subjek)'"></h1>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-md ring-2 ring-white" x-text="selectedEmail.sender_address.charAt(0).toUpperCase()"></div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800" x-text="selectedEmail.sender_address"></p>
                                    <p class="text-xs text-slate-500">ke saya</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-slate-400 bg-slate-100 px-3 py-1 rounded-full" x-text="formatDate(selectedEmail.received_at)"></span>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-8 text-sm text-slate-700 leading-relaxed max-w-4xl">
                        <div class="prose prose-slate prose-sm max-w-none" x-html="selectedEmail.message_content"></div>
                    </div>
                </div>
            </template>

            <template x-if="!selectedEmail">
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 relative z-10">
                    <div class="w-24 h-24 mb-6 rounded-full bg-indigo-50 flex items-center justify-center ring-8 ring-white shadow-sm">
                        <svg class="w-10 h-10 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 font-outfit mb-2">Pilih Pesan</h3>
                    <p class="text-slate-500 text-sm max-w-xs">Pilih salah satu pesan di panel sebelah kiri untuk membaca isi lengkapnya.</p>
                </div>
            </template>
        </div>
    </div>
</x-mailbox-layout>
