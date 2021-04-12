{{-- Admin Menu (unused) --}}
@role('admin', 'manager', 'developer')
@include('includes.sidebar-admin')
@endrole

{{-- School Admin Menu --}}
{{-- @role('school-admin')
@include('includes.sidebar-school-admin')
@endrole --}}

{{-- Teacher / Asst Teacher Menu --}}
@role('teacher', 'assistant-teacher')
    @include('includes.sidebar-teacher')
@endrole

{{-- Course Pages Menu --}}
@role('teacher', 'assistant-teacher', 'student', 'school-admin')
    @if($usersess->class_type != 0 && $usersess->class_type != 99)
        <button class="sidebar-hidden-menu-btn sidebar-coursepages-menu-btn" id="sidebar-button2">Course Pages Menu</button>
        <div class="sidebar-hidden-menu sidebar-coursepages">

        {{-- Page links from last visted in course  --}}
        @if(isset($page_list))
            @if(is_object($parent_page))
                <h3 id="sidebar-header-courseware" class="sidebar-header"><a href="{{ $parent_page->link }}">{!! $parent_page->title !!}</a></h3>
            @else
                <h3 id="sidebar-header-courseware" class="sidebar-header"><span>{!! $parent_page !!}</span></h3>
            @endif

            <ul id="sidebar-courseware" class="sidebar-menu">
            @if(is_object($page_list) || is_array($page_list))
                @foreach($page_list as $page)
                <li class="page_item{{ $page->id == $page_last_visited ? ' current_page_item' : '' }}"><a href="{{ $page->link }}">{{ $page->title }}</a></li>
                @endforeach
            @else
                {!! $page_list !!}
            @endif
            </ul>
        @else
            {{-- Default Get Started if nothing last visted --}}
            <h3 id="sidebar-header-courseware" class="sidebar-header"><a href="{{ $usersess->class_type > 2 ? '/course/hs' : '/course/em' }}">Get Started</a></h3>
            @php $menu_type = $usersess->class_type > 2 ? 'highschool' : 'elementary' @endphp
            <ul id="sidebar-courseware" class="sidebar-menu">
                @foreach(config('constants.courseware_menu')[$menu_type] as $item)
                    @if($item['name'] !== 'Get Started')
                    <li><a href="{{ $item['url'] }}" class="page_item">{{ $item['name'] }}</a></li>
                    @endif
                @endforeach
            </ul>
        @endif
        </div>
    @endif
@endrole

{{-- Sidebar Assignments Insert --}}
@role('student', 'teacher', 'assistant-teacher')
    @if(Request::is('dashboard') && $insert && (count($insert->assignments) || $insert->has_assignments))
        @role('teacher', 'assistant-teacher')
        <h3 id="sidebar-header-assignments" class="sidebar-header sidebar-header-assignments" style="margin-top: 20px" data-toggle="tooltip" title="These are the assignments selected to be inserted into this page. They are removed from a student's sidebar once completed.">
        @endrole
        @role('student')
        <h3 id="sidebar-header-assignments" class="sidebar-header sidebar-header-assignments" style="margin-top: 20px">
        @endrole
            <a href="/assignments"><i class="fas fa-edit mr-2"></i>Highlighted Assignments</a>
        </h3>
        <ul id="sidebar-assignments" class="sidebar-menu sidebar-menu-assignments">
        @role('teacher', 'assistant-teacher')
        <li>
            <div class="sidebar-select">
                {{ count($insert->assignments) ? 'Viewing' : 'View' }}
                inserted assignments for:
                @if(count($insert->classes) > 1)
                <form method="post" action="/select/assignmentinsert" class="select-submit">
                    @csrf
                    <select name="class_id" class="custom-select custom-select-sm custom-select-sidebar custom-select-light-arrow">
                        @if(!$insert->class_id || !count($insert->assignments))<option>Select a class</option>@endif
                        @foreach ($insert->classes as $cls)
                        <option {{ ($cls->id == $insert->class_id && count($insert->assignments)) ? 'selected ' : '' }} value="{{ $cls->id }}">{{$cls->class_name }}</option>
                        @endforeach
                    </select>
                    <noscript><input type="submit" class="btn btn-success" value="Select"></noscript>
                </form>
                @elseif(isset($insert->classes[0]->class_name))
                <span class="text-dark-secondary">{{ $insert->classes[0]->class_name }}</span>
                @endif
            </div>
        </li>
        @endrole
        @foreach($insert->assignments as $asgmt)
            <li>
                @if(auth()->user()->role->slug == 'student')
                <a href="/assignments/view/{{$asgmt->assignment_id}}/{{$asgmt->category_id}}">{{$asgmt->assignment_name}}
                @else
                <a href="/edit/assignments/{{$asgmt->assignment_id}}/view">{{$asgmt->assignment_name}}
                @endif
                @if($asgmt->due_date)
                    <br><span class="small sidebar-duedate">Due {{ \Carbon\Carbon::parse($asgmt->due_date)->diffForHumans() }}</span>
                @endif
                </a>
            </li>
        @endforeach
        </ul>
    @endif
@endrole
