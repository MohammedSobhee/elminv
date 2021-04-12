{{-- Utility Menu --}}
<ul class="navbar-nav nav-header nav-header-utility{{session()->has('ownUser') ? ' nav-ownuser' : '' }}">
    {{-- Back to Admin / Teacher Link --}}
    @if(auth()->user()->school->status)
        @if(session()->has('ownUser'))
            @if(session()->get('ownUser')->role == 'teacher' || session()->get('ownUser')->role == 'assistant-teacher')
            <li class="nav-item logout"><a href="/dashboard/loginas" class="nav-link"><i class="mr-2 fas fa-angle-left"></i> Back to Teacher</a></li>
            @else
            <li class="nav-item logout"><a href="/dashboard/loginas" class="nav-link"><i class="mr-2 fas fa-angle-left"></i> Back to Admin</a></li>
            @endif
        @endif


        {{-- Search --}}
        @norole('admin', 'developer', 'manager')
        @if($usersess->class_type != 0 && $usersess->class_type != 99 && auth()->user()->school->status)
        <li class="nav-item menu-search toggle-search"><a href="#" class="nav-link">Search</a>
        <div class="header-search" role="search">
            <form method="get" class="form searchform" action="/course/">
                <label for="s" class="screen-reader-text">Search</label>
                <input type="search" name="s" placeholder="">
                <button type="submit" class="button-search"><span class="screen-reader-text">Search</span></button>
            </form>
        </div>
        </li>
        @endif
        @endrole

        {{-- Profile --}}
        @if(isset($announcement) && $announcement)
        <li class="nav-item nav-item-announcement">
            <a href="/course/updates" data-toggle="tooltip" title="New Update"><i class="fas fa-bell"></i></a>
        </li>
        @endif
        <li
            class="nav-item position-relative{{ (strstr($uri, 'support') || strstr($uri, 'account')) ? ' current-menu-item' : '' }}{{ $avatar ? ' nav-item-avatar' : '' }}">
            <a class="nav-link dropdown-toggle dropdown-toggle-header dropdown-toggle-profile" href="#" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{-- NOTE: Use session which wouldn't show avatar change until logout/in but prevents multiple Image:makes? --}}
                @if($avatar)
                <img src="{{ $avatar }}" class="nav-avatar" alt="{{ auth()->user()->nickname ?: auth()->user()->first_name }}">
                @else
                <i class="fas fa-user nav-header-icon nav-header-icon-avatar"></i>
                @endif
                {{ auth()->user()->nickname ?: auth()->user()->first_name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li class="dmi{{ strstr($uri, 'account') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/edit/account">Edit Account</a></li>
                @norole('student', 'admin', 'developer', 'manager')
                <li class="dmi{{ strstr($uri, 'support') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/support">Support</a></li>
                @endrole
                @if(!session()->get('ownUser'))
                <li><a class="dropdown-item" href="/logout">Logout</a></li>
                @elseif(session()->has('ownUser'))
                    <li><a href="/dashboard/loginas" class="dropdown-item">Logout of {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</a></li>
                @endif
            </ul>
        </li>
    @endif
</ul>
