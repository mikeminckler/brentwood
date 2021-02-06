<div class="absolute items-center flex flex-col justify-center w-full z-3 p-2 md:p-8 pointer-events-none {{ $photo ? 'text-white text-shadow' : 'text-gray-500' }} {{ $photo ? ( $photo->span > 1 ? 'items-center md:items-center h-full' : 'items-center' ) : 'h-full' }}"

@if (!$link && $photo)
    @click="$eventer.$emit('view-photo', '{{ $photo->large }}')"
@endif
>
    <div class="flex flex-col items-center justify-center">
        <div class="font-oswald font-normal leading-none whitespace-nowrap text-6xl">{!! preg_match('/[0-9]/', $number) === 1 ? ( preg_replace('/\d+/', '<span class="md:text-8xl">$0</span>', preg_replace('/\D+/', '<span class="">$0</span>', $number))) : $number !!}</div>
        <div class="font-oswald text-xl md:text-3xl text-center">{{ $name }}</div>
        <div class="h-1 w-16 my-2 {{ $photo ? 'bg-white drop-shadow' : 'bg-gray-400' }}"></div>
    </div>
</div>
