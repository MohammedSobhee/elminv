@extends('layouts.default')
@section('title', 'Add Accounts')
@section('masthead-margin', 'mb-0')
@section('content')
@include('includes.notify')

<add-accounts
    :school_id="{{ $school_id }}"
    :class_id="{{ $class_id }}"
    :classes="{{ json_encode($classes) }}"
    :user_roles="{{ json_encode($user_roles) }}"
    :school_admin="{{ json_encode($school_admin) }}"
    :users="{{ json_encode($users) }}"
    school_name="{{ $school_name }}">
</add-accounts>

@endsection
