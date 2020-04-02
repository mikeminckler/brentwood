<div class="w-full md:flex">
    <div class="flex-1 relative {{ $content->photos->count() ? 'w-1/2 pb-50p float-right md:w-auto md:pb-0 md:float-none' : '' }}">

        @if ($content->photos->count())
            <div class="absolute w-full h-full">
                @include ('content-elements.photo', ['photo' => $content->photos->first()])
            </div>
        @endif

    </div>

    <div class="flex-2 flex justify-center">

        <div class="text-block relative">
            @if ($content->header)
                <h{{ $first ? '1' : '2'}}>{{ $content->header }}</h{{ $first ? '1': '2' }}>
            @endif
            <div class="body">
                {!! $content->body !!}
            </div>

            @if ($first)
                <div class="h-1 w-16 bg-gray-400 my-4"></div>
            @endif
        </div>

    </div>
</div>
