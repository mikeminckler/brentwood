@extends ('popout')

@section ('content')

<div class="flex flex-col flex-1">
    <chat room="{{ $room }}" :hide-close="true"></chat>
</div>

@endsection
