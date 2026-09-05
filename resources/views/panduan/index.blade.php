<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panduan Book') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="pdfViewer('{{ asset('assets/Manual book _Atlas.pdf') }}')">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <button @click="prevPage" :disabled="pageNum <= 1" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Sebelumnya
                        </button>
                        
                        <div class="text-sm font-medium text-gray-600">
                            Halaman <span class="font-bold text-gray-900" x-text="pageNum"></span> dari <span class="font-bold text-gray-900" x-text="pageCount"></span>
                        </div>

                        <button @click="nextPage" :disabled="pageNum >= pageCount" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm flex items-center gap-2">
                            Selanjutnya
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    <!-- PDF Container -->
                    <div class="relative bg-gray-100 rounded-xl p-4 overflow-auto min-h-[400px] flex justify-center items-center">
                        <template x-if="loading">
                            <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 rounded-xl z-10">
                                <svg class="animate-spin h-10 w-10 text-indigo-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-500 font-medium">Memuat Panduan...</span>
                            </div>
                        </template>

                        <!-- Swipe functionality wrapper -->
                        <div x-ref="pdfWrapper" 
                             @touchstart="handleTouchStart" 
                             @touchmove="handleTouchMove" 
                             @touchend="handleTouchEnd"
                             class="w-full flex justify-center">
                             
                            <canvas id="pdf-canvas" class="shadow-md rounded-lg max-w-full h-auto" :class="{'opacity-0': loading}"></canvas>
                        </div>
                    </div>

                    <!-- Keyboard Navigation Info -->
                    <div class="text-center mt-4 text-xs text-gray-400">
                        Anda dapat menggunakan tombol panah Kiri/Kanan di keyboard atau geser (swipe) di layar sentuh untuk berpindah halaman.
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Gunakan versi 2.16.105 agar kompatibel lebih luas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        function pdfViewer(url) {
            return {
                pdfDoc: null,
                pageNum: 1,
                pageRendering: false,
                pageNumPending: null,
                scale: 1.5,
                canvas: null,
                ctx: null,
                pageCount: 0,
                loading: true,
                
                // Swipe Support
                touchStartX: 0,
                touchEndX: 0,

                init() {
                    this.canvas = document.getElementById('pdf-canvas');
                    this.ctx = this.canvas.getContext('2d');
                    
                    pdfjsLib.getDocument(url).promise.then(pdfDoc_ => {
                        this.pdfDoc = pdfDoc_;
                        this.pageCount = this.pdfDoc.numPages;
                        this.loading = false;
                        this.renderPage(this.pageNum);
                    }).catch(err => {
                        console.error('Error loading PDF: ', err);
                        alert('Gagal memuat file panduan.');
                        this.loading = false;
                    });

                    // Arrow Key Support
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowRight') {
                            this.nextPage();
                        } else if (e.key === 'ArrowLeft') {
                            this.prevPage();
                        }
                    });
                    
                    // Handle window resize to fit responsive canvas
                    window.addEventListener('resize', () => {
                        if(!this.loading && !this.pageRendering) {
                            this.renderPage(this.pageNum);
                        }
                    });
                },

                renderPage(num) {
                    this.pageRendering = true;
                    this.pdfDoc.getPage(num).then(page => {
                        // Responsive Scale
                        let wrapperWidth = this.$refs.pdfWrapper.clientWidth;
                        var unscaledViewport = page.getViewport({scale: 1.0});
                        
                        // Default scale 1.5, or fit to wrapper width if smaller
                        let calcScale = Math.min(1.5, wrapperWidth / unscaledViewport.width);
                        var viewport = page.getViewport({scale: calcScale});

                        this.canvas.height = viewport.height;
                        this.canvas.width = viewport.width;

                        var renderContext = {
                            canvasContext: this.ctx,
                            viewport: viewport
                        };
                        var renderTask = page.render(renderContext);

                        renderTask.promise.then(() => {
                            this.pageRendering = false;
                            if (this.pageNumPending !== null) {
                                this.renderPage(this.pageNumPending);
                                this.pageNumPending = null;
                            }
                        });
                    });
                },

                queueRenderPage(num) {
                    if (this.pageRendering) {
                        this.pageNumPending = num;
                    } else {
                        this.renderPage(num);
                    }
                },

                prevPage() {
                    if (this.pageNum <= 1) return;
                    this.pageNum--;
                    this.queueRenderPage(this.pageNum);
                },

                nextPage() {
                    if (this.pageNum >= this.pageCount) return;
                    this.pageNum++;
                    this.queueRenderPage(this.pageNum);
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
                        // Swiped Left -> Next Page
                        this.nextPage();
                    }
                    if (this.touchEndX > this.touchStartX + 50) {
                        // Swiped Right -> Prev Page
                        this.prevPage();
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
