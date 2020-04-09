<div class="absolute flex flex-col items-center justify-center w-full h-full z-3 {{ $photo ? 'text-white text-shadow' : 'text-gray-500' }}">
    <div class="flex flex-col items-center justify-center">
        <div class="stat-number {{ strlen($number) > 7 ? 'stat-number-long' : '' }}">{!! preg_match('/[0-9]/', $number) === 1 ? ( preg_replace('/\d+/', '<span class="stat-numbers">$0</span>', preg_replace('/\D+/', '<span class="stat-symbol">$0</span>', $number))) : $number !!}</div>
        <div class="stat-name">{{ $name }}</div>
        <div class="h-1 w-16 my-2 {{ $photo ? 'bg-white drop-shadow' : 'bg-gray-400' }}"></div>
    </div>
</div>
