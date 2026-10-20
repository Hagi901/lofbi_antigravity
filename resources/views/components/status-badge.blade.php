@props([
    'status' => '',
    'text' => null,
    'type' => null,
    'icon' => null,
])

@php
    $normalized = strtolower(trim((string) $status));
    $badgeType = $type;
    $badgeIcon = $icon;
    $displayText = $text ?? ucwords(str_replace('_', ' ', $normalized));

    if (!$badgeType) {
        switch ($normalized) {
            case 'disetujui':
            case 'aktif':
            case 'baik':
            case 'selesai':
                $badgeType = 'success';
                $badgeIcon = $badgeIcon ?? 'fa-solid fa-circle-check';
                break;
            case 'menunggu':
            case 'dalam_proses':
            case 'sebagian':
            case 'disetujui sebagian':
            case 'disetujui_sebagian':
            case 'rusak ringan':
            case 'rusak_ringan':
                $badgeType = 'warning';
                $badgeIcon = $badgeIcon ?? 'fa-solid fa-clock';
                break;
            case 'ditolak':
            case 'rusak berat':
            case 'rusak_berat':
            case 'hilang':
            case 'batal':
                $badgeType = 'danger';
                $badgeIcon = $badgeIcon ?? 'fa-solid fa-circle-xmark';
                break;
            case 'keluar':
                $badgeType = 'danger';
                $badgeIcon = $badgeIcon ?? 'fa-solid fa-arrow-up-from-bracket';
                break;
            case 'masuk':
                $badgeType = 'primary';
                $badgeIcon = $badgeIcon ?? 'fa-solid fa-arrow-down-to-bracket';
                break;
            default:
                $badgeType = 'secondary';
                $badgeIcon = $badgeIcon ?? 'fa-solid fa-circle-info';
                break;
        }
    }
@endphp

<span class="badge-app badge-app-{{ $badgeType }}">
    @if($badgeIcon)
        <i class="{{ $badgeIcon }}"></i>
    @endif
    <span>{{ $displayText }}</span>
</span>
