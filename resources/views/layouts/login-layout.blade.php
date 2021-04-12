<!doctype html>
<html>
    <head>
        @include('includes.head')
    </head>
    <body>
        <header id="header" class="header">
            @include('includes.header')
        </header>
        @include('includes.masthead')

        <main id="main" class="main">
            <div class="main-overlay"></div>

            @auth
            <div class="container">
            <div class="row row-scroll">
                <div class="col-lg-12">
                    @yield('breadcrumbs')
                </div>
                {{-- <div class="sidebar sidebar-scroll col-lg-3">
                    @if(auth()->user()->school->status)
                    <div class="sidebar-wrapper">
                        <button type="button" class="sidebar-drop-menu-btn desktop-show" id="sidebar-drop-menu-btn">Course Tools Menu</button>
                    </div>
                    <div class="sidebar-drop-menu">
                        @include('includes.sidebar')
                    </div>
                    @endif
                </div> --}}
            </div>
            </div>
            @endauth

            <div class="container content content-laravel content-default-layout" id="app">
                <div class="w-50 m-auto">
                @yield('notification')
                </div>
                @yield('content')
            </div>
        </main>


        @include('includes.footer')
        @include('includes.scripts')
    </body>
</html>
