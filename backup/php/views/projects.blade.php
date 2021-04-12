@extends('layouts.sidebar-right')
@if($team == 0)
    @section('title', 'My Ideas')
@else
    @section('title', 'My Team Ideas')
@endif
@include('includes.notify')
@section('content')

@if($team == 0)
    <div class="flex-row row no-gutters mb-4 p-2 action-top rounded align-items-center">
        <div class="col-3 col-md-2"><button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#projectAddModal">Add Project</button></div>
        <div class="col-9 col-md-10 pl-3 pl-xl-0"><p class="m-0">Add a project or chose from the list below.</p></div>
    </div>

    @modal(['name' => 'projectAdd', 'title' => 'Add Project', 'form_action' => '/create/project' ])
    <div class="form-group">
            <label for="projectAddInput">Project Name</label>
            <input type="text" class="form-control" name="project_name" id="projectAddInput">
        </div>
    @endmodal
@endif
@if(count($projects) > 0)
<div id="project-display" class="worksheet">
    <ul class="list-parent project-icon">
        @foreach($projects as $project)
        <li><a class="show-list list-{{ $loop->iteration }}">{{ ucwords($project['project_name']) }} <span class="timestamp">Last updated: {{ date('F j, Y', strtotime($project['updated_at'])) }}</span></a>

            <ul class="list-worksheet">
                @if($team == 0)
                <li class="list-config"><a href="#" data-toggle="modal" data-target="#projectOptions{{ $project['project_id'] }}Modal">Project Options</a></li>
                @endif


                @foreach($worksheets as $worksheet)
                {{-- <pre>{{ print_r($worksheet) }}</pre> --}}
                    @if($worksheet['active'] == 1)
                        @role('student')
                            @if($team == 0)
                            <li><a href="worksheets/{{ $worksheet['id'] }}/{{ $project['project_id'] }}"><span class="graded">Grade: <strong>10</strong> / {{ $worksheet['category_value'] }}</span>{{ $worksheet['title'] }}</a></li>
                            @else
                            <li><a href="team/{{ $worksheet['id'] }}/{{ $project['project_id'] }}"><span class="graded">Grade: <strong>10</strong> / {{ $worksheet['category_value'] }}</span>{{ $worksheet['title'] }}</a></li>
                            @endif
                        @endrole

                        @role('teacher', 'assistant-teacher', 'admin', 'manager', 'developer')
                            <li><a href="worksheets/{{ $worksheet['id'] }}/{{ $project['project_id'] }}"><span class="points">{{ $worksheet['category_value'] }} Points</span>{{ $worksheet['title'] }}</a></li>
                        @endrole

                    @endif
                @endforeach
            </ul>

            @if($team == 0)
                @modal([
                    'name' => 'projectOptions' . $project['project_id'],
                    'title' => 'Project Options for <span class="text-primary">' . ucwords($project['project_name']) . '</span>',
                    'form_action' => '/project/management',
                    'project_id' => $project['project_id']
                ])
                @if($user_data['team_check'])
                <div class="form-check">
                    <input type="radio" name="type" class="form-check-input" id="projectsend{{ $project['project_id'] }}" value="1">
                    <label for="projectsend{{ $project['project_id'] }}" class="form-check-label"><strong>Send to team</strong></label>
                    <p><small>Send this project to your group to work on it as a team! (Note: This project will no longer appear in the My ideas section, and will be listed under "My Team Ideas instead") </small></p>
                </div>
                @endif
                <div class="form-check">
                    <input type="radio" name="type" class="form-check-input" id="sendproject{{ $project['project_id'] }}" value="2" data-toggle="popover" data-content="<span class='text-danger'>Deleting a project is irreversible!</span>">
                    <label for="sendproject{{ $project['project_id'] }}" class="form-check-label text-danger"><strong>Delete project</strong></label>
                </div>
                @endmodal
            @endif

        </li>
        @endforeach
    </ul>
</div>
@else
    @if($team == 0)
        <p>Add a project to begin.</p>
    @else
        <p>Send a <a href="/worksheets/">project</a> to your team to begin.</p>
    @endif
@endif

{{-- {{ print_r(json_encode($projects)) }}

<pre>{{ print_r($worksheets) }}</pre> --}}



@endsection
