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

        @if (session()->get('editing') && !request('preview'))
            <div class="relative" v-if="$store.state.editing">
                <div class="sticky top-0">
                    <saving-indicator></saving-indicator>
                    <page-tree :expanded="true" :show-changes="true" max-height="100vh"></page-tree>
                </div>
            </div>
        @endif

        <div id="main" class="relative flex-1 flex flex-col">

            <div id="header" class="sticky top-0 z-4 {{ session()->get('editing') && !request('preview') ? 'px-12' : '' }}">

                <page-editor 
                    :editing-enabled="{{ session()->has('editing') ? 'true' : 'false' }}"
                    :current-page='@json($page ?? '')'
                ></page-editor>

                <div class="flex justify-center relative bg-gray-100">
                    
                    <div class="flex flex-col w-full relative max-w-6xl shadow">

                        <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p"></div>

                        <div class="relative flex">

                            <div class="relative md:flex-1 flex items-center justify-center">
                                <div class="p-2 ml-1 md:ml-0 flex justify-center relative">
                                    <a href="/"><img src="images/logo.svg" class="h-8 md:h-12 block" /></a>
                                </div>
                            </div>

                            <nav class="flex-2 relative flex md:block">

                                <div class="md:hidden absolute right-0 text-white bg-primary px-2 cursor-pointer mt-3 mr-2" @click="$store.dispatch('toggleMenu')"><i class="fas fa-bars"></i></div>

                                <div class="absolute top-0 mt-12 md:mt-0 right-0 md:right-auto md:relative md:h-auto md:flex shadow z-3 md:pr-4 overflow-hidden"
                                    :class="$store.state.showMenu ? 'max-h-200 md:max-h-screen' : 'max-h-0 md:max-h-screen'"
                                    style="transition: max-height var(--transition-time) ease"
                                >

                                    <div class="flex-1 md:flex relative justify-around">
                                        @foreach ($menu as $menu_page)
                                            @if (!$menu_page->unlisted && $menu_page->published_version_id)
                                                <a href="{{ $menu_page->full_slug }}" 
                                                    class="font-oswald flex items-center text-base md:text-lg px-4 py-1 md:py-0 relative hover:bg-white
                                                    {{ Illuminate\Support\Str::contains(request()->path(), $menu_page->slug) ? 'underline bg-white' : 'bg-gray-100' }}"
                                                >{{ $menu_page->name }}</a>
                                                @php 
                                                    if ( Illuminate\Support\Str::contains(request()->path(), $menu_page->slug) ) {
                                                        $sub_menu = $menu_page->pages;
                                                    }
                                                @endphp
                                            @endif
                                        @endforeach 
                                    </div>
                                    
                                    <div class="flex">

                                        <a href="/apply-now" class="button md:ml-4 md:my-4 whitespace-no-wrap text-sm md:text-base">Apply Now</a>

                                        @auth
                                            @if (auth()->user()->hasRole('editor'))
                                                <editing-button class="ml-4"></editing-button>
                                            @endif
                                        @endauth

                                    </div>
                                </div>

                                @if (isset($sub_menu))
                                    <div class="hidden md:flex items-center px-8 bg-gray-100 relative text-sm md:text-base">
                                        @foreach ($sub_menu as $menu_page)
                                            @if (!$menu_page->unlisted && $menu_page->published_version_id)
                                                <a href="{{ $menu_page->full_slug }}" class="py-1 font-oswald ml-8 first:ml-0">{{ $menu_page->name }}</a>
                                            @endif
                                        @endforeach 
                                    </div>
                                @endif

                            </nav>

                        </div>

                    </div>

                </div>
            </div>


            <div class="items-center flex-1 flex flex-col relative" 
                :class="$store.state.editing ? 'px-12' : ''"
                style="background-image: linear-gradient(180deg, rgba(247,250,252,1) 75%, rgba(247,218,199,1));"> 

                <div class="flex flex-1 flex-col w-full max-w-6xl relative">

                    <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p z-2"></div>

                    <div id="content" class="flex-1 flex flex-col relative {{ isset($sub_menu) ? 'mt-0' : '' }}">
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
                <div class="flex relative py-8 w-full max-w-6xl">

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
