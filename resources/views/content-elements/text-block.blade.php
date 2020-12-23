@if ($content->header)
    <div class="w-full md:flex relative z-2 {{ $content->full_width ? 'bg-white' : ($content->photos->count() && $content->style ? 'text-style-'.$content->style : '') }} {{ $sort_order === 1 ? 'mt-8' : 'mt-0'}}">

        @if (!$content->full_width)
            <div class="flex-1 relative {{ $content->photos->count() ? 'md:m-0 md:w-auto md:pb-0 md:float-none' : '' }}"></div>
        @endif

        <div class="flex-2 justify-center {{ $content->photos->count() ? 'md:flex' : 'flex' }} {{ !$content->photos->count() && $content->style ? 'text-style-'.$content->style : '' }}">

            <div class="{{ $content->full_width ? 'px-8 md:px-12 pt-4 w-full' : 'text-block' }} relative {{ $content->style ? 'pt-8' : '' }}">
                <h{{ $first ? '1' : '2'}}>{{ $content->header }}</h{{ $first ? '1': '2' }}>
            </div>

        </div>
    </div>
@endif

<div class="w-full md:flex relative z-3 {{ $content->full_width ? 'bg-white' : ($content->photos->count() && $content->style ? 'text-style-'.$content->style : '') }}">
    @if (!$content->full_width)
        <div class="flex-1 relative {{ $content->photos->count() || ($content->stat_number && $content->stat_name) ? 'pb-50p z-3 md:m-0 md:pb-0' : '' }} {{ $content->header ? ( $content->style ? 'md:-mt-20' : 'md:-mt-12' ) : '' }}">

            @if ($content->stat_number && $content->stat_name && !$content->photos->count())
                @include ('content-elements.stat', ['number' => $content->stat_number, 'name' => $content->stat_name, 'photo' => null, 'link' => null ])
            @endif

            @if ($content->photos->count())
                <div class="absolute w-full h-full z-1">
                    @include ('content-elements.photo', ['photo' => $content->photos->first(), 'stat_number' => $content->stat_number, 'stat_name' => $content->stat_name])
                </div>
            @endif

        </div>
    @endif

    <div class="flex-2 justify-center {{ $content->photos->count() || ($content->stat_number && $content->stat_name) ? 'md:flex' : 'flex' }} {{ !$content->photos->count() && $content->style ? 'text-style-'.$content->style : '' }}">

        <div class="{{ $content->full_width ? 'pb-8 px-8 md:px-12 columns-2' : 'text-block' }} relative {{ $content->style ? 'pb-8' : '' }}">
            <div class="body">
                {!! $content->body !!}
            </div>

            @if ($first)
                <div class="h-1 w-16 bg-gray-400 my-4"></div>
            @endif
        </div>

    </div>
</div>
