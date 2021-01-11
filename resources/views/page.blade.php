@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">
    @include ('content')

    @if (isset($page))
        @if ($page->id === 2)
            <inquiry></inquiry>
        @endif
    @endif
</div>

@endsection
