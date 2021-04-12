@extends('layouts.default')
@section('title', 'Sign-in')
@section('masthead-margin', 'mb-lg-5')
@include('includes.notify')
@section('content')
<login-username
    driver="{{$driver}}"
    user="{{ session()->get('login_username') }}"
/>
@endsection
