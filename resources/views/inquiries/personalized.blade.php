<div class="md:flex">
    <div class="flex-1">
        <div class="flex items-center justify-center w-full h-full my-4 md:my0">
            <img src="https://webdev.brentwood.ca/images/icon.svg" class="w-48 hidden md:block" />
            <img src="https://webdev.brentwood.ca/images/logo.svg" class="md:hidden" />
        </div> 
    </div>
    <div class="flex-2">

        <div class="text-block">
            <div class="body">

                <h1>Welcome {{ $inquiry->name }}</h1>

                <div class="mt-2 italic md:flex text-gray-500">

                    @if ($inquiry->email)
                        <div class="flex items-center">
                            <div class="icon"><i class="fas fa-envelope"></i></div>
                            <div class="pl-2">{{ $inquiry->email }}</div>
                        </div>
                    @endif

                    @if ($inquiry->phone)
                        <div class="flex md:ml-4 items-center">
                            <div class="icon"><i class="fas fa-phone"></i></div>
                            <div class="pl-1">{{ $inquiry->phone }}</div>
                        </div>
                    @endif

                </div>

                <div class="mt-4">
                    <p>Thank you for taking the time to contact us regarding a <span class="font-bold">Grade {{ $inquiry->target_grade }} {{ $inquiry->student_type }} student</span> starting in <span class="font-bold">{{ $inquiry->target_year.'-'.($inquiry->target_year + 1) }}</span>.</p>

                    <p>This page contains important information about our school{{ $inquiry->filtered_tags->count() ? ' including the interests you selected' : '' }}.</p>

                    @if ($inquiry->filtered_tags->count())
                        <div class="md:grid grid-cols-{{ $inquiry->filtered_tags->count() > 2 ? '3' : $inquiry->filtered_tags->count() }} md:my-2 md:bg-white md:shadow rounded px-4 md:py-2">
                            @foreach ($inquiry->filtered_tags as $tag)
                                <div class="flex items-center md:justify-center leading-none my-2 md:my-0">
                                    <div class="text-sm text-gray-500"><i class="fas fa-check"></i></div>
                                    <div class="pl-2">{{ $tag->name }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($inquiry->livestreams->count())
                        <h2 class="mt-8">{{ $inquiry->livestreams->first()->name }}</h2>
                        <p>You have registered for the <span class="font-bold">{{ $inquiry->livestreams->first()->name }}</span> on <span class="font-bold">{{ $inquiry->livestreams->first()->date }}</span>. You will receive an email closer to the event with a link to view the presentation.</p>
                    @endif

                    <p>If you have any questions please contact us at <a href="mailto:admissions@brentwood.ca">admissions@brentwood.ca</a>.</p>

                    <p>Thank you for your interest in Brentwood College School and we look forward to speaking with you soon.</p>

                </div>

            </div>
        </div>

    </div>
</div>
