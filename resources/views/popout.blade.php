<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="locale" content="{{ Illuminate\Support\Facades\App::currentLocale() }}">
    <base href="{{ url('/') }}">

    <title>{{ (isset($page) ? ($page->title ?? $page->name).' - ' : '').env('APP_NAME') }}</title>

    <script src="{{ mix('/js/app.js') }}" defer></script>
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    <script src="https://kit.fontawesome.com/da9050191e.js" crossorigin="anonymous"></script>
</head>

<body class="antialiased relative h-full flex flex-col" style="min-height: 100vh">

    <div id="app" class="relative flex-1 flex">

        <div id="main" class="relative flex-1 flex flex-col">

            <div class="absolute">
                <user-menu :user='@json(auth()->user())'></user-menu>
            </div>

            <div class="items-center flex-1 flex flex-col relative" 
                style="background-image: linear-gradient(180deg, rgba(243, 244, 246 ,1) 75%, rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},1));"> 

                <div class="flex flex-1 flex-col w-full max-w-6xl relative">

                    <div id="content" class="flex-1 flex flex-col relative">
                        @yield ('content')
                    </div>

                </div>

            </div>

        </div>

    </div>

</body>
</html>
