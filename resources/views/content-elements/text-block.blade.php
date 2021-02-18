<div class="w-full md:flex relative z-3 {{ $content->full_width ? ( !$content->header ? 'bg-white pt-8' : 'bg-white' ) : ($content->photos->count() && $content->style ? 'text-style-'.$content->style : '') }}">
    @if (!$content->full_width)
        <div class="flex-1 relative {{ $content->photos->count() ? 'pb-66p z-3 md:m-0 md:pb-0' : ($content->stat_number && $content->stat_name ? 'pt-4 md:pb-25p z-3 md:m-0 md:pb-0' : '') }}">

            @if ($content->stat_number && $content->stat_name && !$content->photos->count())
                <div class="hidden md:block">
                    @include ('content-elements.stat', ['number' => $content->stat_number, 'name' => $content->stat_name, 'photo' => null, 'link' => null ])
                </div>
            @endif

            @if ($content->photos->count())
                <div class="absolute w-full h-full z-1">
                    @include ('content-elements.photo', ['photo' => $content->photos->first(), 'stat_number' => $content->stat_number, 'stat_name' => $content->stat_name])
                </div>
            @endif

        </div>
    @endif

    <div class="flex-2 justify-center flex {{ !$content->photos->count() && $content->style ? 'text-style-'.$content->style : '' }}">

        <div class="{{ $content->full_width ? 'py-8 px-8 md:px-12 columns-2' : 'text-block' }} relative z-4 py-8">

            <h{{ $first ? '1' : '2'}}>{{ $content->header }}</h{{ $first ? '1': '2' }}>

            <div class="body">
                {!! $content->body !!}
            </div>

            @if ($first)
                <div class="h-1 w-16 bg-gray-400 my-2"></div>
            @endif
        </div>

    </div>

    @if ($content->stat_number && $content->stat_name && !$content->photos->count())
        <div class="block md:hidden pb-25p relative -mt-4">
            @include ('content-elements.stat', ['number' => $content->stat_number, 'name' => $content->stat_name, 'photo' => null, 'link' => null ])
        </div>
    @endif

</div>
