@extends('errors::error-layout')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message')
{{ __($exception->getMessage() ?: 'Forbidden') }}
<p><a href="/logout">Logout</a> or contact <a href="https://inventionlandinstitute.com/support">support.</a></p>
@endsection
