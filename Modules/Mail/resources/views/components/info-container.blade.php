<style>
    .info-container {
        background-color: #f8f8f8;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        padding: 20px;
        margin: 24px 0;
    }
    .info-row {
        margin: 12px 0;
    }
    .info-label {
        font-size: 13px;
        color: #737373;
        margin-bottom: 4px;
    }
    .info-value {
        font-size: 15px;
        color: #1a1a1a;
        font-weight: 500;
    }
</style>

<div class="info-container">
    {{ $slot }}
</div>
