@extends('layouts.sidebar-layout')
@section('title', 'Assignments')
@include('includes.notify')
@section('content')

<assignments
    :assignment-list="{{ json_encode($custom_list) }}"
    :assignment-id="{{ $assignment_id }}"
    :assignment-category-id="{{ $assignment_category_id }}"
    :project-list="{{ json_encode($project_list) }}"
    :team_id="{{ $team_id }}"
    :show_worksheets="{{ $usersess->worksheets}}"
    user_role="{{ $usersess->role }}">
</assignments>


@role('admin', 'teacher', 'assistant-teacher')
<h4 class="mt-5">Other Assignments</h4>
<a href="/edit/assignments" class="btn btn-sm btn-secondary">Manage Custom Assignments</a>
@endrole

@endsection
