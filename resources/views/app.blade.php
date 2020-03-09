<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <base href="{{ url('/') }}">

    <script src="{{ mix('/js/app.js') }}" defer></script>
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

</head>
<body class="antialiased relative h-full flex flex-col" style="min-height: 100vh">

    <div id="app" class="relative flex-1 flex flex-col">

        <div class="border-r-4 border-primary fixed h-screen md:w-1/3"></div>
        <div id="header" class="flex items-center relative z-5">

            <div class="flex-1">
                <div class="p-8 flex items-center justify-center">
                    <a href="/"><img src="images/logo.svg" class="h-12" /></a>
                </div>
            </div>

            <div class="flex-2">
                <div class="p-8 flex items-center justify-end">
                    @auth
                        <div class="px-4">{{ auth()->user()->name }}</div>

                        <form method="POST" action="/logout">
                            @csrf
                            <button class="link">Logout</button>
                        </form>

                        @if (auth()->user()->hasRole('editor'))
                            <div class="button ml-4" @click="toggleEditing">Edit</div>
                        @endif

                    @endauth

                    @guest
                        <a href="/login" class="button">Login</a>
                    @endguest
                </div>
            </div>
        </div>

        <div id="content" class="p-4 flex-1 flex flex-col relative z-5">
            @yield ('content')
        </div>

        <div id="footer" class="flex items-center relative z-5">

            <div class="flex-1 flex justify-center">
                <div class="p-8">
                    <img src="images/logo.svg" class="h-12" />
                </div>
            </div>
    
            <div class="flex-2">
                <div class="py-8 px-25p">
                    <a href="tel:2507435521" class="block">250.743.5521</a>
                    <a href="mailto:info@brentwood.ca" class="block">info@brentwood.ca</a>

                    <p class="mt-4">2735 Mount Baker Road<br/>Mill Bay, BC<br/>Canada<br/>VOR 2P1</p>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
