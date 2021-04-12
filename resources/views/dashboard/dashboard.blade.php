@extends('layouts.sidebar-layout')
@section('title', 'Dashboard')

@role('school-admin', 'teacher', 'assistant-teacher', 'admin', 'manager', 'developer')
    @section('title-helper', auth()->user()->name . ' - ' . auth()->user()->role->name)
@endrole

@role('student')
    @section('title-helper', 'Class: ' . auth()->user()->getClassName() . ' - Teacher: ' . auth()->user()->getTeacherName())
@endrole

@section('content')
@include('includes.notify')

@if (session('status'))
<div class="alert alert-success" role="alert">
    {{ session('status') }}
</div>
@endif


@if($agreement)
    @include('dashboard.agreement')
@endif


{{-- Students --}}
@role('student')
<div class="dashboard">
    @include('dashboard.student')
</div>
@endrole

{{-- Chat / Video --}}
@role('teacher', 'assistant-teacher', 'student')
    @if(count($chatlist))
    <div class="mb-5 dashboard-chat">
        <dashboard-chat
            :user="{{ json_encode(auth()->user()) }}"
            :user_role="{{ json_encode(auth()->user()->role->slug) }}"
            :msgs="{{ json_encode($chat_messages) }}"
            :chatlist="{{ json_encode($chatlist) }}">
        </dashboard-chat>
    </div>
    @endif

    @if(count($participants_list))
    <video-conference
        class="mb-5"
        location="dashboard"
        :participants_list='@json($participants_list)'
        :student_create="{{ $videocon_settings['videocon_student'] }}"
        :conferences='@json($conferences)'
        :services_available='@json($services_available)'>
    </video-conference>
    @endif
@endrole

<div class="dashboard">

    {{-- School Admin / Admin --}}
    @role('manager', 'developer', 'admin')
        @include('dashboard.admin')
    @endrole



    {{-- Teachers --}}
    @role('teacher')
        @include('dashboard.teacher')
    @endrole

    {{-- Assistant Teachers --}}
    @role('assistant-teacher')
        @include('dashboard.assistant-teacher')
    @endrole


    {{-- School admin --}}
    @role('school-admin')
        @include('school_management.common.school-admin')
    @endrole

</div>
@endsection
