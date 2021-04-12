@extends('layouts.full-layout')
@section('title', 'Create School')
@include('includes.notify')
@section('content')
@norole('student')
<create-school
    :class_types='@json($class_types)'
    :standards='@json($standards)'
 />
@endrole
@endsection
