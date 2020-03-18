@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    <div class="flex">
        <div class="md:flex-1"></div>

        <div class="flex-2">
            <div class="px-8">
                <h1>{{ $page->name }}</h1>
            </div>
        </div>
    </div>

    <div v-if="!$store.state.editing">
        @foreach ($page->contentElements->sortBy('sort_order') as $content_element)
            @include ('content-elements.'.$content_element->type, ['content' => $content_element->content])
        @endforeach
    </div>

    <content-elements-editor v-if="$store.state.editing"></content-elements-editor>

</div>

@endsection
