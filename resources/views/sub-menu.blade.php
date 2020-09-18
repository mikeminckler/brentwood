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

    @if ($page->author || $page->tags)
        <div class="w-full md:flex relative">
            <div class="flex-1"></div>
            <div class="flex-2 flex justify-center">
                <div class="text-block flex justify-between">
                    <div class="italic">{{ $page->author }}</div>
                    <div class="">
                        @foreach ($page->tags as $tag)
                            <div class="mx-4 px-2 py-1 rounded-lg bg-gray-200 border border-gray-300">{{ $tag->name }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

@endif


