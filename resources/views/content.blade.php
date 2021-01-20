
    @if (!$page->editable || ($page->editable && request('preview')))
        @foreach ($content_elements as $index => $content_element)

            @if ($index === 0 && $content_element->type === 'text-block')
                @include ('sub-menu')
            @endif 

            @if ($content_element->pivot->expandable)
                <expander uuid="{{ $content_element->uuid }}" {{ request('preview') ? ':preview="true"' : '' }}>
            @endif

                <div class="-mt-12 md:-mt-24 pt-16 md:pt-32 first:pt-12 md:first:pt-24 pb-4 md:pb-8 last:pb-0 box-border {{ $index === 0 ? 'first-content-element' : '' }} {{ 'content-element-'.$content_element->type }}"
                    id="c-{{ $content_element->uuid }}">
                @include ('content-elements.'.$content_element->type, [
                        'content' => $content_element->content, 
                        'first' => $content_elements->filter->isType($content_element->type)->first()->id == $content_element->id,
                        'sort_order' => $content_element->pivot->sort_order,
                    ])
                </div>

            @if ($content_element->pivot->expandable)
                </expander>
            @endif

            @if ($index === 0 && $content_element->type !== 'text-block')
                @include ('sub-menu')
            @endif 

        @endforeach
    @endif

    @auth
        @if ($page->editable && !request('preview'))
            <content-elements-editor v-if="$store.state.editing"></content-elements-editor>
        @endif
    @endauth

    @if (!$page->editable || ($page->editable && request('preview')))
        <inquiry></inquiry>
    @endif

    @if ($page->type === 'blog')
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
    @endif

