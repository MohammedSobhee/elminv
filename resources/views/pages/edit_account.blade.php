@extends('layouts.sidebar-layout')
@section('title', auth()->user()->first_name . "'s Account")
@include('includes.notify')
@section('content')

<edit-account :user="{{ json_encode($user) }}" />

@endsection
