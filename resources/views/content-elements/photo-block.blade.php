<div class="relative z-2 md:grid md:grid-cols-{{ $content->columns }} {{ $content->padding ? ($content->columns === 3 ? 'gap-y-2' : 'gap-2') : '' }}">


    @foreach ($content->photos->sortBy('sort_order') as $index => $photo)
        <div class="w-full md:w-auto relative overflow-hidden col-span-{{ $photo->span }} pb-50p md:pb-{{ floor($content->height / $photo->span) }}p"
            key="photo-{{ $photo->id }}"
        >
            @if ($index === $content->photos->count() - 1)
                <div class="h-1 bg-gray-200 opacity-50 w-full absolute bottom-0 z-5"></div>
            @endif
            @include ('content-elements.photo', ['photo' => $photo])
        </div>
    @endforeach

    @if ($content->show_text)

        <div class="
                relative flex justify-center
                py-4
                ml-1 md:ml-0
                col-span-{{ $content->text_span }}
                row-start-{{ ceil($content->text_order / $content->columns) }} 
                col-start-{{ $content->text_order % $content->columns === 0 ? $content->columns : $content->text_order % $content->columns }}
                {{ $content->text_style ? 'text-style-'.$content->text_style : '' }}
            " 
            key="text"
        >

            <div class="text-block flex flex-col justify-center h-full">
                @if ($content->header)
                    <h2>{{ $content->header }}</h2>
                @endif
                {!! $content->body !!}
            </div>

        </div>

    @endif

    <div class="hidden md:grid-cols-1 md:grid-cols-2 md:grid-cols-3 md:grid-cols-4"></div>

</div>
