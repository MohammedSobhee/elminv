@extends('layouts.sidebar-layout')
@section('title', 'School Members')
@include('includes.notify')
@section('content')

@role('school-admin', 'teacher', 'assistant-teacher', 'manager', 'developer', 'admin')
<h4><strong>School:</strong> {{ $school_name }}</h4>
<ul class="list-tree">
    @foreach($school_users as $user)
        <li><strong>{{ $user->role->name }}:</strong> <a class="text-dark" href="mailto:{{ $user->email }}">{{ ucwords($user->first_name. ' ' .$user->last_name) }}</a>
            @isset($user->classes)
            <ul>
                @foreach($user->classes as $class)
                    <li><strong>Class {{ $class->class_type_name }}:</strong> <a class="text-primary" href="/edit/class/{{ $class->id }}">{{ $class->class_name }}</a>
                    @isset($class->members)
                    <ul>
                        @foreach($class->members as $member)
                        <li><strong>{{ $member->role->name }}:</strong> <a class="text-primary" href="/edit/class/{{ $class->id }}/{{ $member->id }}">{{ ucwords($member->first_name. ' ' .$member->last_name) }} <i class="fas fa-chevron-right"></i></a></li>
                        @endforeach
                    </ul>
                    @endisset
                    </li>
                @endforeach
            </ul>
            @endisset
        </li>
    @endforeach
</ul>
@endrole

@role('student')
Access denied.
@endrole

@endsection
