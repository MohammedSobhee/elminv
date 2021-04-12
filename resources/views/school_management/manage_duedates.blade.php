@extends('layouts.sidebar-layout')
@section('title', 'Set Assignment Due Dates')
@include('includes.notify')
{{-- @section('masthead-margin', 'mb-0') --}}


@section('content')
<manage-duedates
    :class_count="{{ $class_count }}"
    :worksheet="{{ json_encode($worksheet) }}"
    :custom="{{ json_encode($custom) }}"
    :editing="{{ json_encode($editing)}}">
</manage-assignments>
@endsection
