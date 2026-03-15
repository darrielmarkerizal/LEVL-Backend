@component('mail::components.email-layout', ['title' => __('mail.post_published.title')])
    <h1>{{ __('mail.post_published.title') }}</h1>

    <p>{!! __('mail.post_published.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.post_published.body') }}</p>

    @component('mail::components.info-container')
        <div style="margin-bottom: 12px;">
            <strong>{{ __('mail.post_published.post_title_label') }}:</strong><br>
            {{ $post->title }}
        </div>
        <div style="margin-bottom: 12px;">
            <strong>{{ __('mail.post_published.category_label') }}:</strong><br>
            <span style="display: inline-block; padding: 4px 12px; background-color: #f3f4f6; border-radius: 4px; font-size: 14px;">
                {{ $post->category->icon() }} {{ $post->category->label() }}
            </span>
        </div>
        @if($post->content)
            <div>
                <strong>{{ __('mail.post_published.excerpt_label') }}:</strong><br>
                <div style="color: #6b7280; line-height: 1.6;">
                    {{ Str::limit(strip_tags($post->content), 150) }}
                </div>
            </div>
        @endif
    @endcomponent

    @component('mail::components.button', ['url' => $postUrl])
        {{ __('mail.post_published.button') }}
    @endcomponent

    @component('mail::components.info-box', ['type' => 'info'])
        {!! __('mail.post_published.info') !!}
    @endcomponent

    @slot('footer')
        <p>{{ __('mail.post_published.footer') }}</p>
    @endslot
@endcomponent
