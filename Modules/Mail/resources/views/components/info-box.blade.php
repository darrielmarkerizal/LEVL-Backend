@props(['type' => 'info'])

@php
$styles = [
    'info' => 'background-color: #eff6ff; border-left: 3px solid #3b82f6; color: #1e3a8a;',
    'warning' => 'background-color: #fffbeb; border-left: 3px solid #fbbf24; color: #92400e;',
    'success' => 'background-color: #f0fdf4; border-left: 3px solid #22c55e; color: #166534;',
    'danger' => 'background-color: #fef2f2; border-left: 3px solid #ef4444; color: #991b1b;',
];
$style = $styles[$type] ?? $styles['info'];
@endphp

<style>
    .info-box {
        padding: 16px;
        margin: 24px 0;
        font-size: 14px;
        border-radius: 6px;
    }
</style>

<div class="info-box" style="{{ $style }}">
    {{ $slot }}
</div>
