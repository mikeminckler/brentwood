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

            <div id="header" class="sticky top-0 z-10 {{ session()->get('editing') && !request('preview') ? 'px-12' : '' }}">

                @if (session()->get('editing'))
                    <page-editor 
                        :editing-enabled="{{ session()->has('editing') ? 'true' : 'false' }}"
                        :current-page='@json($page ?? '')'
                    ></page-editor>
                @endif

                <div class="flex justify-center relative bg-gray-100">
                    
                    <div class="flex flex-col w-full relative max-w-6xl shadow">

                        <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p"></div>

                        <div class="relative flex">

                            <div class="relative md:flex-1 flex items-center justify-center">
                                <div class="p-2 ml-4 md:ml-0 flex justify-center relative">
                                    <a href="/"><img src="images/logo.svg" class="h-8 md:h-12 block" /></a>
                                </div>
                            </div>

                            <nav class="flex-2 relative flex md:block">

                                <div class="flex w-full items-center justify-end mr-4">
                                    <a href="/apply-now" class="button md:hidden mr-4 whitespace-no-wrap text-sm">Apply Now</a>
                                    <div class="md:hidden text-white bg-primary px-2 text-lg cursor-pointer" @click="$store.dispatch('toggleMenu')"><i class="fas fa-bars"></i></div>
                                </div>

                                <div class="absolute md:relative w-screen md:w-auto top-0 mt-12 md:mt-0 right-0 md:right-auto md:h-auto md:flex shadow z-5 md:pr-4 overflow-hidden"
                                    :class="$store.state.showMenu ? 'max-h-screen' : 'max-h-0 md:max-h-screen'"
                                    style="transition: max-height var(--transition-time) ease"
                                >

                                    <div class="w-full flex bg-gray-100">

                                        <div class="flex-1 md:flex relative justify-around w-full px-4">
                                            @foreach ($menu as $menu_page)
                                                @if (!$menu_page->unlisted && $menu_page->published_version_id)

                                                    <div class="font-oswald font-light text-base md:text-lg relative text-primary hover:bg-white md:flex md:items-center
                                                        {{ Illuminate\Support\Str::contains(request()->path(), $menu_page->slug) ? 'bg-white' : 'bg-gray-100' }}"
                                                        ref="menu{{ $menu_page->id }}"
                                                    >
                                                        <div class="flex items-center">
                                                            <a href="{{ $menu_page->full_slug }}" class="px-2 md:px-4 py-1 md:py-4 flex-1 
                                                                {{ Illuminate\Support\Str::contains(request()->path(), $menu_page->slug) ? 'underline' : '' }}">{{ $menu_page->name }}</a>
                                                            @if ($menu_page->pages->count())
                                                                <div class="block md:hidden text-lg cursor-pointer px-2" @click="$refs.menu{{ $menu_page->id }}.classList.toggle('show-sub-menu')">
                                                                    <div class="icon"><i class="fas fa-caret-down"></i></div>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if ($menu_page->pages->count())
                                                            @foreach ($menu_page->pages as $menu_sub_page)
                                                                <div class="sub-menu overflow-hidden font-oswald text-base bg-gray-300 hover:bg-gray-200 md:hidden">
                                                                    <a href="{{ $menu_sub_page->full_slug }}" class="px-4 py-1 block">{{ $menu_sub_page->name }}</a>
                                                                </div>
                                                            @endforeach
                                                        @endif

                                                    </div>

                                                @endif
                                            @endforeach 
                                        </div>

                                        <div class="bg-gray-200 md:bg-transparent flex items-center justify-center md:items-end flex-col px-2">


                                            <div class="flex items-center mb-2 md:mb-0">
                                        
                                                <a href="/apply-now" class="button hidden md:block mr-4 my-4 whitespace-no-wrap text-base">Apply Now</a>
                                                <a href="/search" class="hidden md:block text-xl text-gray-500 cursor-pointer"><i class="fas fa-search"></i></a>

                                                @auth
                                                    @if (auth()->user()->hasRole('editor'))
                                                        <editing-button class="ml-4"></editing-button>
                                                    @endif
                                                @endauth
                                            </div>

                                            <div class="text-sm leading-loose md:hidden text-center">
                                                <a href="tel:2507435521">250.743.5521</a><br/>
                                                <a href="mailto:info@brentwood.ca">info@brentwood.ca</a>
                                            </div>

                                        </div>

                                    </div>
                                    
                                </div>

                            </nav>

                        </div>

                    </div>

                </div>
            </div>


            <div class="items-center flex-1 flex flex-col relative" 
                :class="$store.state.editing ? 'px-12' : ''"
                style="background-image: linear-gradient(180deg, rgba(247,250,252,1) 75%, rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},1));"> 

                <div class="flex flex-1 flex-col w-full max-w-6xl relative">

                    <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p z-2"></div>

                    <div id="content" class="flex-1 flex flex-col relative">
                        @yield ('content')
                    </div>

                </div>

            </div>

            <div id="footer" class="relative flex justify-center" style="min-height: 600px" :class="$store.state.editing ? 'px-12' : ''">

                @if (session()->get('editing') && !request('preview'))
                    <footer-editor></footer-editor>
                @endif
                <div class="absolute z-1 w-full h-full" style="background-image: linear-gradient(180deg, rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},1), rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},0));" ></div>
                <div class="absolute w-full h-full overflow-hidden">
                    <img src="{{ isset($page) ? ( $page->footer_fg_image ? $page->footer_fg_image : '/images/footer_fg.png' ) : '/images/footer_fg.png' }}" class="w-full h-full object-cover z-2 absolute" />
                    <img src="{{ isset($page) ? ( $page->footer_bg_image ? $page->footer_bg_image : '/images/footer_bg.png' ) : '/images/footer_bg.png' }}" class="w-full h-full object-cover" />
                </div>
                <div class="relative w-full max-w-6xl">

                    <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p z-1"></div>
                    
                    <div class="flex items-center pt-8 md:pt-16">
                        <div class="hidden md:flex flex-1 justify-center relative z-2">
                            <div class="p-8">
                                <img src="images/logo.svg" class="h-12" />
                            </div>
                        </div>
                
                        <div class="flex-2 relative z-2">
                            <div class="flex flex-col md:flex-row items-center justify-center">
                                <div class="">
                                    <a href="tel:2507435521">250.743.5521</a><br/>
                                    <a href="mailto:info@brentwood.ca">info@brentwood.ca</a>
                                    <div class="mt-4">2735 Mount Baker Road<br/>Mill Bay, BC, Canada VOR 2P1</div>
                                    <div class="text-xl flex mt-4">
                                        <a href="https://www.youtube.com/user/brentwoodcollege" target="__blank" class="pr-4"><i class="fab fa-youtube"></i></a>
                                        <a href="https://www.facebook.com/brentwoodcollegeschool" target="__blank" class="pr-4"><i class="fab fa-facebook"></i></a>
                                        <a href="https://www.instagram.com/brentwoodboarding" target="__blank" class="pr-4"><i class="fab fa-instagram"></i></a>
                                        <a href="https://twitter.com/BrentwoodNews" target="__blank" class="pr-4"><i class="fab fa-twitter"></i></a>
                                        <a href="https://www.linkedin.com/school/brentwood-college-school" target="__blank" class="pr-4"><i class="fab fa-linkedin"></i></a>
                                    </div>
                                </div>
                                <div class="flex py-8 md:px-8">
                                    <a href="/contact-us" class="button md:ml-4 md:my-4 whitespace-no-wrap text-sm md:text-base">Contact Us</a>
                                </div>
                            </div>
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

        <photo-viewer></photo-viewer>

    </div>

</body>
</html>
