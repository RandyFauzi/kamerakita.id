<x-mailbox-layout>
    <div x-data="mailboxApp()" class="flex w-full h-full bg-white text-slate-800">
        
        <!-- Sidebar (Left Column) -->
        <div class="w-64 flex-shrink-0 border-r border-slate-100 bg-[#F9FAFB] flex flex-col justify-between hidden md:flex">
            <div>
                <!-- Header / Logo -->
                <div class="h-20 flex items-center px-8 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl overflow-hidden shrink-0 flex items-center justify-center bg-white shadow-sm border border-slate-100">
                            <img src="{{ asset('images/Logo.webp') }}" alt="Kamerakita.ai" class="max-h-6 max-w-6 object-contain">
                        </span>
                        <div class="flex flex-col">
                            <span class="font-bold text-[15px] tracking-tight leading-snug text-slate-800">Mailbox</span>
                            <span class="font-bold text-[15px] tracking-tight leading-snug text-slate-800">KameraKita AI</span>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="p-6">


                    <a href="{{ route('mailbox.index') }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl shadow-sm transition-all duration-200 flex justify-center items-center gap-2 mb-8">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Refresh Newest
                    </a>

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
            
            <!-- List Header -->
            <div class="px-4 py-3 flex items-center justify-between border-b border-slate-200 shrink-0 bg-[#e2e4e7]">
                <h2 class="text-lg font-bold text-slate-800">Kotak Masuk</h2>
                <div class="flex items-center gap-2 text-slate-500">
                    <button class="hover:text-slate-800 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg></button>
                    <div class="flex items-center text-xs">
                        <button class="opacity-50 hover:opacity-100 p-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                        <span class="px-1 font-medium">1/1</span>
                        <button class="opacity-50 hover:opacity-100 p-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                    </div>
                </div>
            </div>

            <!-- List Filter / Action Bar -->
            <div class="px-4 py-2.5 border-b border-slate-300 flex items-center overflow-x-auto bg-[#e2e4e7] shrink-0 min-h-[44px]">
                <!-- Checkbox -->
                <div class="flex items-center gap-1 shrink-0 text-slate-500 cursor-pointer hover:text-slate-700 mr-3">
                    <input type="checkbox" :checked="allChecked" @change="toggleAll()" class="rounded border-slate-400 text-indigo-600 focus:ring-indigo-600 w-4 h-4 cursor-pointer bg-white">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                
                <!-- Filters (Show when no items checked) -->
                <div x-show="checkedEmails.length === 0" class="flex items-center gap-2 shrink-0 transition-opacity duration-200">
                    <button @click="filterMode = 'all'" :class="filterMode === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300 border border-slate-300/50'" class="px-3.5 py-1 rounded-full text-xs font-semibold transition-colors">Semua email</button>
                    <button @click="filterMode = 'unread'" :class="filterMode === 'unread' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300 border border-slate-300/50'" class="px-3.5 py-1 rounded-full text-xs font-semibold transition-colors">Belum dibaca</button>
                    <button @click="filterMode = 'read'" :class="filterMode === 'read' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300 border border-slate-300/50'" class="px-3.5 py-1 rounded-full text-xs font-semibold transition-colors">Dibaca</button>
                    <button @click="filterMode = 'starred'" :class="filterMode === 'starred' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300 border border-slate-300/50'" class="px-3.5 py-1 rounded-full text-xs font-semibold transition-colors">Berbintang</button>
                </div>

                <!-- Actions (Show when items checked) -->
                <div x-show="checkedEmails.length > 0" x-cloak class="flex items-center gap-3 shrink-0 text-slate-700 transition-opacity duration-200" style="display: none;">
                    <button class="p-1 hover:bg-slate-300 rounded transition-colors text-slate-700" title="Tandai Belum Dibaca"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></button>
                    <button class="p-1 hover:bg-slate-300 rounded transition-colors text-slate-700" title="Tandai Spam"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></button>
                    <button class="p-1 hover:bg-slate-300 rounded transition-colors text-slate-700" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    <button class="p-1 hover:bg-slate-300 rounded transition-colors text-slate-700" title="Pindahkan"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button>
                    <button class="p-1 hover:bg-slate-300 rounded transition-colors text-slate-700" title="Unduh"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg></button>
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="px-4 py-2 border-b border-slate-300 bg-[#e2e4e7] shrink-0">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input x-model="search" type="text" class="block w-full pl-8 pr-3 py-1.5 border border-slate-300 rounded bg-[#f3f4f6] text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-400 text-xs transition-colors" placeholder="Telusuri...">
                </div>
            </div>

            <!-- List Items -->
            <div class="flex-1 overflow-y-auto bg-[#e2e4e7]">
                <template x-for="email in filteredEmails" :key="email.id">
                    <div @click="selectEmail(email.id)" 
                         :class="{'bg-[#d2d4d9]': selectedEmail && selectedEmail.id === email.id, 'hover:bg-[#dadae0]': !selectedEmail || selectedEmail.id !== email.id}"
                         class="px-4 py-3 border-b border-slate-300/60 cursor-pointer transition-colors relative flex items-start gap-3 group">
                        
                        <!-- Checkbox -->
                        <div class="pt-0.5 shrink-0" @click.stop>
                            <input type="checkbox" :value="email.id" x-model="checkedEmails" class="rounded border-slate-400 text-indigo-600 focus:ring-indigo-600 w-4 h-4 cursor-pointer bg-white">
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h3 class="text-[13px] text-slate-800 truncate pr-2" :class="email.is_read ? '' : 'font-bold'" x-text="email.sender_address.split('@')[0]"></h3>
                                <div class="flex items-center shrink-0 relative h-5">
                                    <div class="flex items-center gap-1.5 transition-opacity group-hover:opacity-0" :class="{'opacity-0': checkedEmails.includes(email.id)}">
                                        <span class="text-[11px] text-slate-500 whitespace-nowrap" x-text="formatDate(email.received_at)"></span>
                                        <!-- Unread Dot Indicator -->
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-600" x-show="!email.is_read"></div>
                                        <div class="w-1.5 h-1.5" x-show="email.is_read"></div>
                                    </div>
                                    <div class="absolute right-0 top-0 h-full flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity pl-2"
                                         :class="{
                                             'bg-[#d2d4d9]': selectedEmail && selectedEmail.id === email.id, 
                                             'bg-[#e2e4e7] group-hover:bg-[#dadae0]': !selectedEmail || selectedEmail.id !== email.id,
                                             'opacity-100': checkedEmails.includes(email.id)
                                         }">
                                        <button @click.stop="toggleRead(email.id, !email.is_read)" class="text-slate-500 hover:text-slate-800 focus:outline-none p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </button>
                                        <button class="text-slate-500 hover:text-slate-800 focus:outline-none p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <button class="text-slate-500 hover:text-slate-800 focus:outline-none p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        </button>
                                        <button @click.stop="toggleStar(email.id)" class="text-slate-400 hover:text-slate-600 focus:outline-none p-1" :class="email.is_starred ? 'text-slate-800 fill-current' : ''">
                                            <svg class="w-4 h-4" :fill="email.is_starred ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-start mt-0.5">
                                <p class="text-[13px] text-slate-600 truncate pr-2" :class="email.is_read ? '' : 'font-bold text-slate-800'" x-text="email.subject || '(Tanpa Subjek)'"></p>
                            </div>
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
            <!-- Global Right Top Bar -->
            <div class="h-20 flex items-center justify-between px-6 lg:px-8 border-b border-slate-100 shrink-0 bg-white z-20 relative">
                <!-- Left contextual actions -->
                <div class="flex items-center gap-2">
                    <button class="md:hidden p-2 -ml-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50" x-show="selectedEmail" @click="selectedEmail = null" style="display: none;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    
                    <template x-if="selectedEmail">
                        <div class="flex items-center gap-2 hidden md:flex">
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></button>
                            <div class="w-px h-6 bg-slate-200 mx-1"></div>
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg></button>
                            <button class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></button>
                        </div>
                    </template>
                </div>
                
                <!-- Right contextual actions (User Profile) -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-full transition-colors relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>
                    
                    <div x-data="{ open: false }" class="relative">
                        <div @click="open = !open" class="flex items-center gap-3 cursor-pointer hover:bg-slate-50 p-1 pr-2 rounded-full transition-colors">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff" alt="Avatar" class="w-8 h-8 rounded-full border border-slate-200">
                            <div class="hidden lg:block text-left mr-1">
                                <p class="text-xs font-bold text-slate-800 leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-slate-500 leading-tight">{{ Auth::user()->email }}</p>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 hidden lg:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        
                        <div x-show="open" @click.away="open = false" x-cloak style="display: none;" class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-50 overflow-hidden">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 font-medium transition-colors">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 flex flex-col relative overflow-hidden">
                <template x-if="selectedEmail">
                    <div class="h-full flex flex-col animate-entrance">
                    
                    <div class="flex-1 overflow-y-auto p-6 md:p-8 bg-slate-50">
                        <!-- Subject -->
                        <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight mb-6" x-text="selectedEmail.subject || '(Tanpa Subjek)'"></h1>
                        
                        <!-- Email Container -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                            <!-- Email Header -->
                            <div class="p-6 pb-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="text-sm text-slate-900 truncate">
                                            <span class="font-bold">Dari</span> 
                                            <span class="text-slate-500">&lt;<span x-text="selectedEmail.sender_address"></span>&gt;</span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                            <span x-text="`{{ auth()->user()->email }} `"></span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-xs text-slate-500 hidden sm:inline" x-text="formatFullDate(selectedEmail.received_at)"></span>
                                        <div class="flex items-center gap-1 text-slate-400">
                                            <button class="hover:bg-slate-100 p-1.5 rounded-md transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></button>
                                            
                                            <!-- 3 Dots Dropdown -->
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" @click.away="open = false" class="hover:bg-slate-100 p-1.5 rounded-md transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg></button>
                                                
                                                <div x-show="open" style="display: none;" class="absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-50 overflow-hidden">
                                                    <button @click="window.print()" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                        Cetak Pesan
                                                    </button>
                                                    <button @click="navigator.clipboard.writeText(selectedEmail.sender_address)" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                        Salin Pengirim
                                                    </button>
                                                    <div class="h-px bg-slate-100 my-1"></div>
                                                    <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 font-medium">
                                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        Hapus Pesan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="border-slate-100">
                            
                            <!-- Body -->
                            <div class="p-6 overflow-x-auto">
                                <div class="prose prose-slate max-w-none text-slate-800 text-sm leading-relaxed [&_blockquote]:border-l-[3px] [&_blockquote]:border-slate-300 [&_blockquote]:pl-3 [&_blockquote]:text-slate-600 [&_blockquote]:my-1.5 [&_blockquote]:ml-1" x-html="formatEmailBody(selectedEmail.message_content)"></div>
                            </div>
                        </div>

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
    
    <script>
        function mailboxApp() {
            return {
                selectedEmail: null,
                emails: {{ Js::from($emails) }},
                search: '',
                filterMode: 'all',
                checkedEmails: [],
                get allChecked() {
                    return this.filteredEmails.length > 0 && this.checkedEmails.length === this.filteredEmails.length;
                },
                toggleAll() {
                    if (this.allChecked) {
                        this.checkedEmails = [];
                    } else {
                        this.checkedEmails = this.filteredEmails.map(e => e.id);
                    }
                },
                get filteredEmails() {
                    let list = this.emails;
                    if (this.search !== '') {
                        list = list.filter(e => e.subject?.toLowerCase().includes(this.search.toLowerCase()) || e.sender_address.toLowerCase().includes(this.search.toLowerCase()));
                    }
                    if (this.filterMode === 'unread') {
                        list = list.filter(e => !e.is_read);
                    } else if (this.filterMode === 'read') {
                        list = list.filter(e => e.is_read);
                    } else if (this.filterMode === 'starred') {
                        list = list.filter(e => e.is_starred);
                    }
                    return list;
                },
                selectEmail(id) {
                    this.selectedEmail = this.emails.find(e => e.id === id);
                    if (this.selectedEmail && !this.selectedEmail.is_read) {
                        this.toggleRead(id, true);
                    }
                },
                toggleRead(id, status) {
                    let email = this.emails.find(e => e.id === id);
                    if (email) {
                        email.is_read = status;
                        fetch(`/mailbox/${id}/read`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({ is_read: status })
                        }).catch(console.error);
                    }
                },
                toggleStar(id) {
                    let email = this.emails.find(e => e.id === id);
                    if (email) {
                        email.is_starred = !email.is_starred;
                        fetch(`/mailbox/${id}/star`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({ is_starred: email.is_starred })
                        }).catch(console.error);
                    }
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
                },
                formatEmailBody(content) {
                    if (!content) return '';
                    
                    // If it already contains common HTML structure tags, return as-is
                    if (/<(br|p|div|html|body|table|a|span|blockquote)[^>]*>/i.test(content)) {
                        return content;
                    }

                    // Otherwise, parse plain text into HTML with blockquotes
                    let escaped = content
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;");

                    let lines = escaped.split(/\r?\n/);
                    let html = '';
                    let quoteDepth = 0;

                    for (let line of lines) {
                        let match = line.match(/^(?:(?:&gt;)\s*)+/);
                        let currentDepth = 0;
                        if (match) {
                            currentDepth = (match[0].match(/&gt;/g) || []).length;
                            line = line.substring(match[0].length);
                        }

                        while (quoteDepth < currentDepth) {
                            html += `<div class="border-l-[3px] border-slate-300 pl-3 my-1.5 ml-1 text-slate-600 text-[13px]">`;
                            quoteDepth++;
                        }
                        while (quoteDepth > currentDepth) {
                            html += `</div>`;
                            quoteDepth--;
                        }

                        html += line + '<br>';
                    }

                    while (quoteDepth > 0) {
                        html += `</div>`;
                        quoteDepth--;
                    }

                    return html;
                }
            };
        }
    </script>
</x-mailbox-layout>
