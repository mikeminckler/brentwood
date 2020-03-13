<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <base href="{{ url('/') }}">

    <script src="{{ mix('/js/app.js') }}" defer></script>
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    <script src="https://kit.fontawesome.com/da9050191e.js" crossorigin="anonymous"></script>

</head>
<body class="antialiased relative h-full flex flex-col" style="min-height: 100vh">

    <div id="app" class="relative flex-1 flex">

        @if(session()->has('editing'))
            <content-editor :current-page='@json($page)'></content-editor>
        @endif

        <page-tree :editing="{{ session()->has('editing') ? 'true' : 'false' }}"></page-tree>

        <div class="relative flex-1 flex flex-col">

            <div class="border-r-4 border-primary absolute top-0 h-full md:w-1/3 z-2"></div>

            <div id="header" class="flex items-center sticky z-2 top-0 bg-gray-100">

                <div class="md:flex-1">
                    <div class="p-8 flex items-center justify-center">
                        <a href="/"><img src="images/logo.svg" class="h-12 hidden md:block" /></a>
                        <a href="/"><img src="images/icon.svg" class="h-12 block md:hidden" /></a>
                    </div>
                </div>

                <div class="flex-2 flex">

                    <nav class="flex-1 flex items-center px-8">
                        @foreach ($menu as $menu_page)
                            <a href="{{ $menu_page->full_slug }}" class="font-oswald text-lg ml-8 first:ml-0">{{ $menu_page->name }}</a>
                        @endforeach 
                    </nav>

                    <div class="p-8 flex items-center justify-end">

                        <a href="/apply-now" class="button ml-4 whitespace-no-wrap">Apply Now</a>

                        @auth
                            @if (auth()->user()->hasRole('editor'))
                                <editing-button class="ml-4"></editing-button>
                            @endif
                        @endauth

                    </div>
                </div>
            </div>

            <div id="content" class="flex-1 flex flex-col relative z-1" style="background-image: linear-gradient(180deg, rgba(247,250,252,1) 50%, rgba(247,218,199,1));"> 
                @yield ('content')
            </div>

            <div id="footer" class="relative">
                <div class="absolute z-1 w-full h-full" style="background-image: linear-gradient(180deg, rgba(247,218,199,1), rgba(245,205,175,0));" ></div>
                <div class="absolute w-full h-full overflow-hidden">
                    <img src="/images/footer_bg.jpg" class="w-full h-full object-cover" />
                </div>
                <div class="flex items-center relative z-1 mt-16 mb-64">

                    <div class="hidden md:flex flex-1 justify-center">
                        <div class="p-8">
                            <img src="images/logo.svg" class="h-12" />
                        </div>
                    </div>
            
                    <div class="flex-2">
                        <div class="py-8 px-25p">
                            <a href="tel:2507435521">250.743.5521</a><br/>
                            <a href="mailto:info@brentwood.ca">info@brentwood.ca</a>

                            <p class="mt-4">2735 Mount Baker Road<br/>Mill Bay, BC<br/>Canada<br/>VOR 2P1</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <processing></processing>

        <feedback 
            {{ $errors->count() ? ':errors="'.json_encode($errors).'"' : '' }} 
            {{ isset($error) ? ':error="'.json_encode($error).'"' : '' }} 
            {{ isset($sucess) ? ':success="'.json_encode($sucess).'"' : '' }} 
        ></feedback>

    </div>

</body>
</html>
