@props([
    'slotName' => '',
    'class' => '',
    'label' => true,
])

@php
    $isEnabled = (bool) \App\Models\Setting::get($slotName . '_enabled', false);
    $adCode = trim((string) \App\Models\Setting::get($slotName . '_code', ''));
    $globalAdSense = (bool) \App\Models\Setting::get('adsense_enabled', false);
    $clientId = trim((string) \App\Models\Setting::get('adsense_client_id', ''));
@endphp

@if ($isEnabled && (!empty($adCode) || ($globalAdSense && !empty($clientId))))
    <div class="ad-container my-6 w-full flex flex-col items-center justify-center overflow-hidden {{ $class }}">
        @if ($label)
            <div class="text-[9px] uppercase tracking-widest text-zinc-400 dark:text-zinc-600 font-semibold mb-1.5 self-center">
                Sponsor / Iklan
            </div>
        @endif
        <div class="w-full flex justify-center items-center rounded-xl bg-zinc-50/70 dark:bg-zinc-900/60 border border-zinc-200/80 dark:border-zinc-800/80 p-2 sm:p-4 min-h-[90px]">
            @if (!empty($adCode))
                {!! $adCode !!}
            @else
                <!-- Responsive Google AdSense Fallback -->
                <ins class="adsbygoogle"
                     style="display:block; text-align:center;"
                     data-ad-layout="in-article"
                     data-ad-format="fluid"
                     data-ad-client="{{ $clientId }}"></ins>
                <script>
                     (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            @endif
        </div>
    </div>
@endif
