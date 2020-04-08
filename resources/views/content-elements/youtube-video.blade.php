<div class="w-full md:flex relative">

    @if (!$content->full_width && !$content->header && !$content->body)
        <div class="flex-1"></div>
    @endif

    <div class="{{ !$content->full_width && !$content->header && !$content->body ? 'flex-3' : 'flex-2'}} flex justify-center items-center relative z-2">
        <div class="{{ !$content->full_width  && ($content->header && $content->body) ? 'md:absolute w-full pb-4 md:pb-video' : 'relative w-full' }}">
            <youtube-player 
                video-id="{{ $content->video_id }}" 
                uuid="{{ $content->contentElement->uuid }}"
                title="{{ $content->title }}"
                :full-width="{{ $content->full_width }}"
                @if ($content->banner)
                :photo="{{ $content->banner }}" 
                @endif
            ></youtube-player>
        </div>
    </div>

    @if (!$content->full_width && ($content->header && $content->body))
        <div class="flex-1 relative">
            <div class="text-block relative {{ $content->style ? 'pt-8' : '' }}">
                <h{{ $first ? '1' : '2'}}>{{ $content->header }}</h{{ $first ? '1': '2' }}>
                <div class="body">
                    {!! $content->body !!}
                </div>

                @if ($first)
                    <div class="h-1 w-16 bg-gray-400 my-4"></div>
                @endif
            </div>
        </div>
    @endif

    @if (!$content->full_width && !$content->header && !$content->body)
        <div class="flex-1"></div>
    @endif
</div>
