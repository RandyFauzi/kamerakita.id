<div x-data="{
        isOpen: false,
        inputText: '',
        isLoading: false,
        messages: [
            { role: 'model', text: 'Halo Admin! Saya KameraBot. Ada data operasional yang perlu saya cek atau eksekusi hari ini?' }
        ],
        init() {
            const saved = localStorage.getItem('kameraBotMessages');
            if (saved) {
                this.messages = JSON.parse(saved);
            }
            this.$watch('messages', value => {
                localStorage.setItem('kameraBotMessages', JSON.stringify(value));
            });
        },
        clearHistory() {
            if (confirm('Bersihkan riwayat percakapan?')) {
                this.messages = [
                    { role: 'model', text: 'Halo Admin! Saya KameraBot. Ada data operasional yang perlu saya cek atau eksekusi hari ini?' }
                ];
            }
        },
        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                setTimeout(() => this.scrollToBottom(), 100);
            }
        },
        scrollToBottom() {
            if (this.$refs.messagesContainer) {
                this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
            }
        },
        formatMessage(text) {
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        },
        async sendMessage() {
            const text = this.inputText.trim();
            if (!text) return;
            
            // Ambil histori (sebelum pesan baru ditambahkan)
            const chatHistory = JSON.parse(JSON.stringify(this.messages));

            this.messages.push({ role: 'user', text: text });
            this.inputText = '';
            this.isLoading = true;
            this.scrollToBottom();

            try {
                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '';
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                };
                if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

                const response = await fetch('/admin-assistant', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ 
                        message: text,
                        history: chatHistory 
                    })
                });

                const data = await response.json();
                
                if (response.status === 429) {
                    this.messages.push({ role: 'model', text: data.reply || 'Sistem sedang memproses terlalu banyak data dalam satu menit. Mohon tunggu sekitar 30 detik sebelum memberikan perintah baru.' });
                } else if (!response.ok) {
                    this.messages.push({ role: 'model', text: data.reply || data.message || 'Maaf, terjadi kesalahan saat menghubungi server.' });
                } else {
                    this.messages.push({ role: 'model', text: data.reply });
                }
            } catch (error) {
                this.messages.push({ role: 'model', text: 'Koneksi terputus. Gagal menghubungi asisten.' });
            } finally {
                this.isLoading = false;
                setTimeout(() => this.scrollToBottom(), 100);
            }
        }
    }"  
    class="fixed bottom-6 right-6 z-[9999] flex flex-col items-end gap-3 font-sans"
>

    <!-- Chat Panel -->
    <div x-show="isOpen" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-200 transform origin-bottom-right"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150 transform origin-bottom-right"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="w-[380px] max-w-[calc(100vw-2rem)] bg-white/95 backdrop-blur-xl border border-gray-200 shadow-2xl rounded-2xl overflow-hidden flex flex-col h-[550px] max-h-[75vh] ring-1 ring-indigo-900/5">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-4 flex justify-between items-center shrink-0 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-1.5 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm tracking-wide">KameraBot</h3>
                    <p class="text-[10px] text-indigo-100 font-medium">AI Admin Command Center</p>
                </div>
            </div>
            <div class="flex gap-1">
                <button @click="clearHistory" title="Bersihkan Obrolan" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg p-1.5 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
                <button @click="isOpen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg p-1.5 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Messages Area -->
        <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50/50">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-sm' : 'bg-white shadow-sm border border-gray-100 text-gray-800 rounded-2xl rounded-tl-sm'" class="px-4 py-2.5 max-w-[85%] text-sm leading-relaxed" x-html="formatMessage(msg.text)">
                    </div>
                </div>
            </template>
            
            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex justify-start" style="display: none;">
                <div class="bg-white shadow-sm border border-gray-100 text-gray-500 rounded-2xl rounded-tl-sm px-4 py-3 flex space-x-1.5 items-center">
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-t border-gray-100 shrink-0">
            <form @submit.prevent="sendMessage" class="flex gap-2 relative">
                <input x-model="inputText" type="text" placeholder="Ketik instruksi untuk KameraBot..." class="flex-1 bg-gray-50 border border-gray-200/80 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400" :disabled="isLoading">
                <button type="submit" :disabled="isLoading || inputText.trim() === ''" class="bg-indigo-600 text-white rounded-xl px-4 py-3 flex items-center justify-center hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Toggle Button -->
    <button @click="toggle()" class="bg-indigo-600 text-white rounded-full p-4 shadow-xl hover:bg-indigo-700 transition-transform transform hover:scale-110 flex items-center justify-center ring-4 ring-white">
        <svg x-show="!isOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        <svg x-show="isOpen" style="display: none;" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>
