import re
import os

blade_path = "resources/views/welcome.blade.php"
with open(blade_path, "r", encoding="utf-8") as f:
    blade_content = f.read()

translations = {
    # Replace Button
    r"""<form method="POST" action="{{ route\('locale\.switch'\) }}" style="display:inline;">[\s\S]*?</form>""": """<!-- Modern Toggle Switch -->
<div class="lang-switch-container">
    <span class="lang-label {{ app()->getLocale() === 'id' ? 'active' : '' }}">ID</span>
    <button type="button" class="lang-toggle {{ app()->getLocale() === 'en' ? 'switched' : '' }}" onclick="switchLanguage()" aria-label="Toggle Language">
        <span class="lang-thumb"></span>
    </button>
    <span class="lang-label {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</span>
</div>

<style>
.lang-switch-container { display: inline-flex; align-items: center; gap: 8px; margin-right: 12px; }
.lang-label { font-size: 13px; font-weight: 600; color: #9ca3af; transition: color 0.3s; }
.lang-label.active { color: #111827; }
.lang-toggle { position: relative; width: 44px; height: 24px; background: #e5e7eb; border-radius: 100px; border: none; cursor: pointer; padding: 2px; transition: background 0.3s; }
.lang-toggle.switched { background: #0ea5e9; }
.lang-thumb { display: block; width: 20px; height: 20px; background: white; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transform: translateX(0); transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); }
.lang-toggle.switched .lang-thumb { transform: translateX(20px); }
</style>

<script>
function switchLanguage() {
    const isEn = {{ app()->getLocale() === 'id' ? 'true' : 'false' }};
    const newLocale = isEn ? 'en' : 'id';
    document.querySelector('.lang-toggle').classList.toggle('switched');
    fetch('{{ route('locale.switch') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ locale: newLocale })
    }).then(() => { window.location.reload(); }).catch(() => { window.location.reload(); });
}
</script>""",
    r'Jadi Mitra Vendor': "{{ __('landing.nav.vendor') }}",
    r'<h2>Kerja simpel, hasil maksimal!</h2>': "<h2>{!! __('landing.features.title2') !!}</h2>",
    r'<p>Kamu cukup rekam aktivitas harian dari rumah. Laporan yang lolos approved akan masuk rekap pendapatan secara transparan.</p>': "<p>{{ __('landing.features.subtitle2') }}</p>",
    r'<h3>Bantu AI Biar Pintar</h3>': "<h3>{{ __('landing.features.item_1_title') }}</h3>",
    r'<p>KameraKita AI ngajarin teknologi pintar \(AI\) biar bisa paham cara manusia beraktivitas di dalam rumah.</p>': "<p>{{ __('landing.features.item_1_desc') }}</p>",
    r'<h3>Rekam Kegiatan Rumah</h3>': "<h3>{{ __('landing.features.item_2_title') }}</h3>",
    r'<p>Tugas kamu cuma pakai alat di kepala, lalu rekam aktivitas harian kayak ngepel, nyuci piring, atau beres-beres.</p>': "<p>{{ __('landing.features.item_2_desc') }}</p>",
    r'<h3>Kirim Video & Terima Cuan</h3>': "<h3>{{ __('landing.features.item_3_title') }}</h3>",
    r'<p>Kerjaan rumah beres, dompet tetep tebel. Gak perlu keahlian khusus, semua orang semua kalangan pasti bisa!</p>': "<p>{{ __('landing.features.item_3_desc') }}</p>",

    r'<h2>Pilih ritme kerja yang paling cocok</h2>': "<h2>{!! __('landing.calculator.title2') !!}</h2>",
    r'<p>Rate dasar Rp60.000 per jam rekaman bersih. Simulasi ini membantu kamu membayangkan potensi cuan mingguan sebelum mulai.</p>': "<p>{{ __('landing.calculator.subtitle2') }}</p>",

    r'<h2>Cuma 3 langkah buat mulai dapet cuan</h2>': "<h2>{!! __('landing.steps.title2') !!}</h2>",
    r'<p>Dari daftar sampai pembayaran, semuanya dibuat simpel dan bakal dipandu tim KameraKita.</p>': "<p>{{ __('landing.steps.subtitle2') }}</p>",
    r'<h3>Gabung & Ikuti Briefing</h3>': "<h3>{{ __('landing.steps.step_1_title') }}</h3>",
    r'<p>Daftar lewat WhatsApp, lalu tim kami bakal jelasin cara kerja, tugas, dan kebutuhan alat.</p>': "<p>{{ __('landing.steps.step_1_desc') }}</p>",
    r'<h3>Rekam Aktivitasmu</h3>': "<h3>{{ __('landing.steps.step_2_title') }}</h3>",
    r'<p>Pilih tugas yang tersedia, pasang HP sesuai panduan, lalu rekam aktivitas sehari-hari seperti biasa.</p>': "<p>{{ __('landing.steps.step_2_desc') }}</p>",
    r'<h3>Upload & Terima Bayaran</h3>': "<h3>{{ __('landing.steps.step_3_title') }}</h3>",
    r'<p>Kirim hasil rekaman untuk dicek. Setelah lolos QC, durasi approved masuk ke pembayaran bulanan.</p>': "<p>{{ __('landing.steps.step_3_desc') }}</p>",

    r'<h2>Cerita kontributor yang mulai punya<br>penghasilan tambahan</h2>': "<h2>{!! __('landing.testimonials.title2') !!}</h2>",
    r'<p>Beberapa pengalaman yang menggambarkan bagaimana alur kerja, QC, dan pembayaran dijalankan dengan lebih rapi.</p>': "<p>{{ __('landing.testimonials.subtitle2') }}</p>",

    r'<h2>Pertanyaan yang Sering Diajukan</h2>': "<h2>{{ __('landing.faq.title') }}</h2>",
    r'<span>Apakah pendaftaran mitra dipungut biaya\?</span>': "<span>{{ __('landing.faq.q1_q') }}</span>",
    r'<p>100% GRATIS. Kami tidak pernah memungut biaya apapun dari kontributor. Segala bentuk pungutan mengatasnamakan KameraKita adalah penipuan.</p>': "<p>{{ __('landing.faq.q1_a') }}</p>",
    r'<span>Kapan komisi hasil rekap durasi akan dicairkan\?</span>': "<span>{{ __('landing.faq.q3_q2') }}</span>",
    r'<p>Pencairan komisi diproses manual oleh admin sesuai jadwal operasional berdasarkan rekap durasi yang sudah approved.</p>': "<p>{{ __('landing.faq.q3_a2') }}</p>",
}

for k, v in translations.items():
    blade_content = re.sub(k, v, blade_content)

with open(blade_path, "w", encoding="utf-8") as f:
    f.write(blade_content)

print("Replaced!")
