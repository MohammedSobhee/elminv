@extends('layouts.sidebar-layout')
@section('title', 'Teams')
@include('includes.notify')
@section('content')

@modal(['name' => 'teamAdd', 'title' => 'Add Team', 'form_action' => '/create/team' ])
    <div class="form-group">
        <label for="teamAddInput">Team Name</label>
        <input type="text" class="form-control" name="team_name" id="teamAddInput">
    </div>
    <div class="form-group">
        <label for="teamClassInput">Add this team to class:</label>
        <select class="custom-select" name="class_id">
            @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
            @endforeach
        </select>
    </div>
@endmodal

@if(count($classes))
<div class="flex-row row no-gutters mb-2 p-2 bg-light-primary rounded align-items-center action-top">
    <div class="col-4 col-md-3 col-lg-2"><button type="button" data-toggle="modal" data-target="#teamAddModal" class="btn btn-sm btn-primary">Add team</button></div>
    <div class="col-8 col-md-9 col-lg-10"><p class="m-0">Add a team or click on one below to assign members, edit its settings, or delete it.</p></div>
</div>
@else
<info-alert>Before creating teams, start by <a href="/edit/class#modal-classAdd">adding</a> your first class.</info-alert>
@endif

{{-- <pre>
@php print_r($final) @endphp
</pre> --}}

<edit-team :team-list="{{ json_encode($classes) }}" :class_id="{{ $class_id }}"></edit-team>


@endsection
