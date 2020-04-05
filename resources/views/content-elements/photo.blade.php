<picture class="photo {{ $photo->fill ? 'fill' : 'fit' }} cursor-zoom-in" @click="$eventer.$emit('view-photo', '{{ $photo->large }}')">
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

@if ($photo->description)
    <div class="absolute bottom-0 z-3 text-white px-2 py-1" style="text-shadow: 1px 1px 0px #000000">{{ $photo->description }}</div>
@endif
