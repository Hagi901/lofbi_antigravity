@props([
    'icon' => 'fa-solid fa-inbox',
    'title' => 'Belum Ada Data',
    'message' => 'Belum ada data yang tersedia untuk ditampilkan.',
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => 'fa-solid fa-plus',
    'actionPermission' => true,
])

<div class="empty-state-widget">
    <div class="empty-state-icon">
        <i class="{{ $icon }}"></i>
    </div>
    <h3 class="empty-state-title">{{ $title }}</h3>
    <p class="empty-state-message">{{ $message }}</p>
    @if($actionText && $actionUrl && $actionPermission)
        <a href="{{ $actionUrl }}" class="btn btn-primary">
            @if($actionIcon)
                <i class="{{ $actionIcon }}"></i>
            @endif
            <span>{{ $actionText }}</span>
        </a>
    @endif
    {{ $slot }}
</div>
