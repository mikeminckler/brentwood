@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    <div v-if="!$store.state.editing">
        @foreach ($page->contentElements()->where('unlisted', 0)->get()->sortBy('sort_order') as $content_element)
            @include ('content-elements.'.$content_element->type, ['content' => $content_element->content])
        @endforeach
    </div>

    <content-elements-editor v-if="$store.state.editing"></content-elements-editor>

</div>

@endsection
