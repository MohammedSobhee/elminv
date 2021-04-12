<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    {{-- Logo / Home Link --}}
    @if(Auth::check())
        @if(auth()->user()->role->slug == 'admin')
        <a class="navbar-brand logo" href="/eduadmin" title="Inventionland Institute"><span class="sr">ILI</span></a>
        @else
        <a class="navbar-brand logo" href="/dashboard" title="Inventionland Institute"><span class="sr">ILI</span></a>
        @endif
    @else
        <a class="navbar-brand logo login" href="/" title="Inventionland Institute"><span class="sr">ILI</span></a>
    @endif



    @auth
    {{-- Mobile menu trigger --}}
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>


    {{-- Nav Menu --}}
    <div class="collapse navbar-collapse" id="navbarMenu">

        {{-- Admin Menu --}}
        @include('includes.nav-admin')

        @if(isset($usersess))
            {{-- User (teacher, student, and school admin) Menu --}}
            @include('includes.nav-user')

            {{-- Utility Menu --}}
            @include('includes.nav-utility')
        @endif

    </div>
    @endauth
</nav>
