@if ($photo->link)
    <a class="absolute w-full h-full" href="{{ $photo->link }}">
@endif

<picture class="photo {{ $photo->fill ? 'fill' : 'fit' }} {{ $photo->link ? 'cursor-pointer' : 'cursor-zoom-in' }}" 

@if (!$photo->link)
    @click="$eventer.$emit('view-photo', '{{ $photo->large }}')"
@endif

>

    <source
        media="(min-width: 900px)"
        srcset="{{ $photo->large }}.webp"
        type="image/webp" >
    <source
        media="(min-width: 400px)"
        srcset="{{ $photo->medium }}.webp"
        type="image/webp" >
    <source
        srcset="{{ $photo->small }}.webp"
        type="image/webp" >
    <img 
        srcset="{{ $photo->small }} 400w, {{ $photo->medium }} 900w, {{ $photo->large }} 1152w"
        src="{{ $photo->large }}"
        type="image/{{ optional($photo->fileUpload)->extension }}"
        alt="{{ $photo->alt }}"
        style="object-position: {{ $photo->offsetX }}% {{ $photo->offsetY}}%;"
    >
</picture>

@if ($photo->stat_number || isset($stat_number))
    @include ('content-elements.stat', ['number' => isset($stat_number) ? $stat_number : $photo->stat_number, 'name' => isset($stat_name) ? $stat_name : $photo->stat_name, 'photo' => $photo, 'link' => $photo->link])
@endif

@if ($photo->description)
    <div class="absolute bottom-0 z-3 text-white px-2 py-1 text-shadow">{{ $photo->description }}</div>
@endif

@if ($photo->link)
    </a>
@endif
