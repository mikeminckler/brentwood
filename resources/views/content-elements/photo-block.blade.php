<div class="relative mt-8 z-2 grid grid-cols-{{ $content->columns }} {{ $content->padding ? ($content->columns === 3 ? 'row-gap-2' : 'gap-2') : '' }}">

    @if ($content->show_text)

        <div class="
                relative 
                col-span-{{ $content->text_span }}
                row-start-{{ ceil($content->text_order / $content->columns) }} 
                col-start-{{ $content->text_order % $content->columns === 0 ? $content->columns : $content->text_order % $content->columns }}
                {{ $content->text_style ? 'photo-block-text-'.$content->text_style : '' }}
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

    @foreach ($content->photos->sortBy('sort_order') as $index => $photo)
        <div class="relative overflow-hidden col-span-{{ $photo->span }}"
            key="photo-{{ $photo->id }}"
            style="padding-bottom: {{ floor($content->height / $photo->span) }}%"
        >
            @if ($index === $content->photos->count() - 1)
                <div class="h-1 bg-gray-200 opacity-50 w-full absolute bottom-0 z-5"></div>
            @endif
            @include ('content-elements.photo', ['photo' => $photo])
        </div>
    @endforeach

</div>
