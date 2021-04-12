@extends('layouts.sidebar-layout')
@section('title', 'Start Video Conference')
@section('content')
@include('includes.notify')
<button
    type="button"
    class="btn btn-sm btn-success mr-2 mb-3"
    data-toggle="modal"
    data-target="#videocon1Modal">
    Start a Video Conference
</button>
<video-conference
    :chatlist='@json($videoconlist)'
    :conferences='@json($conferences)'
    :services_available='@json($services_available)'>
</video-conference>
@endsection
