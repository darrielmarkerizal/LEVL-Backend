<style>
    .code-box {
        background-color: #f8f8f8;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        padding: 20px;
        text-align: center;
        margin: 24px 0;
    }
    .code-label {
        font-size: 13px;
        color: #737373;
        margin-bottom: 8px;
    }
    .code-value {
        font-size: {{ $fontSize ?? '20px' }};
        font-weight: 600;
        color: #1a1a1a;
        letter-spacing: {{ $letterSpacing ?? '1px' }};
        font-family: 'Courier New', monospace;
    }
</style>

<div class="code-box">
    <div class="code-label">{{ $label }}</div>
    <div class="code-value">{{ $value }}</div>
</div>
