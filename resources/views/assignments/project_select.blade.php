@extends('layouts.no-layout')
@section('title', 'Select a Project')
@include('includes.notify')
@section('content')

@if(count($projects) > 0)
    <form>
        <div class="flex-row row mb-4 align-items-center">
            <div class="col-9">
            <select class="custom-select select-url-change">
                @foreach($projects as $project)
                    @if($project['team_id'])
                        <option value="/worksheets/team/{{ $worksheet_id }}/{{ $project['id'] }}">{{ ucwords($project['project_name']) }} (Team)</option>
                    @else
                        <option value="/worksheets/{{ $worksheet_id }}/{{ $project['id'] }}">{{ ucwords($project['project_name']) }}</option>
                    @endif
                @endforeach
            </select>
            </div>
            <div class="col-3">
                <button type="button" class="btn btn-block btn-secondary btn-url-change" data-target="select-url-change">Go</button>
            </div>
        </div>
    </form>
@else
    {{-- <p>Add a project to begin:</p> --}}
    <form action="/create/project" method="post" target="_parent">
    <div class="flex-row row mb-4 align-items-center">
        <div class="col-9">
            {{ csrf_field() }}
            <input type="hidden" name="worksheet_id" value="{{ $worksheet_id }}">
            <input type="text" class="form-control" name="project_name" id="projectAddInput" placeholder="Project Name">
        </div>
        <div class="col-3">
            <input type = "submit" class="btn btn-block btn-primary" value="Save">
        </div>
    </div>
    </form>
@endif

@endsection
