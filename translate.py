import re

file_path = r"C:\laragon\www\kamerakita.id\resources\views\welcome.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# Replace Navbar
content = content.replace('>Keunggulan<span', ">{{ __('landing.nav.benefits') }}<span")
content = content.replace('>Kalkulator<span', ">{{ __('landing.nav.earnings') }}<span")
content = content.replace('>Cara Kerja</a>', ">{{ __('landing.nav.how_it_works') }}</a>")
content = content.replace('>Testimony</a>', ">{{ __('landing.nav.faq') }}</a>") # Oops testimony is faq?
content = content.replace('>FAQ</a>', ">FAQ</a>")
content = content.replace('>Masuk / Daftar</a>', ">{{ __('landing.nav.login') }}</a>")

# Replace Hero
content = content.replace('<h1>Kerja rumah<br>jadi <em>cuan!</em></h1>', "<h1>{!! __('landing.hero.title') !!}</h1>")
content = content.replace('<p>Sambil ngepel, nyuci piring, atau beres-beres rumah, kamu bisa dapat penghasilan tambahan sampai jutaan rupiah sebulan. Tanpa modal, cukup rekam pakai HP-mu.</p>', "<p>{{ __('landing.hero.subtitle') }}</p>")
content = content.replace('>Mulai Daftar Sekarang</a>', ">{{ __('landing.hero.cta_primary') }}</a>")
content = content.replace('>Lihat Cara Kerja</a>', ">{{ __('landing.hero.cta_secondary') }}</a>")

# Replace keunggulan
content = content.replace('<span>KEUNGGULAN</span>', "<span>{{ __('landing.features.badge') }}</span>")
content = content.replace('<h2>Kenapa Gabung<br>KameraKita?</h2>', "<h2>{!! __('landing.features.title') !!}</h2>")
content = content.replace('<p>Kami percaya kerja cerdas tidak perlu repot. Cukup pakai perangkat yang kamu punya, kapanpun kamu mau.</p>', "<p>{{ __('landing.features.subtitle') }}</p>")
content = content.replace('<h3>Fleksibel 100%</h3>', "<h3>{{ __('landing.features.item_1_title') }}</h3>")
content = content.replace('<p>Kerja kapan aja, di mana aja. Gak ada absen, gak ada bos. Kamu yang tentukan targetmu sendiri.</p>', "<p>{{ __('landing.features.item_1_desc') }}</p>")
content = content.replace('<h3>Modal HP Biasa</h3>', "<h3>{{ __('landing.features.item_2_title') }}</h3>")
content = content.replace('<p>Gak butuh kamera pro. Cukup HP Android/iPhone dengan kamera standar dan kuota internet.</p>', "<p>{{ __('landing.features.item_2_desc') }}</p>")
content = content.replace('<h3>Cuan Pasti</h3>', "<h3>{{ __('landing.features.item_3_title') }}</h3>")
content = content.replace('<p>Pembayaran transparan per video yang lolos QC. Cair rutin setiap minggu ke rekeningmu.</p>', "<p>{{ __('landing.features.item_3_desc') }}</p>")

# CTA
content = content.replace('<h2>Siap ubah kerja rumah jadi<br>cuan sampingan?</h2>', "<h2>{!! __('landing.cta.title') !!}</h2>")
content = content.replace('<p>Kuota mitra terbatas di tiap wilayah. Jangan lewatkan kesempatan jadi perintis KameraKita AI.</p>', "<p>{{ __('landing.cta.subtitle') }}</p>")
content = content.replace('>Mulai Daftar Sekarang</a>', ">{{ __('landing.cta.button') }}</a>")

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Done")
