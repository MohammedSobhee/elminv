<!doctype html>
<html>
    <head>
        @include('includes.head')
        @yield('css-header')
    </head>
    <body>
        <header id="header" class="header">
            @include('includes.header')
        </header>
        @include('includes.masthead-fluid')

        <main id="main" class="main">
            <div class="container-fluid content content-laravel content-default-layout" id="app">
                @yield('notification')
                @yield('content')
            </div>
        </main>


        @include('includes.footer')
        @yield('scripts-footer')
    </body>
</html>
