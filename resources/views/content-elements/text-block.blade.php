@if ($content->header)
    <div class="w-full md:flex relative z-2 {{ $content->photos->count() && $content->style ? 'text-style-'.$content->style : '' }} {{ $sort_order === 1 ? 'mt-8' : 'mt-0'}}">
        <div class="flex-1 relative {{ $content->photos->count() ? 'md:m-0 md:w-auto md:pb-0 md:float-none' : '' }}"></div>

        <div class="flex-2 justify-center {{ $content->photos->count() ? 'md:flex' : 'flex' }} {{ !$content->photos->count() && $content->style ? 'text-style-'.$content->style : '' }}">

            <div class="text-block relative {{ $content->style ? 'pt-8' : '' }}">
                <h{{ $first ? '1' : '2'}}>{{ $content->header }}</h{{ $first ? '1': '2' }}>
            </div>

        </div>
    </div>
@endif

<div class="w-full md:flex relative z-3 {{ $content->photos->count() && $content->style ? 'text-style-'.$content->style : '' }}">
    <div class="flex-1 relative {{ $content->photos->count() || ($content->stat_number && $content->stat_name) ? 'w-1/2 pb-50p float-right z-3 m-2 mt-8 md:m-0 md:w-auto md:pb-0 md:float-none' : '' }} {{ $content->header ? ( $content->style ? 'md:-mt-20' : 'md:-mt-12' ) : '' }}">

        @if ($content->stat_number && $content->stat_name)
            @include ('content-elements.stat', ['number' => $content->stat_number, 'name' => $content->stat_name, 'photo' => $content->photos->count() ? true : false ])
        @endif

        @if ($content->photos->count())
            <div class="absolute w-full h-full z-2">
                @include ('content-elements.photo', ['photo' => $content->photos->first()])
            </div>
        @endif

    </div>

    <div class="flex-2 justify-center {{ $content->photos->count() || ($content->stat_number && $content->stat_name) ? 'md:flex' : 'flex' }} {{ !$content->photos->count() && $content->style ? 'text-style-'.$content->style : '' }}">

        <div class="text-block relative {{ $content->style ? 'pb-8' : '' }}">
            <div class="body">
                {!! $content->body !!}
            </div>

            @if ($first)
                <div class="h-1 w-16 bg-gray-400 my-4"></div>
            @endif
        </div>

    </div>
</div>
