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
