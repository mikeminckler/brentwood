<div class="flex relative">

    <div class="flex-1">
        <div class="px-4">

            <h2>{{ $content->header }}</h2>

            @if ($content->tags->count())
                <div class="flex mt-2">
                    @foreach ($content->tags as $tag)
                        <a class="tag" href="{{ $tag->url }}">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif

            <div class="mt-4">
                @foreach ($content->blogs->items() as $blog)
                    <div class=""><a class="" href="{{ $blog->full_slug }}">{{ $blog->name }}</a></div>
                @endforeach
            </div>

            @if ($content->blogs->hasPages())
                <div class="flex justify-center items-center w-full">

                    <div class="flex-1 h-0 border-t border-gray-400 pr-2"></div>

                    <div class="px-2 mx-2 w-4 flex justify-center">
                        @if ($content->blogs->onFirstPage())
                            <div class="text-gray-400"><i class="fas fa-chevron-left"></i></div>
                        @else
                            <a href="{{ $content->blogs->previousPageUrl() }}" class="text-primary cursor-pointer"><i class="fas fa-chevron-left"></i></a>
                        @endif
                    </div>

                    <div class="px-2 mx-2 w-4 flex justify-center">
                        @if ($content->blogs->hasMorePages())
                            <a href="{{ $content->blogs->nextPageUrl() }}" class="text-primary cursor-pointer"><i class="fas fa-chevron-right"></i></a>
                        @else
                            <div class="text-gray-400"><i class="fas fa-chevron-right"></i></div>
                        @endif
                    </div>

                    <div class="flex-1 h-0 border-t border-gray-400 pl-2"></div>

                </div>
            @endif

        </div>
    </div>

    <div class="flex-2">

        <div class="px-8 flex flex-col overflow-hidden h-full">

            <div class="flex-1 flex flex-col">

                <div class="flex-1 overflow-hidden mt-4">
                    <div class="h-0 overflow-visible">
                        @include ('content', ['page' => $content->blogs->first(), 'content_elements' => $content->blogs->first()->content_elements]);
                    </div>
                </div>

                <div class="my-2 flex justify-end">
                    <a href="{{ $content->blogs->first()->full_slug }}">Read More...</a>
                </div>

            </div>

        </div>

    </div>

</div>

