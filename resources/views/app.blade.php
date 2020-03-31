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

        <div class="relative" v-if="$store.state.editing">
            <div class="sticky top-0">
                <saving-indicator></saving-indicator>
                <page-tree></page-tree>
            </div>
        </div>

        <div class="relative flex-1 flex flex-col">

            <div class="sticky top-0 z-4" :class="$store.state.editing ? 'px-12' : ''">

                <page-editor 
                    :editing-enabled="{{ session()->has('editing') ? 'true' : 'false' }}"
                    :current-page='@json($page ?? '')'
                ></page-editor>

                <div id="header" class="flex justify-center relative bg-gray-100 shadow">
                    
                    <div class="flex flex-col w-full relative max-w-6xl">

                        <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p z-3"></div>

                        <div class="relative flex items-center">

                            <div class="md:flex-1 flex items-center justify-center">
                                <div class="px-2 py-1 md:px-8 md:py-4 w-12 ml-1 md:ml-0 md:w-auto flex justify-center">
                                    <a href="/"><img src="images/logo.svg" class="h-12 hidden md:block" /></a>
                                    <a href="/"><img src="images/icon.svg" class="h-12 block md:hidden" /></a>
                                </div>
                            </div>

                            <div class="flex-2 h-full">

                                <div class="flex h-full">

                                    <div class="flex-1 flex items-center -mb-1 h-full z-1">
                                        @foreach ($menu as $menu_page)
                                            @if (!$menu_page->unlisted && $menu_page->published_version_id)
                                                <a href="{{ $menu_page->full_slug }}" 
                                                    class="font-oswald flex items-center md:text-lg px-4 h-full
                                                    {{ Illuminate\Support\Str::contains(request()->path(), $menu_page->slug) ? 'underline shadow bg-white' : '' }}"
                                                >{{ $menu_page->name }}</a>
                                                @php 
                                                    if ( Illuminate\Support\Str::contains(request()->path(), $menu_page->slug) ) {
                                                        $sub_menu = $menu_page->pages;
                                                    }
                                                @endphp
                                            @endif
                                        @endforeach 
                                    </div>
                                    
                                    <div class="flex items-center justify-end">

                                        <a href="/apply-now" class="button ml-4 whitespace-no-wrap">Apply Now</a>

                                        @auth
                                            @if (auth()->user()->hasRole('editor'))
                                                <editing-button class="ml-4"></editing-button>
                                            @endif
                                        @endauth

                                    </div>
                                </div>

                                @if (isset($sub_menu))
                                    <div class="shadow overflow-visible h-0">

                                        <div class="flex items-center px-8 bg-white relative z-2 text-sm md:text-base">
                                            @foreach ($sub_menu as $menu_page)
                                                @if (!$menu_page->unlisted && $menu_page->published_version_id)
                                                    <a href="{{ $menu_page->full_slug }}" class="py-1 font-oswald ml-8 first:ml-0">{{ $menu_page->name }}</a>
                                                @endif
                                            @endforeach 
                                        </div>

                                    </div>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>
            </div>


            <div class="items-center flex-1 flex flex-col relative" 
                :class="$store.state.editing ? 'px-12' : ''"
                style="background-image: linear-gradient(180deg, rgba(247,250,252,1) 75%, rgba(247,218,199,1));"> 

                <div class="flex flex-1 flex-col w-full max-w-6xl relative">

                    <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p z-2"></div>

                    <div id="content" class="flex-1 flex flex-col relative {{ isset($sub_menu) ? 'mt-8' : '' }}">
                        @yield ('content')
                    </div>

                </div>

            </div>

            <div id="footer" class="relative flex justify-center" style="min-height: 500px" :class="$store.state.editing ? 'px-12' : ''">
                <div class="absolute z-1 w-full h-full" style="background-image: linear-gradient(180deg, rgba(247,218,199,1), rgba(245,205,175,0));" ></div>
                <div class="absolute w-full h-full overflow-hidden">
                    <img src="/images/footer_fg.png" class="w-full h-full object-cover z-2 absolute" />
                    <img src="/images/footer_bg.jpg" class="w-full h-full object-cover" />
                </div>
                <div class="flex items-center relative py-8 w-full max-w-6xl">

                    <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p z-1"></div>

                    <div class="hidden md:flex flex-1 justify-center relative z-2">
                        <div class="p-8">
                            <img src="images/logo.svg" class="h-12" />
                        </div>
                    </div>
            
                    <div class="flex-2 relative z-2">
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
