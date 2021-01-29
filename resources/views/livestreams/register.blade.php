@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">
    @include ('content')

    <div class="md:flex mt-4">
        <div class="flex-1"></div>
        <div class="flex-2 flex justify-center">
            <div class="text-block">
                <h2>{{ $livestream->name }} Registration</h2>
                <div class="text-lg font-bold mt-2">{{ $livestream->start_date->timezone('America/Vancouver')->format('l F jS g:ia') }}</div>
                <p>To register for this online open house please complete the form below.</p>
            </div>
        </div>
    </div>

    <inquiry :livestream='@json($livestream)'></inquiry>
</div>

@endsection
