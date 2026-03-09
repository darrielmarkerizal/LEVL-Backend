@props(['status'])

@php
$statusStyles = [
    'pending' => 'background-color: #fffbeb; color: #92400e;',
    'active' => 'background-color: #f0fdf4; color: #166534;',
    'completed' => 'background-color: #eff6ff; color: #1e3a8a;',
    'cancelled' => 'background-color: #fef2f2; color: #991b1b;',
    'declined' => 'background-color: #fef2f2; color: #991b1b;',
];
$style = $statusStyles[$status] ?? $statusStyles['pending'];
@endphp

<style>
    .status-box {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        margin: 8px 0;
    }
</style>

<span class="status-box" style="{{ $style }}">
    {{ $slot }}
</span>
