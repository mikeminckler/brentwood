@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    @if (!session()->get('editing') || (session()->get('editing') && request('preview')))
        @foreach ($content_elements as $content_element)

            @if ($content_element->pivot->expandable)
                <expander uuid="{{ $content_element->uuid }}" {{ request('preview') ? ':preview="true"' : '' }}>
            @endif

                <div class="-mt-12 md:-mt-24 pt-16 md:pt-32 first:pt-12 md:first:pt-24 pb-4 md:pb-8 last:pb-0 box-border" id="c-{{ $content_element->uuid }}">
                @include ('content-elements.'.$content_element->type, [
                        'content' => $content_element->content, 
                        'first' => $content_elements->filter->isType($content_element->type)->first()->id == $content_element->id,
                        'sort_order' => $content_element->pivot->sort_order,
                    ])
                </div>

            @if ($content_element->pivot->expandable)
                </expander>
            @endif

        @endforeach
    @endif

    @if (session()->get('editing') && !request('preview'))
        <content-elements-editor v-if="$store.state.editing"></content-elements-editor>
    @endif

</div>

@endsection
