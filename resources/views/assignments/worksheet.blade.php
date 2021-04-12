@extends('layouts.default')
@if($team_id == 0)
    @section('title', 'My Ideas')
@else
    @section('title', 'My Team Ideas')
@endif
@section('masthead-margin', 'mb-0')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        {{-- <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li> --}}
        {{-- @if($team == 0) --}}
            <li class="breadcrumb-item"><a href="/assignments">My Assignments</a></li>
        {{-- @else
        <li class="breadcrumb-item"><a href="/assignments/team">Team Assignments</a></li>
        @endif --}}
        <li class="breadcrumb-item">{{ $project_name }}</li>
        <li class="breadcrumb-item" aria-current="page">{{ $title }}</li>
    </ol>
</nav>
@endsection

@section('content')
    @if($demo_error)
    <div class="mt-2 alert alert-warning" role="alert">This activity sheet has been disabled for demo accounts.</div>
    @else
    <div class="worksheet-wrapper">
        @if($active)
        <worksheet
            user_role="{{ $usersess->role }}"
            :worksheet='@json($worksheet)'
            :grade="{{ $grade }}"
            :status="{{ $status }}"
            :wid="{{ $wid }}"
            :pid="{{ $pid }}"
            :asid='@json($asid)'
            :has_locked='@json($has_locked)'
            :locked='@json($locked)'
            project_name="{{ $project_name }}"
            :team_id="{{ $team_id }}"
            :message='@json($message)'>
        </worksheet>
        @else
        <div class="alert alert-danger" role="alert">
            This activity sheet is not yet active. <a href="javascript:history.back()">Go Back.</a>
        </div>
        @endif
    </div>
    @endif
@endsection
