@extends('layouts.sidebar-layout')
@section('title', 'Messaging')
@section('content')
@include('includes.notify')


@if(count($classlist))
<messages
    user_role="{{ $usersess->role }}"
    :messages="{{ json_encode($messages) }}"
    :class_list="{{ json_encode($classlist) }}"
    :team_list="{{ json_encode($teamlist) }}"
    :user_list="{{ json_encode($userlist) }}"
    :archive_count="{{ $archive_count }}" />
@else
<info-alert>Before you can begin adding messages, complete setup by adding your first class.</info-alert>
@endif

@endsection
