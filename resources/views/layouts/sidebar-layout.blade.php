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
        <div class="container" id="app">
            <div class="row">

                <div id="content" class="content content-laravel col-lg-9">
                    {{-- Use mt-2 for course tools heading --}}
                    @yield('notification')
                    @yield('content')
                </div>

                <div class="sidebar-wrapper col-lg-3">
                <div class="sidebar sidebar-right" id="sidebar">
                    {{-- <div class="sidebar-drop-menu-btn sidebar-heading">Course Tools Menu</div> --}}
                    @if(auth()->user()->school->status)
                        @include('includes.sidebar')
                    @endif

                    @yield('aside-right')
                </div>
                </div>
            </div>
        </div>

    </main>


    @include('includes.footer')
    @include('includes.scripts')
</body>
</html>
