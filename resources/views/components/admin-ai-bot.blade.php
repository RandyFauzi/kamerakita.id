<div x-data="{
        isOpen: false,
        inputText: '',
        isLoading: false,
        messages: [
            { role: 'model', text: 'Halo Admin! Saya AI Command Center. Ada operasional yang perlu dieksekusi hari ini?' }
        ],
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
            
            this.messages.push({ role: 'user', text: text });
            this.inputText = '';
            this.isLoading = true;
            this.scrollToBottom();

            try {
                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '';
                const bearerToken = localStorage.getItem('token') || ''; 
                
                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                };
                if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;
                if (bearerToken) headers['Authorization'] = `Bearer ${bearerToken}`;

                const response = await fetch('/api/admin-assistant', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({ message: text })
                });

                const data = await response.json();
                
                if (response.status === 429) {
                    this.messages.push({ role: 'model', text: data.reply || 'Sistem sedang memproses terlalu banyak data dalam satu menit. Mohon tunggu sekitar 30 detik sebelum memberikan perintah baru.' });
                } else if (!response.ok) {
                    this.messages.push({ role: 'model', text: data.message || 'Maaf, terjadi kesalahan saat menghubungi server.' });
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
    class="fixed bottom-6 right-6 z-[9999] font-sans"
>
    <!-- Toggle Button -->
    <button @click="toggle()" class="bg-indigo-600 text-white rounded-full p-4 shadow-lg hover:bg-indigo-700 transition-transform transform hover:scale-110 flex items-center justify-center">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
    </button>

    <!-- Chat Panel -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="absolute bottom-20 right-0 w-[380px] max-w-[calc(100vw-2rem)] bg-white/90 backdrop-blur-xl border border-white/40 shadow-2xl rounded-2xl overflow-hidden flex flex-col h-[550px] max-h-[85vh] ring-1 ring-indigo-900/5">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-5 py-4 flex justify-between items-center shrink-0 backdrop-blur-md shadow-sm">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-1.5 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm tracking-wide">KameraBot</h3>
                    <p class="text-[10px] text-indigo-100 font-medium">AI Admin Command Center</p>
                </div>
            </div>
            <button @click="isOpen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-lg p-1.5 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Messages Area -->
        <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-sm' : 'bg-white shadow-sm border border-gray-100 text-gray-800 rounded-2xl rounded-tl-sm'" class="px-4 py-2.5 max-w-[85%] text-sm leading-relaxed" x-html="formatMessage(msg.text)">
                    </div>
                </div>
            </template>
            
            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex justify-start">
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
                <input x-model="inputText" type="text" placeholder="Ketik instruksi untuk KameraBot..." class="flex-1 bg-gray-50/80 border border-gray-200/60 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-gray-400" :disabled="isLoading">
                <button type="submit" :disabled="isLoading || inputText.trim() === ''" class="bg-indigo-600 text-white rounded-xl px-4 py-3 flex items-center justify-center hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>
</div>
