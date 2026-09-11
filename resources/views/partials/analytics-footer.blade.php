@php
    $customBody = trim((string) \App\Models\Setting::get('custom_body_scripts', ''));
    $adsterraEnabled = (bool) \App\Models\Setting::get('adsterra_enabled', false);
    $adsterraSocialbar = trim((string) \App\Models\Setting::get('adsterra_socialbar_code', ''));
    $adsterraPopunder = trim((string) \App\Models\Setting::get('adsterra_popunder_code', ''));
@endphp

@if ($adsterraEnabled && !empty($adsterraSocialbar))
    <!-- Adsterra Social Bar -->
    {!! $adsterraSocialbar !!}
@endif

@if ($adsterraEnabled && !empty($adsterraPopunder))
    <!-- Adsterra Popunder -->
    {!! $adsterraPopunder !!}
@endif

@if (!empty($customBody))
    <!-- Custom Body Scripts -->
    {!! $customBody !!}
@endif
