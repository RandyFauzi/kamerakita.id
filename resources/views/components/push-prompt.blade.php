<div x-data="pushNotificationPrompt()" x-show="showBanner" x-transition.opacity
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4" style="display: none;">
    
    <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 w-full max-w-md flex flex-col items-center text-center gap-4 relative" @click.away="dismissBanner()">
        
        <!-- Close Button -->
        <button @click="dismissBanner()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="flex-shrink-0 bg-blue-50 p-4 rounded-full mb-2">
            <svg class="w-10 h-10 text-blue-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>
        
        <div class="flex-1 w-full">
            <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="bannerTitle">Aktifkan Notifikasi Real-time</h3>
            <p class="text-sm text-gray-500 leading-relaxed" x-text="bannerMessage" x-html="bannerMessageHtml">
                Nyalakan notifikasi untuk selalu mendapat pengumuman terbaru dari Admin dan tidak ketinggalan informasi penting!
            </p>
        </div>

        <div class="w-full mt-4 flex flex-col gap-2">
            <button @click="requestPermission()" x-show="!isDenied && isSupported" class="w-full px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md transition-all transform hover:scale-[1.02]">
                Nyalakan Sekarang
            </button>
            <button @click="dismissBanner()" x-show="!isDenied && isSupported" class="w-full px-5 py-3 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl transition-all">
                Nanti Saja
            </button>
            <p x-show="isDenied || !isSupported" class="text-xs text-red-500 font-medium mt-2">
                Peringatan: Notifikasi saat ini dimatikan oleh sistem perangkat Anda.
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pushNotificationPrompt', () => ({
            showBanner: false,
            isDenied: false,
            isSupported: true,
            bannerTitle: 'Aktifkan Notifikasi Real-time',
            bannerMessageHtml: 'Sistem KameraKita menyarankan Anda untuk menyalakan notifikasi agar tidak tertinggal info penting.<br><br><b>Silakan klik tombol di bawah dan pilih "Allow/Izinkan".</b>',
            vapidPublicKey: '{{ config("webpush.vapid.public_key") }}',

            init() {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    this.isSupported = false;
                    this.bannerTitle = 'Perangkat Tidak Mendukung Notifikasi';
                    this.bannerMessageHtml = 'Browser Anda tidak mendukung Web Push.<br><br><b>Pengguna iPhone (iOS):</b> Anda wajib menekan ikon <b>Share (Bagikan)</b> lalu pilih <b>"Add to Home Screen"</b>. Setelah itu, buka aplikasi dari layar utama HP Anda.';
                    return;
                }

                if (!this.vapidPublicKey || this.vapidPublicKey.trim() === '') {
                    this.isSupported = false;
                    this.bannerTitle = 'Konfigurasi Server Belum Lengkap';
                    this.bannerMessageHtml = 'VAPID_PUBLIC_KEY belum dikonfigurasi di file .env server. Silakan hubungi administrator sistem.';
                    this.showBanner = true;
                    return;
                }

                this.registerServiceWorker();

                if (Notification.permission === 'default' && !localStorage.getItem('push_prompt_dismissed')) {
                    setTimeout(() => { this.showBanner = true; }, 1500);
                } else if (Notification.permission === 'denied') {
                    // Silently ignore if denied, don't harass user.
                    this.isDenied = true;
                } else if (Notification.permission === 'granted') {
                    // Silently resubscribe to ensure backend is in sync
                    this.subscribeUser(true);
                }
            },

            registerServiceWorker() {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('ServiceWorker registered');
                }).catch(error => {
                    console.error('ServiceWorker registration failed:', error);
                });
            },

            dismissBanner() {
                this.showBanner = false;
                localStorage.setItem('push_prompt_dismissed', 'true');
            },

            urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                for (let i = 0; i < rawData.length; ++i) { outputArray[i] = rawData.charCodeAt(i); }
                return outputArray;
            },

            requestPermission() {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        this.bannerTitle = 'Sedang Mengaktifkan...';
                        this.bannerMessageHtml = 'Mohon tunggu sebentar, sistem sedang mendaftarkan perangkat Anda...';
                        this.subscribeUser(false);
                    } else if (permission === 'denied') {
                        this.isDenied = true;
                        this.bannerTitle = 'Akses Notifikasi Diblokir';
                        this.bannerMessageHtml = 'Anda telah memblokir notifikasi. Klik ikon gembok di sebelah alamat web browser Anda, lalu ubah izin Notifikasi menjadi <b>"Allow/Izinkan"</b>. Setelah itu refresh halaman ini.';
                    }
                });
            },

            subscribeUser(isSilent = false) {
                navigator.serviceWorker.ready.then(registration => {
                    return registration.pushManager.getSubscription().then(existingSub => {
                        if (existingSub) {
                            return existingSub.unsubscribe().then(() => {
                                return registration.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
                                });
                            });
                        } else {
                            return registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
                            });
                        }
                    });
                }).then(pushSubscription => {
                    this.storeSubscription(pushSubscription, isSilent);
                }).catch(error => {
                    console.error('Failed to subscribe:', error);
                    if (!isSilent) {
                        this.bannerTitle = 'Gagal Mengaktifkan';
                        this.bannerMessageHtml = 'Terjadi kesalahan sistem saat mendaftarkan notifikasi: ' + error.message;
                    }
                });
            },

            storeSubscription(pushSubscription, isSilent) {
                // Better serialization across browsers
                let key = pushSubscription.getKey('p256dh');
                let token = pushSubscription.getKey('auth');
                let contentEncoding = (window.PushManager && PushManager.supportedContentEncodings) ? PushManager.supportedContentEncodings[0] : 'aesgcm';

                let data = {
                    endpoint: pushSubscription.endpoint,
                    keys: {
                        p256dh: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                        auth: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null
                    },
                    contentEncoding: contentEncoding
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('/push-subscriptions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(data)
                }).then(async response => {
                    if (response.ok) {
                        this.showBanner = false;
                    } else if (response.status === 419) {
                        // CSRF token expired, reload the page to get a new one
                        if (!sessionStorage.getItem('push_csrf_reloaded')) {
                            sessionStorage.setItem('push_csrf_reloaded', 'true');
                            window.location.reload();
                        } else {
                            if (!isSilent) {
                                this.bannerTitle = 'Sesi Kedaluwarsa (419)';
                                this.bannerMessageHtml = 'Gagal menyimpan notifikasi karena sesi habis. Silakan login kembali.';
                            }
                        }
                    } else {
                        const errorData = await response.text();
                        console.error('Server returned error:', response.status, errorData);
                        if (!isSilent) {
                            this.bannerTitle = 'Gagal Menyimpan (Error ' + response.status + ')';
                            this.bannerMessageHtml = 'Gagal menyimpan data notifikasi ke server. Tim kami akan segera memperbaikinya. Silakan klik Nanti Saja.';
                        } else {
                            // Even if silent, if it fails, maybe we show something?
                            // Actually just log it.
                        }
                    }
                }).catch(err => {
                    console.error('Store error:', err);
                    if (!isSilent) {
                        this.bannerTitle = 'Koneksi Terputus';
                        this.bannerMessageHtml = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
                    }
                });
            }
        }));
    });
</script>
