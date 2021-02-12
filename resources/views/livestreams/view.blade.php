@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">

    <div class="">

        <div class="flex flex-col items-center md:items-start m-4">
            <h1>{{ $livestream->name }}</h1>
            <div class="text-lg font-bold mt-2">{{ $livestream->date }}</div>
        </div>

        <div class="md:flex w-full">
            <div class="flex-3">
                <youtube-player :content='@json($livestream)' uuid="{{ $livestream->id }}" ></youtube-player>
            </div>

            @if ($livestream->enable_chat)
                <chat room="{{ $livestream->chat_room }}" :moderators='@json($livestream->moderators)'></chat>
            @endif
        </div>


    </div>
    
</div>

@endsection
