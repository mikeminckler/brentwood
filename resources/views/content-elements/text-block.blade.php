<div class="w-full flex">
    <div class="flex-1">
    </div>

    <div class="flex-2">

        <div class="text-block">
            @if ($content->header)
                <h2>{{ $content->header }}</h2>
            @endif
            {!! $content->body !!}
        </div>

    </div>
</div>
