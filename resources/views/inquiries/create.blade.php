@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">
    @include ('content')

    @if ($livestream)
        <div class="md:flex mt-4">
            <div class="flex-1"></div>
            <div class="flex-2 flex justify-center">
                <div class="text-block">
                    <h2>Online Open House Registration</h2>
                    <div class="py-2 text-gray-500">{{ $livestream->start_date->format('Y-m-d h:ma') }}</div>
                    <p>To register for this online open house please complete the form below.</p>
                </div>
            </div>
        </div>
    @endif

    <inquiry :livestream='@json($livestream)'></inquiry>
</div>

@endsection
