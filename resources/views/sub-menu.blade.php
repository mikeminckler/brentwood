@if (isset($page))
    @if ($page->sub_menu)
        <div class="hidden relative -mt-8 mb-8">
            <div class="absolute z-3 flex bg-gray-100 w-full justify-center">
                @foreach ($page->sub_menu as $menu_page)
                    @if (!$menu_page->unlisted && $menu_page->published_version_id)
                        <div class="px-4 py-2 mx-2">
                            <a href="{{ $menu_page->full_slug }}" class="font-oswald font-light">{{ $menu_page->name }}</a>
                        </div>
                    @endif
                @endforeach 
            </div>
        </div>
    @endif

    @if ($page->type === 'blog')
        <div class="w-full md:flex relative">
            <div class="flex-1"></div>
            <div class="flex-2 flex justify-center">
                <div class="text-block flex justify-between items-center">
                    <h1>{{ $page->name }}</h1>
                </div>
            </div>
        </div>
        <div class="w-full md:flex relative">
            <div class="flex-1"></div>
            <div class="flex-2 flex justify-center">
                <div class="text-block flex justify-between items-center py-1">
                    <div class="italic">{{ $page->author }}</div>
                    <div class="">{{ $page->published_at->format('F j, Y g:ia') }}</div>
                </div>
            </div>
        </div>
        @if ($page->tags) 
            <div class="w-full md:flex relative mt-2 -mb-2">
                <div class="flex-1"></div>
                <div class="flex-2 flex justify-center">
                    <div class="text-block flex items-center">
                        @foreach ($page->tags as $tag)
                            <a href="/tags/{{ $tag->id }}" class="mr-4 px-2 py-1 rounded-lg bg-gray-200 border border-gray-300">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

@endif


