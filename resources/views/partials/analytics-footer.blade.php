@php
    $customBody = trim((string) \App\Models\Setting::get('custom_body_scripts', ''));
@endphp

@if (!empty($customBody))
    <!-- Custom Body Scripts -->
    {!! $customBody !!}
@endif
