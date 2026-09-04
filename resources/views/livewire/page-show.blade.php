<div class="min-h-screen py-10 sm:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb & Quick Policy Nav --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-4 mb-8 text-xs">
            <nav class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Beranda</a>
                <span class="text-zinc-300 dark:text-zinc-700">/</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-medium">Informasi & Kebijakan</span>
            </nav>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('page.about') }}" wire:navigate class="px-2.5 py-1 rounded-full text-xs transition-colors {{ $page === 'tentang-kami' ? 'bg-amber-500 text-white font-medium' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                    Tentang Kami
                </a>
                <a href="{{ route('page.privacy') }}" wire:navigate class="px-2.5 py-1 rounded-full text-xs transition-colors {{ $page === 'kebijakan-privasi' ? 'bg-amber-500 text-white font-medium' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                    Kebijakan Privasi
                </a>
                <a href="{{ route('page.terms') }}" wire:navigate class="px-2.5 py-1 rounded-full text-xs transition-colors {{ $page === 'syarat-ketentuan' ? 'bg-amber-500 text-white font-medium' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                    Syarat & Ketentuan
                </a>
                <a href="{{ route('page.contact') }}" wire:navigate class="px-2.5 py-1 rounded-full text-xs transition-colors {{ $page === 'kontak' ? 'bg-amber-500 text-white font-medium' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                    Kontak
                </a>
                <a href="{{ route('page.disclaimer') }}" wire:navigate class="px-2.5 py-1 rounded-full text-xs transition-colors {{ $page === 'disclaimer' ? 'bg-amber-500 text-white font-medium' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                    Disclaimer
                </a>
            </div>
        </div>

        {{-- Main Editorial Container --}}
        <article class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 sm:p-10 shadow-xs">
            
            {{-- PAGE: TENTANG KAMI --}}
            @if ($page === 'tentang-kami')
                <header class="mb-8 border-b border-zinc-100 dark:border-zinc-800/80 pb-6">
                    <span class="inline-block text-xs uppercase tracking-widest font-bold text-amber-600 dark:text-amber-400 mb-2">Profil Redaksi</span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-zinc-950 dark:text-zinc-50 leading-tight">
                        Tentang DISEKITAR
                    </h1>
                    <p class="mt-3 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Publikasi kurasi independen yang mengangkat cerita di balik tempat, cita rasa kuliner, dan dinamika ruang perkotaan di Indonesia.
                    </p>
                </header>

                <div class="prose dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 space-y-6 text-sm sm:text-base leading-relaxed">
                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">Misi Kami</h2>
                        <p>
                            <strong>DISEKITAR</strong> lahir dari kesadaran bahwa setiap sudut kota menyimpan narasi unik yang layak diceritakan. Di tengah membanjirnya informasi tempat yang sering kali seragam dan dangkal, kami berupaya menghadirkan kurasi mendalam untuk membantu Anda menemukan hotel dengan karakter kuat, kedai kopi yang penuh ketenangan, tempat makan legendaris, hingga ruang publik tersembunyi.
                        </p>
                        <p>
                            Fokus kami tidak sekadar menyajikan daftar alamat, melainkan menghadirkan konteks: suasana, keunikan arsitektur, kisah di balik dapur atau bangunan, serta panduan praktis yang memudahkan perjalanan Anda.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">Prinsip & Nilai Editorial</h2>
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong>Kurasi Berbasis Nilai:</strong> Setiap tempat yang masuk dalam publikasi kami dipilih berdasarkan keunikan, kualitas pengalaman, dan relevansi budaya lokal.</li>
                            <li><strong>Integritas & Kejujuran:</strong> Ulasan kami dibuat secara independen. Setiap konten promosi berbayar atau kerja sama komersial akan selalu ditandai secara transparan kepada pembaca.</li>
                            <li><strong>Konektivitas Wilayah:</strong> Kami menghubungkan tempat dengan landmark ikonik di sekitarnya, sehingga memudahkan pembaca merencanakan rute eksplorasi kota secara menyeluruh.</li>
                            <li><strong>Pembaruan Berkelanjutan:</strong> Dinamika tempat usaha dapat berubah dengan cepat. Kami secara berkala meninjau dan memperbarui informasi operasional, harga, dan fasilitas.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">Kategori Publikasi</h2>
                        <p>
                            Dokumentasi kami mencakup ragam kategori tempat dan pengalaman hidup:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 not-prose my-4">
                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700/60">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">🏨 Akomodasi & Hotel</h4>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Ulasan hotel butik, penginapan bernuansa heritage, hingga resor peristirahatan yang nyaman.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700/60">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">☕ Kafe & Ruang Temu</h4>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Kedai kopi berkarakter, tempat kerja ramah laptop (work-from-cafe), dan ruang komunitas.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700/60">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">🍲 Kuliner & Gastronomi</h4>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Eksplorasi cita rasa tradisional, kuliner legendaris turun-temurun, dan kuliner kontemporer.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700/60">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">🏛️ Destinasi & Ruang Budaya</h4>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Landmark bersejarah, galeri seni, taman kota, dan titik wisata ikonik di berbagai kota.</p>
                            </div>
                        </div>
                    </section>

                    <section class="border-t border-zinc-100 dark:border-zinc-800 pt-6">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Punya cerita tempat menarik di sekitar Anda yang layak diulas? Kirimkan saran Anda melalui <a href="{{ route('page.contact') }}" wire:navigate class="text-amber-600 dark:text-amber-400 underline font-medium">halaman kontak kami</a>.
                        </p>
                    </section>
                </div>

            {{-- PAGE: KEBIJAKAN PRIVASI --}}
            @elseif ($page === 'kebijakan-privasi')
                <header class="mb-8 border-b border-zinc-100 dark:border-zinc-800/80 pb-6">
                    <span class="inline-block text-xs uppercase tracking-widest font-bold text-amber-600 dark:text-amber-400 mb-2">Legalitas & Privasi</span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-zinc-950 dark:text-zinc-50 leading-tight">
                        Kebijakan Privasi
                    </h1>
                    <p class="mt-3 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                        Terakhir diperbarui: {{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y') }}
                    </p>
                </header>

                <div class="prose dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 space-y-6 text-sm sm:text-base leading-relaxed">
                    <p>
                        Selamat datang di <strong>DISEKITAR</strong> (<a href="{{ url('/') }}" class="text-amber-600 dark:text-amber-400 underline">https://disekitar.com</a>). Kami menghargai dan berkomitmen penuh untuk melindungi privasi setiap pengunjung. Dokumen Kebijakan Privasi ini menerangkan jenis data apa yang dikumpulkan saat Anda mengakses situs kami, bagaimana data tersebut diproses, serta hak-hak privasi yang Anda miliki.
                    </p>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">1. Data yang Kami Kumpulkan</h2>
                        <p>
                            DISEKITAR membatasi pengumpulan data hanya pada informasi teknis non-identitas pribadi (*non-personally identifiable information*) untuk tujuan operasional dan analitik situs:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong>Informasi Log Teknis:</strong> Ketika Anda mengakses situs, sistem mencatat alamat IP yang telah disamarkan/di-hash (*salted hash*), jenis peramban (*browser*), jenis perangkat (ponsel, desktop, atau tablet), resolusi layar, halaman yang dikunjungi, serta stempel waktu kunjungan. Kami tidak mengaitkan data log ini dengan nama lengkap atau identitas pribadi Anda.</li>
                            <li><strong>Komunikasi Sukarela:</strong> Jika Anda menghubungi redaksi melalui surat elektronik atau formulir kontak, kami menyimpan alamat email dan konten pesan Anda semata-mata untuk merespons pertanyaan atau permohonan Anda.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">2. Cookie dan Iklan Google AdSense (Penting)</h2>
                        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-zinc-800 dark:text-zinc-200">
                            <p class="text-sm font-semibold mb-2">Pemberitahuan Khusus Iklan Pihak Ketiga:</p>
                            <p class="text-xs sm:text-sm leading-relaxed">
                                Situs kami bekerja sama dengan <strong>Google AdSense</strong> sebagai penyedia jaringan periklanan pihak ketiga. Google menggunakan cookie untuk menayangkan iklan kepada pengguna berdasarkan kunjungan sebelumnya ke situs kami maupun situs web lain di internet.
                            </p>
                        </div>
                        <ul class="list-disc pl-5 space-y-2 mt-3">
                            <li>
                                <strong>Kuki DART / DoubleClick:</strong> Penggunaan kuki periklanan memungkinkan Google dan mitranya untuk menayangkan iklan yang relevan kepada pengunjung berdasarkan pola kunjungan mereka di internet.
                            </li>
                            <li>
                                <strong>Pilihan Menyisih (Opt-Out):</strong> Anda dapat memilih untuk menyisih dari periklanan yang dipersonalisasi dengan mengunjungi halaman 
                                <a href="https://adssettings.google.com/" target="_blank" rel="noopener noreferrer" class="text-amber-600 dark:text-amber-400 underline font-medium">Setelan Iklan Google (Google Ads Settings)</a>.
                            </li>
                            <li>
                                Alternatifnya, pengguna dapat menyisih dari penggunaan kuki vendor pihak ketiga untuk iklan yang dipersonalisasi dengan mengunjungi situs inisiatif regulasi periklanan di 
                                <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener noreferrer" class="text-amber-600 dark:text-amber-400 underline font-medium">www.aboutads.info</a>.
                            </li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">3. Penggunaan Cookie Pihak Ketiga Lainnya</h2>
                        <p>
                            Selain Google AdSense, kami dapat menggunakan layanan analitik seperti Google Analytics untuk menganalisis tren penggunaan situs secara agregat (misalnya halaman mana yang paling banyak dibaca dan durasi kunjungan). Layanan analitik ini juga memanfaatkan kuki untuk mengukur kinerja situs secara anonim.
                        </p>
                        <p>
                            Anda memiliki kendali penuh untuk menonaktifkan atau memblokir kuki melalui pengaturan peramban web Anda (seperti Google Chrome, Mozilla Firefox, Apple Safari, atau Microsoft Edge). Harap dicatat bahwa menonaktifkan kuki tertentu mungkin memengaruhi fungsionalitas beberapa fitur situs (seperti preferensi mode gelap).
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">4. Keamanan Data & Perlindungan Konsumen</h2>
                        <p>
                            Kami menerapkan protokol keamanan standar industri (termasuk enkripsi SSL/HTTPS) untuk mencegah akses tidak sah, pengungkapan, atau modifikasi data yang kami simpan. Kami tidak pernah memperjualbelikan, menyewakan, atau mendistribusikan data pengunjung kepada pihak mana pun di luar ketentuan operasional situs yang sah.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">5. Tautan ke Situs Eksternal</h2>
                        <p>
                            Artikel di DISEKITAR dapat memuat tautan ke situs web pihak ketiga, seperti tautan Google Maps untuk navigasi lokasi atau akun media sosial resmi tempat usaha yang diulas. Harap perhatikan bahwa kami tidak memiliki kendali atas kebijakan privasi dan praktik pengumpulan data dari situs-situs eksternal tersebut. Kami menyarankan Anda membaca kebijakan privasi masing-masing situs yang Anda tuju.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">6. Pertanyaan & Kontak Privasi</h2>
                        <p>
                            Jika Anda memiliki pertanyaan, klarifikasi, atau permintaan penghapusan data sehubungan dengan Kebijakan Privasi ini, silakan hubungi tim kami melalui email: <strong>redaksi@disekitar.com</strong> atau melalui halaman <a href="{{ route('page.contact') }}" wire:navigate class="text-amber-600 dark:text-amber-400 underline">Kontak</a>.
                        </p>
                    </section>
                </div>

            {{-- PAGE: SYARAT & KETENTUAN --}}
            @elseif ($page === 'syarat-ketentuan')
                <header class="mb-8 border-b border-zinc-100 dark:border-zinc-800/80 pb-6">
                    <span class="inline-block text-xs uppercase tracking-widest font-bold text-amber-600 dark:text-amber-400 mb-2">Ketentuan Layanan</span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-zinc-950 dark:text-zinc-50 leading-tight">
                        Syarat dan Ketentuan Layanan
                    </h1>
                    <p class="mt-3 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                        Terakhir diperbarui: {{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y') }}
                    </p>
                </header>

                <div class="prose dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 space-y-6 text-sm sm:text-base leading-relaxed">
                    <p>
                        Dengan mengakses dan menggunakan situs <strong>DISEKITAR</strong> (<a href="{{ url('/') }}" class="text-amber-600 dark:text-amber-400 underline">https://disekitar.com</a>), Anda menyetujui untuk terikat oleh Syarat dan Ketentuan berikut. Jika Anda tidak menyetujui salah satu bagian dari ketentuan ini, Anda disarankan untuk tidak melanjutkan akses ke situs ini.
                    </p>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">1. Hak Kekayaan Intelektual</h2>
                        <p>
                            Seluruh konten orisinal yang diterbitkan di DISEKITAR — termasuk namun tidak terbatas pada artikel kurasi, analisis editorial, tata letak antarmuka, grafik, logo, dan kode sumber — adalah milik sah <strong>DISEKITAR</strong> dan dilindungi oleh Undang-Undang Hak Cipta Republik Indonesia.
                        </p>
                        <p>
                            Pengutipan teks diperbolehkan maksimal 1 (satu) paragraf untuk keperluan wajar (*fair use*), dengan syarat wajib menyertakan atribusi yang jelas serta tautan aktif (*dofollow*) ke halaman sumber asli di DISEKITAR. Penggandaan massal, scraping otomatis tanpa izin, atau penerbitan ulang seluruh artikel dilarang keras.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">2. Penggunaan Informasi</h2>
                        <p>
                            Seluruh informasi yang disajikan di DISEKITAR dimaksudkan sebagai referensi dan panduan wisata, kuliner, serta gaya hidup perkotaan. Meskipun tim kami berusaha memastikan seluruh data (seperti lokasi, fasilitas, jam buka, dan kisaran harga) akurat pada saat penulisan, perubahan operasional di lapangan dapat terjadi tanpa pemberitahuan dari pihak pengelola tempat terkait.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">3. Perilaku Pengguna</h2>
                        <p>
                            Dalam menggunakan situs DISEKITAR, pengguna dilarang:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Melakukan tindakan yang merusak, membebani secara berlebih (*DDoS*), atau mengganggu stabilitas infrastruktur server situs.</li>
                            <li>Memanfaatkan bot, spider, scraper, atau perangkat otomatis untuk mengambil database konten tanpa izin tertulis dari redaksi.</li>
                            <li>Menyalahgunakan formulir kontak untuk aktivitas spamming, promosi ilegal, atau pengiriman materi berbahaya.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">4. Hukum yang Berlaku</h2>
                        <p>
                            Syarat dan Ketentuan ini diatur dan ditafsirkan sesuai dengan hukum yang berlaku di Negara Kesatuan Republik Indonesia. Setiap perselisihan yang timbul sehubungan dengan penggunaan situs ini akan diselesaikan secara musyawarah untuk mencapai mufakat.
                        </p>
                    </section>
                </div>

            {{-- PAGE: KONTAK --}}
            @elseif ($page === 'kontak')
                <header class="mb-8 border-b border-zinc-100 dark:border-zinc-800/80 pb-6">
                    <span class="inline-block text-xs uppercase tracking-widest font-bold text-amber-600 dark:text-amber-400 mb-2">Komunikasi Redaksi</span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-zinc-950 dark:text-zinc-50 leading-tight">
                        Hubungi Kami
                    </h1>
                    <p class="mt-3 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Kami selalu terbuka untuk saran, koreksi data tempat, pengajuan ulasan, kerja sama editorial, maupun pertanyaan umum.
                    </p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                    <div class="md:col-span-6 space-y-6 text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed">
                        <section>
                            <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Alamat Surat Elektronik</h3>
                            <p>Untuk pertanyaan editorial, kemitraan media, atau pertanyaan umum seputar publikasi:</p>
                            <div class="mt-3 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 space-y-2">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Redaksi & Liputan:</div>
                                        <a href="mailto:redaksi@disekitar.com" class="font-semibold text-zinc-900 dark:text-zinc-100 hover:text-amber-600 dark:hover:text-amber-400">redaksi@disekitar.com</a>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 pt-2 border-t border-zinc-200/50 dark:border-zinc-700/50">
                                    <span class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </span>
                                    <div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">Dukungan Teknis & Privasi:</div>
                                        <a href="mailto:admin@disekitar.com" class="font-semibold text-zinc-900 dark:text-zinc-100 hover:text-blue-600 dark:hover:text-blue-400">admin@disekitar.com</a>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Koreksi Data Tempat</h3>
                            <p>
                                Apakah Anda adalah pengelola tempat makan, hotel, atau kafe yang diulas di DISEKITAR dan ingin memperbarui jam operasional, menu, atau informasi kontak? Cantumkan tautan artikel yang dimaksud dalam email Anda agar tim kami dapat segera memperbaruinya.
                            </p>
                        </section>

                        <section>
                            <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Waktu Respons</h3>
                            <p>
                                Tim redaksi kami berusaha menanggapi setiap surat masuk dalam kurun waktu 1x24 jam hingga 2x24 jam kerja (Senin - Jumat).
                            </p>
                        </section>
                    </div>

                    <div class="md:col-span-6 bg-zinc-50 dark:bg-zinc-800/40 p-6 rounded-xl border border-zinc-200/70 dark:border-zinc-700/60">
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-1">Panduan Pengajuan Tempat</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">Ingin tempat Anda dikurasi oleh tim DISEKITAR? Kirimkan informasi berikut ke email kami:</p>
                        
                        <div class="space-y-3 text-xs text-zinc-600 dark:text-zinc-300">
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 text-[10px]">1</span>
                                <div><strong>Nama & Lokasi Tempat:</strong> Nama lengkap bisnis, alamat fisik, dan tautan titik Google Maps.</div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 text-[10px]">2</span>
                                <div><strong>Kategori:</strong> Hotel / Kafe / Restoran / Kuliner / Destinasi Budaya.</div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 text-[10px]">3</span>
                                <div><strong>Cerita Unik:</strong> Apa yang membedakan tempat Anda dari tempat lainnya di kota Anda.</div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 text-[10px]">4</span>
                                <div><strong>Foto Berkualitas:</strong> Lampirkan beberapa dokumentasi suasana tempat dan sajian utama.</div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700 text-center">
                            <a href="mailto:redaksi@disekitar.com?subject=Rekomendasi%20Tempat%20Disekitar" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold text-xs hover:bg-amber-600 dark:hover:bg-amber-500 transition-colors">
                                <i class="fa-solid fa-paper-plane"></i>
                                Kirim Email Rekomendasi
                            </a>
                        </div>
                    </div>
                </div>

            {{-- PAGE: DISCLAIMER --}}
            @elseif ($page === 'disclaimer')
                <header class="mb-8 border-b border-zinc-100 dark:border-zinc-800/80 pb-6">
                    <span class="inline-block text-xs uppercase tracking-widest font-bold text-amber-600 dark:text-amber-400 mb-2">Pernyataan Hukum</span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-zinc-950 dark:text-zinc-50 leading-tight">
                        Penafian (Disclaimer)
                    </h1>
                    <p class="mt-3 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
                        Terakhir diperbarui: {{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y') }}
                    </p>
                </header>

                <div class="prose dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 space-y-6 text-sm sm:text-base leading-relaxed">
                    <p>
                        Seluruh materi yang dipublikasikan di situs <strong>DISEKITAR</strong> (<a href="{{ url('/') }}" class="text-amber-600 dark:text-amber-400 underline">https://disekitar.com</a>) diterbitkan dengan itikad baik untuk tujuan informasi umum dan panduan eksplorasi tempat saja.
                    </p>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">1. Keakuratan Informasi Tempat</h2>
                        <p>
                            Informasi mengenai jam buka operasional, harga menu makanan, tarif kamar penginapan, ketersediaan fasilitas, nomor telepon, dan status operasional tempat usaha dapat berubah sewaktu-waktu oleh pihak manajemen bisnis tanpa pemberitahuan kepada kami. DISEKITAR tidak memberikan jaminan mutlak atas kelengkapan, keandalan, atau keakuratan mutakhir dari rincian operasional tersebut. Setiap tindakan yang Anda ambil berdasarkan informasi di situs ini merupakan risiko Anda sendiri.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">2. Independensi Ulasan</h2>
                        <p>
                            Pendapat, ulasan suasana, dan deskripsi rasa atau kenyamanan yang tertuang dalam artikel merupakan pandangan subjektif dari tim kurasi editorial DISEKITAR pada saat kunjungan atau kurasi dilakukan. Pengalaman setiap individu terhadap sebuah tempat dapat berbeda-beda.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">3. Hak Cipta Gambar & Materi Visual</h2>
                        <p>
                            Foto-foto yang ditampilkan pada publikasi DISEKITAR berasal dari dokumentasi mandiri, materi promosi resmi tempat usaha, atau sumber terbuka dengan atribusi yang layak. Jika Anda adalah pemegang hak cipta sah atas salah satu foto yang ditampilkan dan tidak berkenan materi tersebut dimuat, silakan hubungi kami di <strong>redaksi@disekitar.com</strong> dengan menyertakan bukti kepemilikan. Kami akan segera memperbarui atribusi atau menurunkan materi tersebut dalam waktu 1x24 jam kerja.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">4. Tautan Eksternal</h2>
                        <p>
                            Situs ini menyediakan tautan ke situs eksternal (seperti tautan peta Google Maps, situs resmi pemesanan, atau profil media sosial pengelola tempat). Meskipun kami berupaya hanya menyediakan tautan ke situs yang bermanfaat dan beretika, kami tidak memiliki kendali atas konten dan sifat situs-situs tersebut. Pemilik situs eksternal dapat mengubah konten sebelum kami sempat menghapus tautan yang mungkin telah berubah statusnya.
                        </p>
                    </section>
                </div>
            @endif

        </article>

    </div>
</div>
