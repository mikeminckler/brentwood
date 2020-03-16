@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">
    <div class="flex item-center">

        <div class="md:flex-1"></div>

        <div class="flex-2">
            <div class="p-8">
                <h1>{{ $page->name }}</h1>
            </div>
        </div>

        @foreach ($page->contentElements as $content_element)
            @include ('content-elements.'.$content_element->type, ['content' => $content_element->content])
        @endforeach

    </div>

    <add-content v-if="$store.state.editing"></add-content>
</div>

@endsection