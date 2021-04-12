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
            <div class="container content content-laravel content-default-layout" id="app">
                @yield('notification')
                @yield('content')
            </div>
        </main>


        @include('includes.footer')
        @include('includes.scripts')
    </body>
</html>
