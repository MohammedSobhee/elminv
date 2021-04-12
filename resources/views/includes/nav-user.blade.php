@if(auth()->user()->school->status)
    @php
    switch ($usersess->class_type) {
        case 4: // highschool
            $class_url = '/course/hs';
            break;
        case 3: // middleschool
            $class_url = '/course/hs'; //'/course/ms';
            break;
        case 2: // elementary
            $class_url = '/course/em';
            break;
        case 1: // elementary
            $class_url = '/course/em';
            break;
    }
    @endphp
    {{-- Teacher, student, and school admin menu --}}

    @norole('admin', 'developer', 'manager')
    <ul id="nav-header" class="navbar-nav mr-auto nav-header">

        <li class="nav-item{{ strstr($uri, 'dashboard') ? ' current-menu-item' : '' }}"><a href="/dashboard" class="nav-link"><i class="fas fa-tachometer-alt nav-header-icon mr-2"></i>Dashboard</a></li>
        @if(in_array($usersess->role, ['teacher', 'student', 'assistant-teacher']) && $usersess->class_type != 99)
        <li class="nav-item{{ strstr($uri, 'assignments') && !strstr($uri, 'edit/assignments') ? ' current-menu-item' : '' }}"><a href="/assignments" class="nav-link"><i class="fas fa-edit nav-header-icon mr-2"></i>Assignments</a></li>
        @endif

        @if($usersess->class_type != 99)
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle dropdown-toggle-header" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-book-reader nav-header-icon mr-2"></i>Course Material</a>
            @php
                $ownuser_class = session()->has('ownUser') ? ' nav-ownuser' : '';
                $menu_type = $usersess->class_type > 2 ? 'highschool' : 'elementary';
            @endphp
            <ul class="dropdown-menu{{$ownuser_class}}">
                @foreach(config('constants.courseware_menu')[$menu_type] as $item)
                <li><a href="{{ $item['url'] }}" class="dropdown-item">{{ $item['name'] }}</a></li>
                @endforeach
            </ul>
        </li>
        @endif

        @role('teacher', 'assistant-teacher')
        <li class="nav-item dropdown{{isset($uri) && Str::contains($uri, ['gradebook', 'edit/class', 'edit/team', 'messages', 'edit/assignments','edit/duedates', 'schooladmin', 'edit/settings']) ? ' current-menu-item' : ''}}">
            <a class="nav-link dropdown-toggle dropdown-toggle-header" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-toolbox nav-header-icon mr-2"></i>Teacher Tools</a>
            <ul class="dropdown-menu">
                <li class="dmi{{ strstr($uri, 'gradebook') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/gradebook">Gradebook</a></li>
                <li class="dmi{{ strstr($uri, 'edit/class') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/edit/class">Classes</a></li>
                <li class="dmi{{ strstr($uri, 'edit/team') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/edit/team">Teams</a></li>
                <li class="dmi{{ strstr($uri, 'messages') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/messages">Messaging</a></li>
                <li class="dmi{{ strstr($uri, 'edit/assignments') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/edit/assignments">Custom Assignments</a></li>
                <li class="dmi{{ strstr($uri, 'edit/duedates') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/edit/duedates">Set Due Dates</a></li>
                @if(auth()->user()->canSchoolAdmin())
                <li class="dmi{{ strstr($uri, 'schooladmin') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/schooladmin">School Administration</a></li>
                @endif
                <li class="dmi{{ strstr($uri, 'edit/settings') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/edit/settings">Settings</a></li>
                @if($usersess->role == 'teacher' || $usersess->role == 'assistant-teacher' || $usersess->role == 'school-admin')
                <li class="dmi"><a class="dropdown-item" href="/course/student-resources">Student Resources</a></li>
                <li class="dmi"><a class="dropdown-item" href="/course/teacher-resources">Teacher Resources</a></li>
                @endif
                <li><a class="dropdown-item" href="/course/updates">Updates &amp; Announcements</a></li>
            </ul>
        </li>
        @endrole

        @role('student')
        <li class="nav-item"><a href="/course/student-resources" class="nav-link">Student Resources</a></li>
        @endrole

        @role('school-admin')
        <li class="nav-item dropdown{{isset($uri) && Str::contains($uri, ['schooltree', 'dashboard#add-members']) ? ' current-menu-item' : ''}}">
            <a class="nav-link dropdown-toggle dropdown-toggle-header" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-toolbox nav-header-icon mr-2"></i>School Administrator Tools</a>
            <ul class="dropdown-menu">
                <li class="dmi{{ strstr($uri, 'gradebook') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/dashboard/schooltree">Member Tree</a></li>
                <li class="dmi{{ strstr($uri, 'edit/class') ? ' current_page_item' : '' }}"><a class="dropdown-item" href="/dashboard#add-members">Add Members</a></li>
                <li><a class="dropdown-item" href="/course/updates">Updates &amp; Announcements</a></li>
            </ul>
        </li>
        @endrole

    </ul>
    @endrole
@else
    <ul class="navbar-nav mr-auto nav-header"></ul>
@endif
