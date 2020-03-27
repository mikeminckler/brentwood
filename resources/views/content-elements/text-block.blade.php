<div class="w-full flex py-8">
    <div class="flex-1 relative">

        @if ($content->photos->count())
            <div class="absolute w-full pb-100p">
                @include ('content-elements.photo', ['photo' => $content->photos->first()])
            </div>
        @endif

    </div>

    <div class="flex-2">

        <div class="text-block relative">
            @if ($content->header)
                <h2>{{ $content->header }}</h2>
            @endif
            {!! $content->body !!}

            @if ($first)
                <div class="h-1 w-16 bg-gray-400 my-4"></div>
            @endif
        </div>

    </div>
</div>
