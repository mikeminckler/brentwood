<div class="absolute bottom-0 flex flex-col justify-center w-full z-3 p-2 md:p-8 pointer-events-none {{ $photo ? 'text-white text-shadow' : 'text-gray-500' }} {{ $photo ? ( $photo->span > 1 ? 'items-end h-full' : 'items-center' ) : '' }}"

@if (!$link && $photo)
    @click="$eventer.$emit('view-photo', '{{ $photo->large }}')"
@endif
>
    <div class="flex flex-col items-center justify-center">
        <div class="stat-number {{ strlen($number) > 7 ? 'stat-number-long' : '' }}">{!! preg_match('/[0-9]/', $number) === 1 ? ( preg_replace('/\d+/', '<span class="stat-numbers">$0</span>', preg_replace('/\D+/', '<span class="stat-symbol">$0</span>', $number))) : $number !!}</div>
        <div class="stat-name">{{ $name }}</div>
        <div class="h-1 w-16 my-2 {{ $photo ? 'bg-white drop-shadow' : 'bg-gray-400' }}"></div>
    </div>
</div>
