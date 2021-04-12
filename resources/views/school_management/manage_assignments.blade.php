@extends('layouts.default')
@section('title', 'Custom Assignments')
@include('includes.notify')
@section('masthead-margin', 'mb-0')


@section('content')
{{-- <hr class="mb-3 mt-lg-5">
<h4 class="mb-3">Custom Assignment Repository</h4> --}}

{{-- {{ print_r(json_encode($data)) }} --}}
<manage-assignments
    :assignments="{{ json_encode($data) }}"
    view="{{ $view }}"
    :editing_id="{{ $editing_id }}">
</manage-assignments>
@endsection
