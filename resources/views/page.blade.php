@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    @if (!session()->get('editing') || (session()->get('editing') && request('preview')))
        @foreach ($content_elements as $content_element)
            <div class="my-8 first:mt-0 last:mb-0" id="c-{{ $content_element->uuid }}">
                @include ('content-elements.'.$content_element->type, ['content' => $content_element->content, 'first' => $content_elements->filter->isType($content_element->type)->first()->id == $content_element->id])
            </div>
        @endforeach
    @endif

    @if (session()->get('editing') && !request('preview'))
        <content-elements-editor v-if="$store.state.editing"></content-elements-editor>
    @endif

</div>

@endsection
