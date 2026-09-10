@props([
    'lat' => -6.2088,
    'lon' => 106.8456,
    'locationName' => 'Sekitar',
    'compact' => false,
])

<div x-data="weatherAqiWidget({{ (float) $lat }}, {{ (float) $lon }}, '{{ addslashes($locationName) }}')" 
     x-init="init()"
     class="w-full my-4 text-xs">
    
    <!-- Loading State -->
    <template x-if="loading">
        <div class="flex items-center gap-2 p-3 rounded-xl bg-zinc-50 dark:bg-zinc-900/60 border border-zinc-200/70 dark:border-zinc-800 animate-pulse text-zinc-400">
            <i class="fa-solid fa-cloud-sun text-amber-500 text-sm animate-bounce"></i>
            <span>Memeriksa cuaca & kualitas udara {{ $locationName }}...</span>
        </div>
    </template>

    <!-- Error Fallback (Hidden cleanly) -->
    <template x-if="error && !loading">
        <div class="hidden"></div>
    </template>

    <!-- Content State -->
    <template x-if="!loading && !error && data">
        <div class="p-3 sm:p-4 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs transition-all">
            <div class="flex flex-wrap items-center justify-between gap-3">
                
                <!-- Left: Weather Info -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-lg flex-shrink-0"
                         :class="data.weatherBg">
                        <i :class="data.weatherIcon"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 font-semibold text-zinc-900 dark:text-zinc-100">
                            <span class="text-sm font-bold" x-text="data.temp + '°C'"></span>
                            <span>·</span>
                            <span x-text="data.weatherLabel"></span>
                        </div>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                            <span><i class="fa-solid fa-droplet text-blue-400 text-[10px] mr-1"></i><span x-text="data.humidity + '% lembap'"></span></span>
                            <span>·</span>
                            <span><i class="fa-solid fa-wind text-teal-400 text-[10px] mr-1"></i><span x-text="data.windSpeed + ' km/h'"></span></span>
                        </div>
                    </div>
                </div>

                <!-- Right: Air Quality (AQI) & Outdoor Recommendation -->
                <div class="flex items-center gap-3">
                    <div class="flex flex-col items-end text-right">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500">Kualitas Udara:</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold inline-flex items-center gap-1"
                                  :class="data.aqiBadgeClass">
                                <span class="w-1.5 h-1.5 rounded-full" :class="data.aqiDotClass"></span>
                                <span x-text="'AQI ' + data.aqi + ' (' + data.aqiLabel + ')'"></span>
                            </span>
                        </div>
                        <span class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5" x-text="data.advice"></span>
                    </div>
                </div>

            </div>
        </div>
    </template>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('weatherAqiWidget', (lat, lon, locationName) => ({
        lat: lat,
        lon: lon,
        locationName: locationName,
        loading: true,
        error: false,
        data: null,

        async init() {
            if (!this.lat || !this.lon) {
                this.loading = false;
                this.error = true;
                return;
            }

            const cacheKey = `disekitar_wx_${this.lat.toFixed(2)}_${this.lon.toFixed(2)}`;
            const cached = sessionStorage.getItem(cacheKey);

            if (cached) {
                try {
                    const parsed = JSON.parse(cached);
                    if (Date.now() - parsed.timestamp < 10 * 60 * 1000) { // 10 menit TTL
                        this.data = parsed.data;
                        this.loading = false;
                        return;
                    }
                } catch (e) {}
            }

            try {
                // Fetch Weather & AQI secara paralel dari Open-Meteo
                const [weatherRes, aqiRes] = await Promise.all([
                    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${this.lat}&longitude=${this.lon}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&timezone=Asia%2FJakarta`),
                    fetch(`https://air-quality-api.open-meteo.com/v1/air-quality?latitude=${this.lat}&longitude=${this.lon}&current=us_aqi,pm2_5&timezone=Asia%2FJakarta`)
                ]);

                if (!weatherRes.ok) throw new Error('Failed to fetch weather');

                const wxJson = await weatherRes.json();
                const aqiJson = aqiRes.ok ? await aqiRes.json() : null;

                const currWx = wxJson.current || {};
                const currAqi = aqiJson?.current || {};

                const temp = Math.round(currWx.temperature_2m ?? 28);
                const humidity = Math.round(currWx.relative_humidity_2m ?? 75);
                const windSpeed = Math.round(currWx.wind_speed_10m ?? 10);
                const weatherCode = currWx.weather_code ?? 0;
                const aqi = Math.round(currAqi.us_aqi ?? 45);

                const weatherMeta = this.parseWeatherCode(weatherCode);
                const aqiMeta = this.parseAqi(aqi, temp);

                this.data = {
                    temp,
                    humidity,
                    windSpeed,
                    weatherLabel: weatherMeta.label,
                    weatherIcon: weatherMeta.icon,
                    weatherBg: weatherMeta.bg,
                    aqi,
                    aqiLabel: aqiMeta.label,
                    aqiBadgeClass: aqiMeta.badgeClass,
                    aqiDotClass: aqiMeta.dotClass,
                    advice: aqiMeta.advice,
                };

                sessionStorage.setItem(cacheKey, JSON.stringify({
                    timestamp: Date.now(),
                    data: this.data
                }));
            } catch (err) {
                this.error = true;
            } finally {
                this.loading = false;
            }
        },

        parseWeatherCode(code) {
            if (code === 0) {
                return { label: 'Cerah', icon: 'fa-solid fa-sun text-amber-500', bg: 'bg-amber-50 dark:bg-amber-950/40 text-amber-600' };
            }
            if (code >= 1 && code <= 3) {
                return { label: 'Cerah Berawan', icon: 'fa-solid fa-cloud-sun text-amber-500', bg: 'bg-sky-50 dark:bg-sky-950/40 text-sky-600' };
            }
            if (code === 45 || code === 48) {
                return { label: 'Berkabut', icon: 'fa-solid fa-smog text-zinc-400', bg: 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600' };
            }
            if (code >= 51 && code <= 55) {
                return { label: 'Gerimis Ringan', icon: 'fa-solid fa-cloud-rain text-blue-400', bg: 'bg-blue-50 dark:bg-blue-950/40 text-blue-600' };
            }
            if (code >= 61 && code <= 65) {
                return { label: 'Hujan', icon: 'fa-solid fa-cloud-showers-heavy text-blue-500', bg: 'bg-blue-50 dark:bg-blue-950/40 text-blue-600' };
            }
            if (code >= 80 && code <= 82) {
                return { label: 'Hujan Lebat', icon: 'fa-solid fa-cloud-showers-water text-indigo-500', bg: 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600' };
            }
            if (code >= 95) {
                return { label: 'Hujan Petir', icon: 'fa-solid fa-bolt text-amber-600', bg: 'bg-amber-100 dark:bg-amber-950/60 text-amber-700' };
            }
            return { label: 'Berawan', icon: 'fa-solid fa-cloud text-zinc-400', bg: 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600' };
        },

        parseAqi(aqi, temp) {
            if (aqi <= 50) {
                return {
                    label: 'Baik / Segar',
                    badgeClass: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                    dotClass: 'bg-emerald-500',
                    advice: '🌿 Udara sangat segar, ideal untuk eksplorasi dan jalan-jalan.'
                };
            }
            if (aqi <= 100) {
                return {
                    label: 'Sedang',
                    badgeClass: 'bg-yellow-50 dark:bg-yellow-950/40 text-yellow-800 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800',
                    dotClass: 'bg-yellow-500',
                    advice: '🌤️ Kondisi udara aman untuk sebagian besar aktivitas luar ruangan.'
                };
            }
            if (aqi <= 150) {
                return {
                    label: 'Sensitif',
                    badgeClass: 'bg-orange-50 dark:bg-orange-950/40 text-orange-800 dark:text-orange-300 border border-orange-200 dark:border-orange-800',
                    dotClass: 'bg-orange-500',
                    advice: '😷 Kelompok sensitif disarankan memakai masker saat beraktivitas.'
                };
            }
            return {
                label: 'Kurang Sehat',
                badgeClass: 'bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                dotClass: 'bg-rose-500',
                advice: '⚠️ Polusi tinggi, pertimbangkan berkunjung ke tempat tertutup (indoor).'
            };
        }
    }));
});
</script>
