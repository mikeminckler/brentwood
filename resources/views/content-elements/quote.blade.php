<div class="md:flex justify-center relative z-2">

    <div class="relative flex-1 bg-gray-200 md:w-1/2">

        <div class="md:p-4 absolute z-2 font-light text-gray-400 leading-none" style="font-size: 150px">&ldquo;</div>
        
        <div class="px-8 pt-2 pb-4 md:px-16 md:py-8 text-gray-700">
            <div class="relative z-3 italic leading-relaxed my-4">{!! $content->body !!}</div>

            <div class="h-1 w-16 bg-primary mb-4"></div>

            <div class="flex flex-col w-full md:items-end">
                <div class="">
                    {{ $content->author_name }}<br/>
                    {{ $content->author_details }}
                </div>
            </div>
        </div>

    </div>

    @if ($content->photos->count())
        <div class="flex-1 flex bg-gray-100 relative w-full md:w-auto pb-75p md:pb-0">

            <div class="h-1 bg-gray-200 opacity-50 w-full absolute bottom-0 z-5"></div>
            @include ('content-elements.photo', ['photo' => $content->photos->first()])

        </div>
    @endif

</div>
