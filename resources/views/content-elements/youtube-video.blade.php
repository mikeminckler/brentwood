<div class="w-full md:flex relative">

    @if (!$content->full_width && !$content->body)
        <div class="flex-1"></div>
    @endif

    <div class="{{ !$content->full_width && !$content->header && !$content->body ? 'flex-3' : 'flex-2'}} flex justify-center items-center relative z-2">
        <div class="{{ !$content->full_width  && ($content->header && $content->body) ? 'md:relative w-full pb-4 md:pb-0' : 'relative w-full' }}">
            <youtube-player :content='@json($content)' uuid="{{ $content_element->uuid }}" :shadow="{{ $content->full_width ? 'false' : 'true' }}">

                <div class="md:bg-white px-8 md:px-16 mt-4 md:py-4 text-gray-600 w-full max-w-2xl md:shadow-lg">

                    <div class="flex items-center">
                        <h1 class="flex-1">{{ $content->header }}</h1>
                        <div class="cursor-pointer h-8 -mr-4 overflow-visible px-2 relative text-primary text-4xl" @click="$eventer.$emit('play-video', '{{ $content_element->uuid }}')"><i class="fab fa-youtube"></i></div>
                    </div>

                    <div class="body">{!! $content->body !!} </div>

                    <div class="flex link" @click="$eventer.$emit('play-video', '{{ $content_element->uuid }}')">
                        <div class=""><i class="fab fa-youtube"></i></div>
                        <div class="pl-2">Play Video</div>
                    </div>

                    <div class="h-1 w-16 bg-gray-400 my-4"></div>

                </div>

            </youtube-player>
        </div>
    </div>

    @if (!$content->full_width && ($content->header && $content->body))
        <div class="flex-1 relative flex items-center justify-center bg-white py-8 border-b-4 border-primary {{ !$content->full_width ? '-ml-8 pl-8' : '' }}">
            <div class="text-block relative {{ $content->style ? 'pt-8' : '' }}">

                <div class="flex items-center">
                    <h{{ $first ? '1' : '2'}} class="flex-1">{{ $content->header }}</h{{ $first ? '1': '2' }}>
                    <div class="cursor-pointer h-8 -mr-4 overflow-visible px-2 relative text-primary text-4xl" @click="$eventer.$emit('play-video', '{{ $content_element->uuid }}')"><i class="fab fa-youtube"></i></div>
                </div>

                <div class="body">
                    {!! $content->body !!}
                </div>

                <div class="flex link" @click="$eventer.$emit('play-video', '{{ $content_element->uuid }}')">
                    <div class=""><i class="fab fa-youtube"></i></div>
                    <div class="pl-2">Play Video</div>
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
