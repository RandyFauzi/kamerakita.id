<x-mailbox-layout>
    <div x-data="{ 
            selectedEmail: null,
            emails: {{ Js::from($emails) }},
            search: '',
            get filteredEmails() {
                if (this.search === '') return this.emails;
                return this.emails.filter(e => e.subject?.toLowerCase().includes(this.search.toLowerCase()) || e.sender_address.toLowerCase().includes(this.search.toLowerCase()));
            },
            selectEmail(id) {
                this.selectedEmail = this.emails.find(e => e.id === id);
            },
            formatDate(dateStr) {
                const d = new Date(dateStr);
                const today = new Date();
                if (d.toDateString() === today.toDateString()) {
                    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                }
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            },
            formatFullDate(dateStr) {
                return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            }
        }" 
        class="flex w-full h-full bg-white text-slate-800">
        
        <!-- Sidebar (Left Column) -->
        <div class="w-64 flex-shrink-0 border-r border-slate-100 bg-[#F9FAFB] flex flex-col justify-between hidden md:flex">
            <div>
                <!-- Header / Logo -->
                <div class="h-20 flex items-center px-8 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-indigo-200 shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="font-bold text-lg tracking-tight">KameraKita</span>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="p-6">
                    <!-- User Email Highlight -->
                    <div class="mb-6 bg-indigo-50/50 border border-indigo-100 rounded-xl p-3 flex items-center gap-3 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shrink-0 border border-indigo-100 text-indigo-600 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-0.5">Alamat Email Anda</p>
                            <p class="text-sm font-semibold text-slate-800 truncate" title="{{ Auth::user()->email }}">{{ Auth::user()->email }}</p>
                        </div>
                    </div>

                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-indigo-200 transition-all flex justify-center items-center gap-2 mb-8" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        New message
                    </button>

                    <nav class="space-y-1 text-sm font-medium">
                        <a href="#" class="flex items-center justify-between px-4 py-2.5 rounded-lg bg-indigo-50 text-indigo-700">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                Inbox
                            </div>
                            <span class="bg-indigo-100 text-indigo-700 py-0.5 px-2 rounded-full text-xs" x-text="emails.length"></span>
                        </a>
                        <a href="#" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                Marked
                            </div>
                        </a>
                    </nav>
                </div>
            </div>
            
            <div class="p-6 border-t border-slate-100">
                <a href="{{ route('dashboard') }}" class="flex items-center justify-center gap-2 w-full text-sm font-semibold text-slate-500 hover:text-slate-900 bg-white border border-slate-200 py-2.5 rounded-lg shadow-sm transition-all hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Email List (Middle Column) -->
        <div class="w-full md:w-80 lg:w-96 flex-shrink-0 border-r border-slate-100 flex flex-col bg-white z-10" :class="{'hidden md:flex': selectedEmail}">
            
            <!-- List Header & Search -->
            <div class="h-20 flex items-center px-6 border-b border-slate-100 shrink-0 gap-4">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input x-model="search" type="text" class="block w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors" placeholder="Search emails...">
                </div>
            </div>

            <!-- List Filter -->
            <div class="flex items-center px-6 py-3 border-b border-slate-50 bg-[#FBFBFC] text-xs font-semibold text-slate-400 shrink-0">
                <div class="flex gap-4">
                    <span class="text-indigo-600 border-b-2 border-indigo-600 pb-1 cursor-pointer">Newest</span>
                    <span class="hover:text-slate-600 cursor-pointer pb-1">Unread</span>
                </div>
            </div>

            <!-- List Items -->
            <div class="flex-1 overflow-y-auto">
                <template x-for="email in filteredEmails" :key="email.id">
                    <div @click="selectEmail(email.id)" 
                         :class="{'bg-indigo-50/50 border-l-2 border-indigo-600': selectedEmail && selectedEmail.id === email.id, 'border-l-2 border-transparent hover:bg-slate-50': !selectedEmail || selectedEmail.id !== email.id}"
                         class="p-5 border-b border-slate-100 cursor-pointer transition-colors relative group">
                        
                        <div class="flex justify-between items-start mb-1">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold" x-text="email.sender_address.charAt(0).toUpperCase()"></div>
                                <h3 class="text-sm font-semibold text-slate-800 truncate max-w-[120px] lg:max-w-[150px]" x-text="email.sender_address.split('@')[0]"></h3>
                            </div>
                            <span class="text-[10px] font-medium text-slate-400 whitespace-nowrap" x-text="formatDate(email.received_at)"></span>
                        </div>
                        
                        <div class="pl-8">
                            <p class="text-sm font-bold text-slate-900 mb-1 truncate group-hover:text-indigo-600 transition-colors" x-text="email.subject || '(No Subject)'"></p>
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed" x-text="email.message_content.replace(/(<([^>]+)>)/gi, '').substring(0, 100) + '...'"></p>
                        </div>
                    </div>
                </template>
                
                <template x-if="filteredEmails.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center p-8">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500">Inbox Kosong</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Email Reader (Right Column) -->
        <div class="flex-1 bg-white flex flex-col relative" :class="{'hidden md:flex': !selectedEmail}">
            <template x-if="selectedEmail">
                <div class="h-full flex flex-col animate-entrance">
                    
                    <!-- Top Action Bar -->
                    <div class="h-20 flex items-center justify-between px-8 border-b border-slate-100 shrink-0">
                        <button class="md:hidden p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50" @click="selectedEmail = null">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        
                        <div class="flex items-center gap-2 hidden md:flex">
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg></button>
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></button>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-8 lg:p-12">
                        <!-- Date Header -->
                        <div class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-4 before:h-px before:flex-1 before:bg-slate-100 after:h-px after:flex-1 after:bg-slate-100">
                            <span x-text="formatFullDate(selectedEmail.received_at)"></span>
                        </div>
                        
                        <!-- Reader Header -->
                        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-6" x-text="selectedEmail.subject || '(Tanpa Subjek)'"></h1>
                        
                        <!-- Tags -->
                        <div class="flex gap-2 mb-8">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-xs font-semibold rounded-md border border-indigo-100">Internal</span>
                            <span class="px-3 py-1 bg-slate-50 text-slate-500 text-xs font-semibold rounded-md border border-slate-200">System</span>
                        </div>
                        
                        <!-- Body -->
                        <div class="prose prose-slate prose-sm max-w-none text-slate-700 leading-loose" x-html="selectedEmail.message_content"></div>
                        
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!selectedEmail">
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-[#FAFAFA]">
                    <div class="w-24 h-24 mb-6 rounded-full bg-white shadow-sm ring-1 ring-slate-900/5 flex items-center justify-center">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Pilih Pesan</h3>
                    <p class="text-slate-500 text-sm max-w-xs">Pilih email di kolom tengah untuk membaca isi pesan sepenuhnya di sini.</p>
                </div>
            </template>
        </div>
    </div>
    
    <style>
        .animate-entrance {
            animation: fadeIn 0.3s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-mailbox-layout>
