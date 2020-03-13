@extends ('app')

@section ('content')

<div class="flex-1 flex item-center">

    <div class="md:flex-1"></div>

    <div class="flex-2">

        <div class="p-8">
            <h1>{{ $page->name }}</h1>
        </div>

    </div>

</div>

@endsection
