@props([
    'value' => 0,
    'decimals' => 0,
    'showPrefix' => true,
])

@php
    $num = is_numeric($value) ? (float) $value : 0;
    $formatted = number_format($num, $decimals, ',', '.');
@endphp
<span>{{ $showPrefix ? 'Rp ' : '' }}{{ $formatted }}</span>
