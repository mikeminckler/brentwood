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

            <div class="text-block flex flex-col justify-center h-full columns-{{ $content->text_span }}">
                @if ($content->header)
                    <h2>{{ $content->header }}</h2>
                @endif
                {!! $content->body !!}
            </div>

        </div>

    @endif

    @foreach ($content->photos as $photo)
        <div class="relative overflow-hidden col-span-{{ $photo->span }}"
            key="photo-{{ $photo->id }}"
            style="padding-bottom: {{ floor($content->height / $photo->span) }}%"
        >
            @include ('content-elements.photo', ['photo' => $photo])
        </div>
    @endforeach

</div>
