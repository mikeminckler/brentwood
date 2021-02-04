@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    <div class="">

        <div class="my-4">
            <h1>{{ $livestream->name }}</h1>
            <div class="text-lg font-bold mt-2">{{ $livestream->start_date->timezone('America/Vancouver')->format('l F jS g:ia') }}</div>
        </div>

        <div class="md:flex w-full">
            <div class="flex-3">
                <youtube-player :content='@json($livestream)' uuid="{{ $livestream->id }}" ></youtube-player>
            </div>

            <chat room="livestream.{{ $livestream->id }}"></chat>
        </div>


    </div>
    
</div>

@endsection
