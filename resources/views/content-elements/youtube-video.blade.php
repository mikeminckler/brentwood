<div class="w-full flex py-8 relative">
    <div class="flex-1 relative">
    </div>

    <div class="flex-2 flex justify-center relative z-2">
        <youtube-player 
            video-id="{{ $content->video_id }}" 
            uuid="{{ $content->contentElement->uuid }}"
            title="{{ $content->title }}"
            @if ($content->banner)
                :photo="{{ $content->banner }}" 
            @endif
        ></youtube-player>
    </div>
</div>
