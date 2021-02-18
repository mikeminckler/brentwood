@extends ('app')

@section ('content')

<div class="flex flex-col flex-1">
    @include ('content')

    <div class="md:flex mt-4">
        <div class="flex-1"></div>
        <div class="flex-2 flex justify-center">
            <div class="text-block">
                <h2>{{ $livestream->name }} Registration</h2>
                <div class="text-lg font-bold mt-2">{{ $livestream->start_date->timezone('America/Vancouver')->format('l F jS g:ia') }}</div>
                <p>To register for this online open house please complete the form below.</p>
            </div>
        </div>
    </div>

    <div class="md:flex mt-4 w-full">
        <div class="flex-1"></div>
        <div class="flex-2 flex justify-center">
            <div class="w-full md:bg-white md:mx-8 md:p-8 md:shadow">
                <inquiry :livestream='@json($livestream)'
                    :show-student-info="true"
                    :show-interests="true"
                ></inquiry>
            </div>
        </div>
    </div>

</div>

@endsection
