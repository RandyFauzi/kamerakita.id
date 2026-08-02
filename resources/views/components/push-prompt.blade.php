<div x-data="pushNotificationPrompt()" x-show="showBanner" x-transition.opacity
     class="fixed bottom-0 left-0 right-0 md:left-64 p-4 z-50 flex justify-center pb-28 md:pb-6" style="display: none;">
    
    <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 p-5 md:p-6 w-full max-w-2xl flex flex-col sm:flex-row items-center gap-4 relative">
        
        <!-- Close Button (Nanti Saja) -->
        <button @click="dismissBanner()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="flex-shrink-0 bg-blue-50 p-3 rounded-full">
            <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>
        
        <div class="flex-1 text-center sm:text-left">
            <h3 class="text-base font-bold text-gray-900 mb-1" x-text="bannerTitle">Aktifkan Notifikasi Real-time</h3>
            <p class="text-sm text-gray-500" x-text="bannerMessage">
                Nyalakan notifikasi untuk selalu mendapat pengumuman terbaru dari Admin dan tidak ketinggalan informasi penutupan project!
            </p>
        </div>

        <div class="flex-shrink-0 flex flex-col gap-2 mt-2 sm:mt-0 w-full sm:w-auto">
            <button @click="requestPermission()" x-show="!isDenied" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                Nyalakan Sekarang
            </button>
            <button @click="dismissBanner()" x-show="!isDenied" class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                Nanti Saja
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pushNotificationPrompt', () => ({
            showBanner: false,
            isDenied: false,
            bannerTitle: 'Aktifkan Notifikasi Real-time',
            bannerMessage: 'Nyalakan notifikasi untuk selalu mendapat pengumuman terbaru dari Admin dan tidak ketinggalan informasi penutupan project!',
            vapidPublicKey: '{{ env("VAPID_PUBLIC_KEY") }}',

            init() {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    // Push not supported
                    return;
                }

                this.registerServiceWorker();

                // Check permission
                if (Notification.permission === 'default' && !localStorage.getItem('push_prompt_dismissed')) {
                    setTimeout(() => {
                        this.showBanner = true;
                    }, 1500);
                } else if (Notification.permission === 'denied') {
                    // We don't show the banner continuously if denied, but you could if you want.
                    // For now, let's keep it clean.
                }
            },

            registerServiceWorker() {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('ServiceWorker registered with scope:', registration.scope);
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
                const base64 = (base64String + padding)
                    .replace(/\-/g, '+')
                    .replace(/_/g, '/');

                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);

                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            },

            requestPermission() {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        this.subscribeUser();
                    } else if (permission === 'denied') {
                        this.isDenied = true;
                        this.bannerTitle = 'Notifikasi Diblokir Browser';
                        this.bannerMessage = 'Anda menekan "Block". Untuk menyalakannya, klik ikon gembok di sebelah alamat web browser (address bar) dan ubah setelan Notifikasi ke "Allow".';
                    } else {
                        this.dismissBanner();
                    }
                });
            },

            subscribeUser() {
                navigator.serviceWorker.ready.then(registration => {
                    const subscribeOptions = {
                        userVisibleOnly: true,
                        applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey)
                    };

                    return registration.pushManager.subscribe(subscribeOptions);
                }).then(pushSubscription => {
                    console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
                    this.storeSubscription(pushSubscription);
                }).catch(error => {
                    console.error('Failed to subscribe the user: ', error);
                    alert("Gagal mengaktifkan notifikasi: " + error.message);
                });
            },

            storeSubscription(pushSubscription) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('/push-subscriptions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(pushSubscription)
                }).then(response => {
                    if (response.ok) {
                        this.showBanner = false;
                    }
                }).catch(err => {
                    console.error('Send subscription failed:', err);
                });
            }
        }));
    });
</script>
