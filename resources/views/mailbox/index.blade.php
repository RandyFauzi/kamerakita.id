<x-mailbox-layout>
    <div x-data="mailboxApp()" class="flex w-full h-full bg-white text-slate-800 relative">
        
        <!-- Toast Notification -->
        <div x-show="toast.show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white"
             :class="toast.type === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
             style="display: none;">
            <span x-text="toast.message"></span>
        </div>

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
                    <!-- Logged in User Info -->
                    <div class="mb-6 px-1">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Logged in as</div>
                        <div class="text-[13px] font-semibold text-slate-700 truncate" title="{{ auth()->user()->email }}">
                            {{ auth()->user()->email }}
                        </div>
                    </div>

                    <button @click="fetchEmails(1)" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl shadow-sm transition-all duration-200 flex justify-center items-center gap-2 mb-8">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Refresh Newest
                    </button>

                    <nav class="space-y-1 text-sm font-medium">
                        <button @click="setFilter('all')" :class="filterMode === 'all' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                All Inbox
                            </div>
                        </button>
                        <button @click="setFilter('unread')" :class="filterMode === 'unread' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Unread
                            </div>
                        </button>
                        <button @click="setFilter('starred')" :class="filterMode === 'starred' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 opacity-70 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Starred
                            </div>
                        </button>
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

        <!-- Email List -->
        <div class="w-full md:w-[380px] lg:w-[420px] bg-slate-50 flex flex-col border-r border-slate-200 shrink-0 relative z-10" :class="{'hidden md:flex': selectedEmailId}">
            
            <!-- List Header -->
            <div class="h-20 px-6 flex items-center justify-between border-b border-slate-200 bg-white shrink-0">
                <h1 class="text-xl font-bold text-slate-800 tracking-tight" x-text="filterMode === 'unread' ? 'Unread' : (filterMode === 'starred' ? 'Starred' : 'Inbox')"></h1>
                <div class="flex items-center gap-2">
                    <button @click="fetchEmails(1)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Refresh">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </div>

            <!-- Search & Actions -->
            <div class="p-4 bg-white border-b border-slate-100 flex flex-col gap-3 shrink-0 shadow-sm z-10">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" @keydown.enter="fetchEmails(1)" placeholder="Search emails... (Enter to search)" class="w-full pl-10 pr-4 py-2.5 bg-[#F9FAFB] border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400">
                </div>
            </div>

            <!-- The List -->
            <div class="flex-1 overflow-y-auto min-h-0 bg-slate-50">
                <div x-show="isLoadingList" class="flex justify-center p-8">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <template x-if="!isLoadingList && emails.length === 0">
                    <div class="flex flex-col items-center justify-center h-full p-8 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <h3 class="text-slate-800 font-semibold mb-1">No emails found</h3>
                        <p class="text-sm text-slate-500">You're all caught up!</p>
                    </div>
                </template>

                <div class="divide-y divide-slate-100">
                    <template x-for="email in emails" :key="email.id">
                        <div @click="selectEmail(email.id)" 
                             class="group flex gap-3 p-4 cursor-pointer transition-all border-l-4"
                             :class="selectedEmailId === email.id ? 'bg-indigo-50/50 border-indigo-600' : (email.is_read ? 'bg-white border-transparent hover:bg-slate-50' : 'bg-white border-transparent hover:bg-slate-50')">
                            
                            <!-- Star Action -->
                            <button @click.stop="toggleStar(email.id)" class="pt-1 text-slate-300 hover:text-yellow-400 transition-colors" :class="{'text-yellow-400': email.is_starred}">
                                <svg class="w-5 h-5" :fill="email.is_starred ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </button>

                            <div class="flex-1 min-w-0 flex flex-col gap-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-[14px] truncate" :class="email.is_read ? 'text-slate-700' : 'text-slate-900 font-bold'" x-text="email.sender_address"></h3>
                                    <span class="text-[11px] whitespace-nowrap shrink-0" :class="email.is_read ? 'text-slate-400' : 'text-indigo-600 font-semibold'" x-text="formatDate(email.received_at)"></span>
                                </div>
                                <p class="text-[13px] truncate" :class="email.is_read ? 'text-slate-500' : 'text-slate-800 font-medium'" x-text="email.subject || '(No Subject)'"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Pagination -->
            <div class="p-4 bg-white border-t border-slate-200 flex items-center justify-between text-sm text-slate-500" x-show="totalPages > 1">
                <button @click="fetchEmails(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 bg-slate-100 rounded disabled:opacity-50">Prev</button>
                <span x-text="`Page ${currentPage} of ${totalPages}`"></span>
                <button @click="fetchEmails(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 bg-slate-100 rounded disabled:opacity-50">Next</button>
            </div>
        </div>

        <!-- Viewer (Right Column) -->
        <div class="flex-1 flex flex-col min-w-0 bg-white relative z-20" :class="{'hidden md:flex': !selectedEmailId}">
            
            <template x-if="!selectedEmailId">
                <div class="flex-1 flex flex-col items-center justify-center bg-slate-50/50">
                    <div class="w-24 h-24 bg-white rounded-full shadow-sm flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 mb-2">No email selected</h2>
                    <p class="text-slate-500">Select an email from the list to read it here.</p>
                </div>
            </template>

            <template x-if="selectedEmailId">
                <div class="flex-1 flex flex-col min-h-0 bg-white">
                    <!-- Viewer Header -->
                    <div class="h-20 px-6 flex items-center justify-between border-b border-slate-100 shrink-0 bg-white">
                        <div class="flex items-center gap-4">
                            <!-- Mobile Back Button -->
                            <button @click="selectedEmailId = null" class="md:hidden p-2 -ml-2 text-slate-400 hover:text-slate-600 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <h2 class="text-xl font-bold text-slate-800 truncate" x-text="selectedEmailData ? (selectedEmailData.subject || '(No Subject)') : 'Loading...'"></h2>
                        </div>
                    </div>

                    <!-- Email Metadata -->
                    <div x-show="selectedEmailData" class="px-8 py-6 border-b border-slate-100 shrink-0 bg-white">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-lg shrink-0">
                                    <span x-text="selectedEmailData ? selectedEmailData.sender_address.charAt(0).toUpperCase() : ''"></span>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-900 text-[15px]" x-text="selectedEmailData ? selectedEmailData.sender_address : ''"></div>
                                    <div class="text-sm text-slate-500" x-text="selectedEmailData ? formatFullDate(selectedEmailData.received_at) : ''"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Body -->
                    <div class="flex-1 overflow-y-auto bg-white p-8">
                        <div x-show="isLoadingBody" class="flex justify-center p-8">
                            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        
                        <div x-show="!isLoadingBody && selectedEmailData" 
                             class="prose prose-slate max-w-none text-[15px] leading-relaxed"
                             x-html="selectedEmailData ? selectedEmailData.sanitized_content : ''">
                        </div>
                    </div>
                </div>
            </template>
        </div>

    </div>

    <script>
        function mailboxApp() {
            return {
                emails: [],
                currentPage: 1,
                totalPages: 1,
                search: '',
                filterMode: 'all',
                selectedEmailId: null,
                selectedEmailData: null,
                isLoadingList: false,
                isLoadingBody: false,
                toast: { show: false, message: '', type: 'error' },

                init() {
                    this.fetchEmails(1);
                },

                setFilter(mode) {
                    this.filterMode = mode;
                    this.fetchEmails(1);
                },

                fetchEmails(page) {
                    this.isLoadingList = true;
                    this.currentPage = page;
                    
                    let url = `/mailbox/api/emails?page=${page}&filter=${this.filterMode}`;
                    if (this.search.trim() !== '') {
                        url += `&search=${encodeURIComponent(this.search)}`;
                    }

                    fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.emails = data.data;
                        this.currentPage = data.current_page;
                        this.totalPages = data.last_page;
                        this.isLoadingList = false;
                    })
                    .catch(err => {
                        this.isLoadingList = false;
                        this.showToast("Gagal memuat list email.", "error");
                    });
                },

                selectEmail(id) {
                    this.selectedEmailId = id;
                    this.selectedEmailData = null;
                    this.isLoadingBody = true;

                    // Mark as read immediately in UI
                    let emailObj = this.emails.find(e => e.id === id);
                    if (emailObj && !emailObj.is_read) {
                        this.toggleRead(id, true);
                    }

                    fetch(`/mailbox/api/emails/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.selectedEmailData = data;
                        this.isLoadingBody = false;
                    })
                    .catch(err => {
                        this.isLoadingBody = false;
                        this.showToast("Gagal memuat konten email.", "error");
                    });
                },

                showToast(message, type = 'error') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => { this.toast.show = false; }, 3500);
                },

                toggleRead(id, status) {
                    let email = this.emails.find(e => e.id === id);
                    if (email) {
                        email.is_read = status;
                        fetch(`/mailbox/${id}/read`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({ is_read: status })
                        }).catch(err => {
                            email.is_read = !status;
                        });
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
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({ is_starred: email.is_starred })
                        }).catch(err => {
                            email.is_starred = !email.is_starred;
                        });
                    }
                },

                formatDate(dateStr) {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    if (isNaN(d.getTime())) return '-';
                    
                    const today = new Date();
                    if (d.toDateString() === today.toDateString()) {
                        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    }
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                },

                formatFullDate(dateStr) {
                    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                }
            };
        }
    </script>
</x-mailbox-layout>
