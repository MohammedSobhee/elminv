@extends('errors::error-layout')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message')
{{ __($exception->getMessage() ?: 'Service Unavailable') }}
This page has experienced an issue. Please contact <a href="https://inventionlandinstitute.com/support">support.</a>
@endsection
