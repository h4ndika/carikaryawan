<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Halaman Tidak Ditemukan | CariKaryawan</title>
    <meta name="robots" content="noindex,follow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Theme Initialization Script -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#fafaf9] dark:bg-zinc-950 font-sans text-zinc-900 dark:text-zinc-100 antialiased selection:bg-amber-100 selection:text-amber-900 transition-colors duration-300 flex flex-col justify-between">
    
    <!-- Minimal Header -->
    <header class="w-full border-b border-zinc-200/80 dark:border-zinc-800/80 bg-[#fafaf9]/90 dark:bg-zinc-950/90 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                <span class="font-serif-title text-2xl font-bold tracking-tight text-zinc-950 dark:text-white group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors">
                    CariKaryawan<span class="text-amber-800 dark:text-amber-400">.</span>
                </span>
            </a>

            <div class="flex items-center gap-3">
                <!-- Theme Switcher Button -->
                <button type="button" 
                        onclick="toggleTheme()"
                        class="h-9 w-9 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 flex items-center justify-center transition-colors cursor-pointer"
                        aria-label="Ubah Tema">
                    <i class="fa-solid fa-sun text-sm hidden dark:block text-amber-400"></i>
                    <i class="fa-solid fa-moon text-sm block dark:hidden text-zinc-600"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center px-4 py-16 sm:py-24">
        <div class="max-w-xl mx-auto text-center space-y-8">
            
            <!-- Badge & Visual Indicator -->
            <div class="inline-flex items-center justify-center">
                <span class="px-3.5 py-1 rounded-full text-xs font-mono font-bold tracking-wider bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60">
                    KESALAHAN 404
                </span>
            </div>

            <!-- Headings -->
            <div class="space-y-4">
                <h1 class="font-serif-title text-4xl sm:text-5xl lg:text-6xl font-bold text-zinc-950 dark:text-zinc-50 tracking-tight leading-tight">
                    Jejak Terputus di Sini.
                </h1>
                <p class="text-zinc-600 dark:text-zinc-400 text-sm sm:text-base leading-relaxed max-w-md mx-auto">
                    Halaman atau lowongan yang kamu tuju tidak ditemukan, mungkin sudah ditutup atau dipindahkan.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('home') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 dark:bg-zinc-100 px-6 py-3 text-xs uppercase tracking-wider font-bold text-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-white transition-colors shadow-sm">
                    <i class="fa-solid fa-house text-xs"></i>
                    Kembali ke Beranda
                </a>
                <a href="{{ route('search') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-6 py-3 text-xs uppercase tracking-wider font-bold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-400 dark:hover:border-zinc-600 transition-colors">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    Cari Lowongan
                </a>
            </div>

            <!-- Subtle Hint -->
            <div class="pt-8 border-t border-zinc-200/80 dark:border-zinc-800/80">
                <p class="text-xs text-zinc-400 dark:text-zinc-500">
                    Ketik posisi, perusahaan, atau kota di menu pencarian untuk menemukan lowongan terkait.
                </p>
            </div>
        </div>
    </main>

    <!-- Minimal Footer -->
    <footer class="w-full border-t border-zinc-200/80 dark:border-zinc-800/80 py-6 text-center text-xs text-zinc-400 dark:text-zinc-500">
        <p>&copy; {{ date('Y') }} CariKaryawan. Agregator lowongan kerja Indonesia.</p>
    </footer>

    <!-- Theme Toggle Function -->
    <script>
        function toggleTheme() {
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                localStorage.theme = 'light';
                document.documentElement.classList.remove('dark');
            } else {
                localStorage.theme = 'dark';
                document.documentElement.classList.add('dark');
            }
        }
    </script>
</body>
</html>