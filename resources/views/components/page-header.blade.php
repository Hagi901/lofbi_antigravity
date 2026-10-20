@props([
    'title' => '',
    'subtitle' => null,
])

<div class="page-header-widget">
    <div>
        <h1 class="page-header-title">{{ $title }}</h1>
        @if($subtitle)
            <p class="page-header-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions) || !empty($slot->toHtml()))
        <div class="d-flex align-items-center flex-wrap gap-2">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
