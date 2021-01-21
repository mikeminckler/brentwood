
        <div class="blog-footer relative w-full">
            <div class="w-full md:flex relative">
                <div class="flex-1"></div>
                <div class="flex-2 flex justify-center">
                    <div class="text-block flex justify-between items-center">

                        <div class="flex-1">
                            @if ($page->previous_blog)
                                <a class="flex items-center" href="{{ $page->previous_blog->full_slug }}">
                                    <div class="mr-2"><i class="fas fa-arrow-alt-circle-left"></i></div>
                                    <div class="">{{ $page->previous_blog->name }}</div>
                                </a>
                            @endif
                        </div>
                        <div class="px-4"></div>
                        <div class="flex-1">
                            @if ($page->next_blog)
                                <a class="flex items-center" href="{{ $page->next_blog->full_slug }}">
                                    <div class="">{{ $page->next_blog->name }}</div>
                                    <div class="ml-2"><i class="fas fa-arrow-alt-circle-right"></i></div>
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
