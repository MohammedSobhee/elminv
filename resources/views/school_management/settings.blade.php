@extends('layouts.sidebar-layout')
@section('title', 'Course Settings')
@include('includes.notify')
@section('content')

<settings-teacher
    :rubric-categories="{{ json_encode($rubric) }}"
    :show-worksheets="{{$usersess->worksheets}}"
    :rubric-worksheets="{{ json_encode($worksheets) }}"
    :zoom-auth="{{ json_encode($zoom_auth) }}"
    :settings="{{ json_encode($settings) }}">
</settings-teacher>



@endsection
