@extends('layouts.sidebar-layout')
@section('title', 'School  Administration')
@include('includes.notify')
@section('content')
@role('school-admin', 'teacher', 'assistant-teacher', 'manager', 'developer', 'admin')

@include('school_management.common.school-admin')

@endrole
@endsection
