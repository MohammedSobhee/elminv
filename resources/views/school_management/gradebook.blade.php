@extends('layouts.sidebar-layout')
@section('title', 'Gradebook')
@include('includes.notify')
@section('content')
<gradebook
    :classes='@json($classes)'
    :pending_user_type='@json($pending_user_type)'
    :pending_user_id='@json($pending_user_id)'>
</gradebook>

@endsection
