@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    <div v-if="!$store.state.editing">
        @foreach ($page->published_content_elements as $content_element)
            @include ('content-elements.'.$content_element->type, ['content' => $content_element->content, 'first' => $page->published_content_elements->filter->isType($content_element->type)->first()->id == $content_element->id])
        @endforeach
    </div>

    @if (session()->get('editing'))
        <content-elements-editor v-if="$store.state.editing"></content-elements-editor>
    @endif

</div>

@endsection
