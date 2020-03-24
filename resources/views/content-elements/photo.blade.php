<picture class="photo">
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
        type="image/{{ $photo->fileUpload->extension }}"
        alt="{{ $photo->alt }}"
        style="object-position: {{ $photo->offsetX }}% {{ $photo->offsetY}}%;"
    >
</picture>
