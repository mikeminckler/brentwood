@if (isset($page))
    @if ($page->sub_menu && $page->show_sub_menu)
        <div class="hidden md:block relative">
            <div class="absolute z-3 w-full bottom-0">
                <div class="bg-white bg-opacity-75 flex shadow justify-end">
                    @foreach ($page->sub_menu as $menu_page)
                        @if (!$menu_page->unlisted && $menu_page->published_version_id)
                            <a href="{{ $menu_page->full_slug }}" class="px-4 py-2 font-oswald font-light hover:underline">{{ $menu_page->name }}</a>
                        @endif
                    @endforeach 
                </div>
            </div>
        </div>
    @endif

    @if ($page->type === 'blog')
        <div class="blog-header relative w-full">
            <div class="w-full md:flex relative blog-title">
                <div class="flex-1"></div>
                <div class="flex-2 flex justify-center">
                    <div class="text-block flex justify-between items-center">
                        <h1>{{ $page->name }}</h1>
                    </div>
                </div>
            </div>
            <div class="w-full md:flex relative blog-author">
                <div class="flex-1"></div>
                <div class="flex-2 flex justify-center">
                    <div class="text-block flex justify-between items-center py-1">
                        <div class="italic">{{ $page->author }}</div>
                        <div class="">{{ $page->published_at->format('F j, Y g:ia') }}</div>
                    </div>
                </div>
            </div>
            @if ($page->tags) 
                <div class="w-full md:flex relative mt-2 -mb-2 blog-tags">
                    <div class="flex-1"></div>
                    <div class="flex-2 flex justify-center">
                        <div class="text-block flex items-center">
                            @foreach ($page->tags as $tag)
                                <a href="/tags/{{ $tag->id }}" class="tag">{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

@endif


