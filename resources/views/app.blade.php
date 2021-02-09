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

    @if (!session()->get('editing') && !request('preview'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_ID') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ env('GOOGLE_ANALYTICS_ID') }}');
        </script>

        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window,document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ env('FACEBOOK_PIXEL_ID') }}'); 
            fbq('track', 'PageView');
        </script>

        <noscript>
             <img height="1" width="1" src="https://www.facebook.com/tr?id={{ env('FACEBOOK_PIXEL_ID') }}&ev=PageView &noscript=1"/>
        </noscript>
        
    @endif

</head>

<body class="antialiased relative h-full flex flex-col" style="min-height: 100vh">

    <div id="app" class="relative flex-1 flex">

        @if (session()->get('editing') && !request('preview'))
            <div class="relative" v-if="$store.state.editing">
                <div class="sticky top-0 flex flex-col h-full">
                    <saving-indicator></saving-indicator>
                    <div class="flex-1">
                        <page-tree :sort="true" :expanded="true" :show-changes="true" max-height="100%"></page-tree>
                    </div>
                    @include ('side-menu')
                </div>
            </div>
        @endif

        <div id="main" class="relative flex-1 flex flex-col">

            <div id="header" class="sticky top-0 z-10 {{ optional($page ?? '')->editable && !request('preview') ? '' : '' }}">

                <div class="flex justify-center relative">
                    
                    <div class="flex flex-col w-full relative max-w-6xl shadow bg-gray-100">

                        <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p"></div>

                        <div class="relative flex">

                            <div class="relative flex-1 flex items-center justify-center">
                                <div class="ml-2 md:ml-0 flex justify-center items-center relative transition-all duration-500" :class="scrollPosition > 64 ? 'h-6 md:h-6' : 'h-8 md:h-14'">
                                    <a href="/"><img src="images/logo.svg" class="transition-all duration-500" :class="scrollPosition > 64 ? 'h-6 md:h-8' : 'h-8 md:h-10'" /></a>
                                </div>
                            </div>

                            <nav class="flex-1 relative flex md:block items-center h-full">

                                <div class="relative md:hidden flex w-full items-center justify-end">
                                    <a href="https://www.brentwood.bc.ca/admissions/application-process/application-process/#/?c=2409" target="_blank" class="button md:hidden mr-2 whitespace-no-wrap text-sm">Apply</a>
                                    <div class="text-white bg-primary px-2 text-lg cursor-pointer mr-2" @click="$store.dispatch('toggleMenu')"><i class="fas fa-bars"></i></div>
                                </div>

                                <div class="absolute md:relative w-screen md:w-auto top-0 md:mt-0 right-0 md:right-auto md:h-full md:flex items-center z-5 md:overflow-visible overflow-hidden"
                                    :class="[$store.state.showMenu ? 'max-h-screen pb-1 md:pb-0' : 'max-h-0 md:max-h-screen', scrollPosition > 64 ? 'mt-8' : 'mt-10']"
                                    style="transition: max-height var(--transition-time) ease"
                                >

                                    <div class="w-full flex bg-gray-100 h-full border-l-4 md:border-l-0 border-primary shadow md:shadow-none relative"
                                        :class="[$store.state.showMenu ? 'shadow md:shadow-none' : '']"
                                    >

                                        <div id="menu" class="flex-1 md:flex relative w-full justify-center md:px-4">
                                            @foreach (App\Utilities\Menu::getMenu()->sortBy->sort_order as $menu_page)
                                                @if (!$menu_page->unlisted && $menu_page->published_version_id)

                                                    <div class="font-oswald font-light text-base md:text-lg relative text-primary hover:underline md:flex md:items-center
                                                        {{ Illuminate\Support\Str::contains(request()->path(), $menu_page->slug) ? 'bg-white' : 'bg-gray-100' }}"
                                                        ref="menu{{ $menu_page->id }}"
                                                    >
                                                        <div class="flex items-center h-full">
                                                            <a href="{{ $menu_page->full_slug }}" class="inline-flex items-center whitespace-nowrap px-2 md:px-4 flex-1 py-1 md:py-0 md:h-full 
                                                                {{ $menu_page->name === 'Admissions' ? 'bg-white' : '' }}
                                                                {{ Illuminate\Support\Str::contains(request()->path(), $menu_page->full_slug) ? 'underline' : '' }}">{{ $menu_page->name }}</a>
                                                            @if ($menu_page->pages->count())
                                                                <div class="block md:hidden text-lg cursor-pointer w-6 px-2 mr-2" @click="$refs.menu{{ $menu_page->id }}.classList.toggle('show-sub-menu')">
                                                                    <div class="icon"><i class="fas fa-caret-down"></i></div>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if ($menu_page->pages->count())
                                                            @foreach ($menu_page->pages as $menu_sub_page)
                                                                <div class="sub-menu overflow-hidden font-oswald text-base bg-gray-100 hover:bg-white md:hidden">
                                                                    <a href="{{ $menu_sub_page->full_slug }}" class="px-4 py-1 block">{{ $menu_sub_page->name }}</a>
                                                                </div>
                                                            @endforeach
                                                        @endif

                                                    </div>

                                                @endif
                                            @endforeach 
                                        </div>

                                        <div class="bg-gray-200 md:bg-transparent flex items-center md:items-end md:justify-center flex-col relative">

                                            <div class="flex md:items-center mb-2 md:mb-0 relative">
                                        
                                                <a href="/admissions/apply" class="button hidden md:block mr-2 my-0 whitespace-no-wrap text-base">Apply Now</a>
                                                <a href="#" class="hidden md:block text-xl text-gray-500 cursor-pointer mr-2"><i class="fas fa-search"></i></a>

                                                @auth
                                                    <user-menu :user='@json(auth()->user())'></user-menu>

                                                    @if (auth()->user()->hasRole('editor'))
                                                        <div class="absolute -mr-8 right-0 hidden md:block">
                                                            <editing-button v-show="{{ !request('preview') }}" class="ml-4" :enabled="{{ session()->get('editing') ? 'true' : 'false'}}"></editing-button>
                                                        </div>
                                                    @endif

                                                @endauth
                                            </div>

                                            <div class="text-sm leading-loose hidden text-center">
                                                <a href="tel:2507435521">250.743.5521</a><br/>
                                                <a href="mailto:info@brentwood.ca">info@brentwood.ca</a>
                                            </div>

                                        </div>

                                    </div>
                                    
                                </div>

                            </nav>

                        </div>

                        @auth
                            @if (auth()->user()->hasRole('editor'))
                                <echos :editing="{{ session()->get('editing') ? 'true' : 'false' }}"></echos>
                            @endif
                        @endauth

                    </div>

                </div>

                @if (optional($page ?? '')->editable)
                    <page-editor :current-page='@json($page ?? '')' resource="{{ $page ?  $page->resource : '' }}"></page-editor>
                @endif

            </div>


            <div class="items-center flex-1 flex flex-col relative" 
                style="background-image: linear-gradient(180deg, rgba(243, 244, 246 ,1) 75%, rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},1));"> 

                <div class="flex flex-1 flex-col w-full max-w-6xl relative">

                    <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p z-2"></div>

                    <div id="content" class="flex-1 flex flex-col relative">
                        @yield ('content')
                    </div>

                </div>

            </div>


            <div id="footer" class="relative flex justify-center">

                @if (optional($page ?? '')->editable && optional($page ?? '')->type === 'page' && !request('preview'))
                    <footer-editor></footer-editor>
                @else

                    <div class="hidden md:block absolute z-1 w-full h-full" style="background-image: linear-gradient(180deg, rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},1), rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},0));" ></div>
                    <div class="md:hidden absolute z-1 w-full h-full" style="background-image: linear-gradient(180deg, rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},1) 70%, rgba({{ isset($page) ? ($page->footer_color ? $page->footer_color : '218,241,250') : '218,241,250' }},0));" ></div>

                    <div class="absolute w-full h-full overflow-hidden flex items-end">

                    @php 

                        if (isset($page)) {
                            $footerFgPhoto = $page->getFooterFgPhoto();
                            $footerBgPhoto = $page->getFooterBgPhoto();
                        } else {
                            $home_page = App\Models\Page::find(1);
                            $footerFgPhoto = $home_page->footerFgPhoto;
                            $footerBgPhoto = $home_page->footerBgPhoto;
                        }

                    @endphp

                    @if ($footerFgPhoto)
                        <picture class="w-full md:h-full z-2 absolute">
                            <source media="(min-width: 900px)" srcset="{{ $footerFgPhoto->large }}.webp" type="image/webp" >
                            <source media="(min-width: 400px)" srcset="{{ $footerFgPhoto->medium }}.webp" type="image/webp" >
                            <source srcset="{{ $footerFgPhoto->small }}.webp" type="image/webp" >
                            <img class="w-full h-full md:object-cover"
                                srcset="{{ $footerFgPhoto->small }} 400w, {{ $footerFgPhoto->medium }} 900w, {{ $footerFgPhoto->large }} 1152w"
                                src="{{ $footerFgPhoto->large }}"
                                type="image/{{ optional($footerFgPhoto->fileUpload)->extension }}"
                                alt="Brentwood College School Footer Foreground" >
                        </picture>
                    @endif

                    @if ($footerBgPhoto)
                        <picture class="w-full md:h-full">
                            <source media="(min-width: 900px)" srcset="{{ $footerBgPhoto->large }}.webp" type="image/webp" >
                            <source media="(min-width: 400px)" srcset="{{ $footerBgPhoto->medium }}.webp" type="image/webp" >
                            <source srcset="{{ $footerBgPhoto->small }}.webp" type="image/webp" >
                            <img class="w-full h-full md:object-cover" 
                                srcset="{{ $footerBgPhoto->small }} 400w, {{ $footerBgPhoto->medium }} 900w, {{ $footerBgPhoto->large }} 1152w"
                                src="{{ $footerBgPhoto->large }}"
                                type="image/{{ optional($footerBgPhoto->fileUpload)->extension }}"
                                alt="Brentwood College School Footer Background" >
                        </picture>
                    @endif

                    </div>

                @endif

                <div class="relative w-full max-w-6xl pb-24 md:pb-64 {{ isset($page) ? $page->footer_text_color : '' }}">

                    <div class="border-r-4 border-primary absolute top-0 h-full md:ml-33p z-4 md:z-1"></div>
                    
                    <div class="md:flex items-center py-8 md:pt-16 relative z-4">
                        <div class="flex flex-col flex-1 justify-center items-center relative">
                            <div class="p-8">
                                <img src="images/logo.svg" class="h-12" />
                            </div>

                            <div class="text-2xl mb-8 font-oswald font-light leading-tight flex justify-center">
                                <div>Where Students <span class="border-b-2 border-primary">Choose</span> To Be</div>
                            </div>

                        </div>
                
                        <div class="flex-2 relative z-2">

                            <div class="flex flex-col md:flex-row items-center justify-center">

                                <div class="">
                                    <clock></clock>
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
                                <div class="flex md:flex-col py-2 md:py-8 px-12 md:px-8 w-full md:w-auto">
                                    <a href="/admissions/apply" class="button ml-4 my-2 whitespace-no-wrap text-sm md:text-base">Apply Now</a>
                                    <a href="https://www.brentwood.bc.ca/inquiry" target="_blank" class="button ml-4 my-2 whitespace-no-wrap text-sm md:text-base">Contact Us</a>
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
