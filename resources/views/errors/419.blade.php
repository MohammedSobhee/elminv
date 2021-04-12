@extends('errors::error-layout')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message')
This page has experienced an issue. Try to <a href="/logout">logout</a> and log back in or contact <a href="https://inventionlandinstitute.com/support">support.</a>
@endsection
