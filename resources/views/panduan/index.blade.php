<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panduan Book') }}
        </h2>
    </x-slot>

    <!-- Panggil Library PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <!-- Struktur UI (Tailwind) & State (Alpine.js) -->
                    <div x-data="pdfViewer('{{ asset('assets/ManualBook_Atlas.pdf') }}')" class="w-full flex flex-col items-center">
                        
                        <!-- Top Navigation Bar -->
                        <div class="flex justify-between items-center w-full max-w-4xl mb-6 bg-white px-6 py-3 rounded-xl shadow-sm border border-gray-100">
                            <button @click="prevPage" :disabled="pageNum <= 1" class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                                < Sebelumnya
                            </button>
                            
                            <span class="text-gray-600 font-medium">
                                Halaman <span x-text="pageNum" class="font-bold text-gray-900"></span> dari <span x-text="pageCount" class="font-bold text-gray-900"></span>
                            </span>
                            
                            <button @click="nextPage" :disabled="pageNum >= pageCount" class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                                Selanjutnya >
                            </button>
                        </div>

                        <!-- PDF Canvas Container -->
                        <div class="relative w-full max-w-4xl border border-gray-200 rounded-xl overflow-hidden bg-gray-50 flex justify-center min-h-[600px]">
                            
                            <!-- Loading Spinner -->
                            <div x-show="isRendering" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 z-10 backdrop-blur-sm">
                                <div class="animate-spin h-10 w-10 border-4 border-blue-500 border-t-transparent rounded-full mb-3"></div>
                                <span class="text-sm font-medium text-gray-500">Memuat Halaman...</span>
                            </div>
                            
                            <!-- Added touch events for swipe compatibility -->
                            <div class="w-full flex justify-center overflow-auto" 
                                 @touchstart="handleTouchStart" 
                                 @touchmove="handleTouchMove" 
                                 @touchend="handleTouchEnd">
                                <canvas id="pdf-canvas" class="max-w-full object-contain"></canvas>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4 text-xs text-gray-400">
                            Anda dapat menggunakan tombol panah Kiri/Kanan di keyboard atau geser (swipe) di layar sentuh untuk berpindah halaman.
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Logika Render PDF.js -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pdfViewer', (pdfUrl) => ({
                pdfDoc: null,
                pageNum: 1,
                pageCount: 0,
                isRendering: true,
                canvas: null,
                ctx: null,
                
                // Swipe Support
                touchStartX: 0,
                touchEndX: 0,

                init() {
                    this.canvas = document.getElementById('pdf-canvas');
                    this.ctx = this.canvas.getContext('2d');
                    
                    // Set worker path (menggunakan versi 3.11.174 sesuai instruksi)
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                    // Load PDF
                    pdfjsLib.getDocument(pdfUrl).promise.then(doc => {
                        this.pdfDoc = doc;
                        this.pageCount = doc.numPages;
                        this.renderPage(this.pageNum);
                    }).catch(err => {
                        console.error(err);
                        alert('Gagal memuat file panduan. Pastikan URL valid.');
                        this.isRendering = false;
                    });
                    
                    // Arrow Key Support
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowRight') {
                            this.nextPage();
                        } else if (e.key === 'ArrowLeft') {
                            this.prevPage();
                        }
                    });
                },

                renderPage(num) {
                    this.isRendering = true;
                    this.pdfDoc.getPage(num).then(page => {
                        // Skala 1.5 agar teks PDF tetap tajam saat dirender ke canvas
                        const viewport = page.getViewport({ scale: 1.5 });
                        this.canvas.height = viewport.height;
                        this.canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: this.ctx,
                            viewport: viewport
                        };
                        
                        page.render(renderContext).promise.then(() => {
                            this.isRendering = false;
                        });
                    });
                },

                prevPage() {
                    if (this.pageNum <= 1 || this.isRendering) return;
                    this.pageNum--;
                    this.renderPage(this.pageNum);
                },

                nextPage() {
                    if (this.pageNum >= this.pageCount || this.isRendering) return;
                    this.pageNum++;
                    this.renderPage(this.pageNum);
                },

                // Swipe Logic
                handleTouchStart(e) {
                    this.touchStartX = e.changedTouches[0].screenX;
                },
                handleTouchMove(e) {
                    this.touchEndX = e.changedTouches[0].screenX;
                },
                handleTouchEnd() {
                    if (this.touchEndX < this.touchStartX - 50) {
                        this.nextPage();
                    }
                    if (this.touchEndX > this.touchStartX + 50) {
                        this.prevPage();
                    }
                }
            }));
        });
    </script>
</x-app-layout>
