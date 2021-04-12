@extends('layouts.no-layout-vue')
@section('title', 'Live Classroom Discussion')
@section('content')
<chat
    :user="{{ auth()->user() }}"
    :msgs="{{ json_encode($messages) }}"
    ctype="{{ $ctype }}"
    ctype_id="{{ $ctype_id }}"
    :popup="true">
</chat>
@endsection
