@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    <div class="">

        <div class="my-4">
            <h1>{{ $livestream->name }}</h1>
            <div class="py-2 text-gray-500">{{ $livestream->start_date->format('Y-m-d h:ma') }}</div>
        </div>

        <div class="md:flex w-full">
            <div class="flex-3">
                <youtube-player :content='@json($livestream)' uuid="{{ $livestream->id }}" ></youtube-player>
            </div>

            <div class="flex-1">
                <chat room-id="livestream-{{ $livestream->id }}"></chat>
            </div>
        </div>


    </div>
    
</div>

@endsection
