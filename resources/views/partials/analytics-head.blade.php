@php
    $gaEnabled = (bool) \App\Models\Setting::get('ga_enabled', false);
    $gaId = trim((string) \App\Models\Setting::get('ga_measurement_id', ''));
    $gtmEnabled = (bool) \App\Models\Setting::get('gtm_enabled', false);
    $gtmId = trim((string) \App\Models\Setting::get('gtm_container_id', ''));
    $umamiEnabled = (bool) \App\Models\Setting::get('umami_enabled', false);
    $umamiId = trim((string) \App\Models\Setting::get('umami_website_id', ''));
    $umamiSrc = trim((string) \App\Models\Setting::get('umami_script_url', 'https://cloud.umami.is/script.js'));
    $customHead = trim((string) \App\Models\Setting::get('custom_head_scripts', ''));
@endphp

@if ($gaEnabled && !empty($gaId))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');

        document.addEventListener('livewire:navigated', () => {
            gtag('event', 'page_view', {
                page_location: window.location.href,
                page_path: window.location.pathname,
                page_title: document.title
            });
        });
    </script>
@endif

@if ($gtmEnabled && !empty($gtmId))
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    <!-- End Google Tag Manager -->
@endif

@if ($umamiEnabled && !empty($umamiId))
    <!-- Umami Analytics -->
    <script defer src="{{ $umamiSrc ?: 'https://cloud.umami.is/script.js' }}" data-website-id="{{ $umamiId }}"></script>
@endif

@if (!empty($customHead))
    <!-- Custom Head Scripts -->
    {!! $customHead !!}
@endif
