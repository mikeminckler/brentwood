<div class="md:flex relative">

    <div class="flex-1"></div>

    <div class="flex-2">

        <div class="md:bg-white my-8 md:mx-8 md:p-8 md:rounded-lg md:shadow">

            <div class="flex justify-center relative">
                <div class="text-block relative z-4">
                    <h{{ $first ? '1' : '2'}}>{{ $content->header }}</h{{ $first ? '1': '2' }}>
                    <div class="body">
                        {!! $content->body !!}
                    </div>
                    @if ($content->body)
                        <div class="h-1 w-16 bg-gray-400 my-4"></div>
                    @endif
                </div>
            </div>

            <inquiry
                {{ $content->show_student_info ? ':show-student-info="true"' : '' }}
                {{ $content->show_interests ? ':show-interests="true"' : '' }}
                {{ $content->show_livestreams ? ':show-livestreams="true"' : '' }}
                {{ $content->show_livestreams_first ? ':show-livestreams-first="true"' : '' }}
                @if ($content->livestreams->count())
                    :livestreams='@json($content->livestreams)'
                @endif
            ></inquiry>

        </div>


    </div>

</div>

