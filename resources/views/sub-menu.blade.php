@if (isset($page))
    @if ($page->sub_menu)
        <div class="hidden md:block relative -mt-8 mb-8">
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
@endif
