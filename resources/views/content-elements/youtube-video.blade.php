<div class="w-full md:flex relative">

    @if (!$content->full_width) 
        <div class="flex-1 relative"></div>
    @endif

    <div class="flex-2 flex justify-center relative z-2">
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
