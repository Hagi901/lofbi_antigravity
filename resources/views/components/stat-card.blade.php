@props([
    'label' => '',
    'value' => '0',
    'subtext' => null,
    'icon' => 'fa-solid fa-chart-simple',
    'color' => 'primary', // primary, success, warning, danger, info
    'url' => null,
])

@php
    $tag = $url ? 'a' : 'div';
    $hrefAttr = $url ? 'href="' . $url . '"' : '';
@endphp

<{{ $tag }} {!! $hrefAttr !!} class="stat-card-widget stat-{{ $color }} text-decoration-none h-100">
    <div class="pe-2">
        <div class="stat-label">{{ $label }}</div>
        <div class="stat-value">{{ $value }}</div>
        @if($subtext)
            <div class="stat-subtext">{{ $subtext }}</div>
        @endif
    </div>
    <div class="stat-icon">
        <i class="{{ $icon }}"></i>
    </div>
</{{ $tag }}>
