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

        {{-- Main Content Container --}}
        <article class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 p-6 sm:p-10 shadow-xs">
            
            {{-- PAGE: TENTANG KAMI --}}
            @if ($page === 'tentang-kami')
                <header class="mb-8 border-b border-zinc-100 dark:border-zinc-800/80 pb-6">
                    <span class="inline-block text-xs uppercase tracking-widest font-bold text-amber-600 dark:text-amber-400 mb-2">Profil</span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-zinc-950 dark:text-zinc-50 leading-tight">
                        Tentang CariKaryawan
                    </h1>
                    <p class="mt-3 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Agregator lowongan kerja di Indonesia, dilengkapi tools untuk membantu proses melamar.
                    </p>
                </header>

                <div class="prose dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 space-y-6 text-sm sm:text-base leading-relaxed">
                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">Apa Itu CariKaryawan</h2>
                        <p>
                            <strong>CariKaryawan</strong> mengumpulkan informasi lowongan kerja yang dipublikasikan di berbagai sumber di internet, lalu merangkumnya per kota dan kategori agar lebih mudah dicari.
                        </p>
                        <p>
                            CariKaryawan <strong>bukan pemberi kerja, bukan agen penyalur tenaga kerja, dan tidak menerima lamaran</strong>. Untuk melamar, Anda diarahkan ke tautan lowongan asli di situs sumbernya.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">Cara Kerja</h2>
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong>Pengumpulan otomatis:</strong> Data lowongan dikumpulkan secara otomatis dari sumber publik dan dapat dirapikan dengan bantuan AI.</li>
                            <li><strong>Tautan asli:</strong> Setiap lowongan menyertakan tautan lamaran ke sumber aslinya bila tersedia.</li>
                            <li><strong>Masa tayang:</strong> Lowongan ditandai ditutup setelah masa tayangnya berakhir (21 hari sejak dipublikasikan) atau jika sumbernya sudah tidak tersedia.</li>
                            <li><strong>Tanpa verifikasi:</strong> Kami tidak memverifikasi keaslian lowongan maupun perusahaan. Selalu periksa sendiri sebelum mengirim data pribadi, dan waspada terhadap lowongan yang meminta biaya.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">Tools Karier</h2>
                        <p>Selain lowongan, tersedia tools gratis untuk persiapan melamar:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 not-prose my-4">
                            <a href="{{ route('career.cv-builder') }}" wire:navigate class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700/60 hover:border-amber-400 transition-colors">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">CV Builder</h4>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Susun CV ramah ATS.</p>
                            </a>
                            <a href="{{ route('career.interview-prep') }}" wire:navigate class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700/60 hover:border-amber-400 transition-colors">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Interview Prep</h4>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Latihan pertanyaan wawancara.</p>
                            </a>
                            <a href="{{ route('tools.hub') }}" wire:navigate class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/60 dark:border-zinc-700/60 hover:border-amber-400 transition-colors">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">Surat Lamaran</h4>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Buat draf surat lamaran dari halaman lowongan.</p>
                            </a>
                        </div>
                    </section>

                    <section class="border-t border-zinc-100 dark:border-zinc-800 pt-6">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Pemilik lowongan yang ingin lowongannya diperbarui atau diturunkan dapat menghubungi kami lewat <a href="{{ route('page.contact') }}" wire:navigate class="text-amber-600 dark:text-amber-400 underline font-medium">halaman kontak</a>.
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
                        Selamat datang di <strong>CariKaryawan</strong> (<a href="{{ url('/') }}" class="text-amber-600 dark:text-amber-400 underline">https://carikaryawan.com</a>). Dokumen ini menjelaskan data apa yang diproses saat Anda memakai situs dan tools kami, serta pilihan yang Anda miliki.
                    </p>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">1. Data yang Kami Proses</h2>
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong>Data kunjungan:</strong> alamat IP yang di-hash, jenis peramban dan perangkat, halaman yang dikunjungi, dan waktu kunjungan, untuk statistik penggunaan situs.</li>
                            <li><strong>Data pada tools karier:</strong> teks yang Anda masukkan ke CV Builder, pembuat surat lamaran, atau Interview Prep (misalnya riwayat kerja dan pendidikan) dikirim ke penyedia layanan AI untuk menghasilkan keluaran. Hindari memasukkan data sensitif seperti nomor identitas, nomor rekening, atau kata sandi.</li>
                            <li><strong>Komunikasi:</strong> jika Anda mengirim email ke kami, kami menyimpan alamat email dan isi pesan untuk menanggapinya.</li>
                        </ul>
                        <p>Kami tidak menyediakan akun pengguna atau fitur pengiriman lamaran. Lamaran dikirim langsung ke situs sumber lowongan, dan data yang Anda berikan di sana tunduk pada kebijakan privasi situs tersebut.</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">2. Cookie dan Iklan Pihak Ketiga</h2>
                        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-zinc-800 dark:text-zinc-200">
                            <p class="text-xs sm:text-sm leading-relaxed">
                                Situs ini dapat menampilkan iklan dari <strong>Google AdSense</strong>. Google dan mitranya menggunakan cookie untuk menayangkan iklan berdasarkan kunjungan Anda ke situs ini dan situs lain.
                            </p>
                        </div>
                        <ul class="list-disc pl-5 space-y-2 mt-3">
                            <li>
                                Anda dapat menyisih dari iklan yang dipersonalisasi melalui
                                <a href="https://adssettings.google.com/" target="_blank" rel="noopener noreferrer" class="text-amber-600 dark:text-amber-400 underline font-medium">Setelan Iklan Google</a>
                                atau <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener noreferrer" class="text-amber-600 dark:text-amber-400 underline font-medium">www.aboutads.info</a>.
                            </li>
                            <li>Sebagian tautan di situs dapat berupa tautan afiliasi dan ditandai <code>sponsored</code>; kami dapat menerima komisi bila Anda membeli lewat tautan tersebut.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">3. Analitik</h2>
                        <p>
                            Kami dapat memakai layanan analitik seperti Google Analytics dan Umami untuk memahami penggunaan situs secara agregat. Anda dapat mengatur atau menghapus cookie melalui pengaturan peramban; menonaktifkannya dapat memengaruhi fitur seperti preferensi tema gelap.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">4. Tautan ke Situs Eksternal</h2>
                        <p>
                            Lowongan di CariKaryawan menautkan ke situs pihak ketiga. Kami tidak mengendalikan kebijakan privasi dan praktik data situs tersebut.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">5. Kontak Privasi</h2>
                        <p>
                            Pertanyaan atau permintaan terkait data Anda dapat dikirim ke <strong>kontak@carikaryawan.com</strong> atau melalui halaman <a href="{{ route('page.contact') }}" wire:navigate class="text-amber-600 dark:text-amber-400 underline">Kontak</a>.
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
                        Dengan mengakses <strong>CariKaryawan</strong> (<a href="{{ url('/') }}" class="text-amber-600 dark:text-amber-400 underline">https://carikaryawan.com</a>), Anda menyetujui ketentuan berikut. Jika tidak setuju, mohon hentikan penggunaan situs.
                    </p>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">1. Sifat Layanan</h2>
                        <p>
                            CariKaryawan adalah agregator informasi lowongan kerja. Kami <strong>bukan pemberi kerja dan bukan penyalur tenaga kerja</strong>, dan tidak menjadi pihak dalam hubungan antara pelamar dan perusahaan. Proses melamar, seleksi, dan keputusan perekrutan sepenuhnya ada pada pemberi kerja di situs sumber.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">2. Informasi Lowongan</h2>
                        <p>
                            Informasi lowongan dikumpulkan dari sumber pihak ketiga dan dapat berubah, tidak lengkap, atau sudah tidak berlaku. Kami tidak memverifikasi keaslian lowongan atau perusahaan, dan tidak menjamin Anda akan mendapatkan pekerjaan. Jangan membayar biaya apa pun kepada pihak yang mengaku mewakili lowongan, dan periksa kembali informasi di tautan sumber asli.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">3. Tools Karier Berbasis AI</h2>
                        <p>
                            Keluaran CV Builder, pembuat surat lamaran, dan Interview Prep dihasilkan dengan bantuan AI dan dapat mengandung kesalahan. Anda bertanggung jawab memeriksa dan menyesuaikan hasilnya sebelum digunakan, serta atas kebenaran data yang Anda masukkan.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">4. Hak Kekayaan Intelektual</h2>
                        <p>
                            Tata letak, kode, dan logo CariKaryawan dilindungi hak cipta. Hak atas teks dan merek pada lowongan tetap dimiliki masing-masing pemilik sumbernya. Penyalinan massal atau scraping otomatis tanpa izin dilarang.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">5. Perilaku Pengguna</h2>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Dilarang membebani atau mengganggu infrastruktur situs (misalnya DDoS).</li>
                            <li>Dilarang memakai bot atau scraper untuk mengambil data tanpa izin tertulis.</li>
                            <li>Dilarang menyalahgunakan tools atau formulir kontak untuk spam atau materi berbahaya.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">6. Hukum yang Berlaku</h2>
                        <p>
                            Ketentuan ini diatur menurut hukum Republik Indonesia. Perselisihan diselesaikan terlebih dahulu secara musyawarah.
                        </p>
                    </section>
                </div>

            {{-- PAGE: KONTAK --}}
            @elseif ($page === 'kontak')
                <header class="mb-8 border-b border-zinc-100 dark:border-zinc-800/80 pb-6">
                    <span class="inline-block text-xs uppercase tracking-widest font-bold text-amber-600 dark:text-amber-400 mb-2">Kontak</span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl font-bold text-zinc-950 dark:text-zinc-50 leading-tight">
                        Hubungi Kami
                    </h1>
                    <p class="mt-3 text-sm sm:text-base text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Untuk pertanyaan umum, kemitraan, atau permintaan perubahan dan penghapusan lowongan.
                    </p>
                </header>

                <div class="max-w-xl space-y-6 text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed">
                    <section>
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Surel</h3>
                        <div class="mt-3 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm" aria-hidden="true">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <a href="mailto:kontak@carikaryawan.com" class="font-semibold text-zinc-900 dark:text-zinc-100 hover:text-amber-600 dark:hover:text-amber-400">kontak@carikaryawan.com</a>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Perubahan atau Penghapusan Lowongan</h3>
                        <p>
                            Jika Anda mewakili pemberi kerja dan ingin lowongan diperbarui atau diturunkan, sertakan tautan halaman lowongan di CariKaryawan dalam email Anda.
                        </p>
                    </section>

                    <section>
                        <h3 class="font-serif-title text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-2">Waktu Respons</h3>
                        <p>
                            Kami berusaha menanggapi dalam 1-3 hari kerja.
                        </p>
                    </section>
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
                        Materi di <strong>CariKaryawan</strong> (<a href="{{ url('/') }}" class="text-amber-600 dark:text-amber-400 underline">https://carikaryawan.com</a>) disediakan untuk tujuan informasi umum.
                    </p>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">1. Bukan Pemberi Kerja</h2>
                        <p>
                            CariKaryawan hanya mengumpulkan dan menampilkan informasi lowongan dari sumber lain. Kami bukan pemberi kerja atau agen penyalur, dan tidak menerima lamaran. Lamaran dikirim melalui tautan asli lowongan di situs sumber.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">2. Keakuratan Informasi</h2>
                        <p>
                            Posisi, gaji, persyaratan, lokasi, dan status lowongan dapat berubah tanpa pemberitahuan. Kami tidak memverifikasi keaslian lowongan maupun perusahaan dan tidak menjamin kelengkapan atau keakuratannya. Gunakan penilaian Anda sendiri, dan waspada terhadap lowongan yang meminta pembayaran atau data sensitif.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">3. Hasil Tools Berbasis AI</h2>
                        <p>
                            CV, surat lamaran, dan pertanyaan wawancara yang dihasilkan tools kami dibuat dengan bantuan AI dan bukan jaminan diterima kerja. Periksa dan sesuaikan sebelum digunakan.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">4. Hak Cipta dan Penghapusan</h2>
                        <p>
                            Hak atas konten lowongan dimiliki masing-masing sumbernya. Jika Anda pemilik konten atau pemberi kerja dan ingin lowongan diubah atau diturunkan, hubungi <strong>kontak@carikaryawan.com</strong> dengan menyertakan tautan halaman terkait.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-serif-title">5. Tautan Eksternal dan Afiliasi</h2>
                        <p>
                            Kami tidak mengendalikan konten situs eksternal yang ditautkan. Sebagian tautan dapat berupa tautan afiliasi dan ditandai <code>sponsored</code>.
                        </p>
                    </section>
                </div>
            @endif

        </article>

    </div>
</div>
