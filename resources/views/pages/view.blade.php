@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    @include ('content')

    @if ($page->type === 'blog')
        @include ('blogs.nav')
    @endif

</div>

@endsection
