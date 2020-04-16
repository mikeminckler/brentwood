
    <div class="relative z-2">

        @if ($content->photos->count())
            <div class="relative pb-50p md:pb-40p">
                <div class="absolute w-full h-full bg-gray-100 flex flex-col items-center justify-center">
                    @include ('content-elements.photo', ['photo' => $content->photos->first()])
                </div>
            </div>
        @endif

        <div class="relative flex md:-mt-16 justify-center z-4">

            <div class="md:bg-white px-8 md:px-16 py-8 text-gray-600 w-full max-w-2xl md:shadow-lg">
                @if ($content->header)
                    <h{{ $index === 1 ? '1' : '2'}}>{{ $content->header }}</h{{ $index === 1 ? '1': '2' }}>
                @endif

                <div class="body">
                    {!! $content->body !!}
                </div>

                <div class="h-1 w-16 bg-gray-400 my-4"></div>

            </div>

        </div>

    </div>
