<style>
    .course-box {
        background-color: #f8f8f8;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        padding: 20px;
        margin: 24px 0;
    }
    .course-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0 0 8px 0;
    }
    .course-code {
        font-size: 14px;
        color: #737373;
        margin: 0;
    }
</style>

<div class="course-box">
    <div class="course-title">{{ $title }}</div>
    @if(isset($code))
        <div class="course-code">{{ $code }}</div>
    @endif
</div>
