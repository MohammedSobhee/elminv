@extends('errors::error-layout')

@section('title', __('Uh oh...'))
@section('code', '500')
@section('message')
There is a problem with this request. Please contact <a href="/support">support</a> to let us know!
@endsection
