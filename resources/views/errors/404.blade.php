@extends('errors::error-layout')

@section('title', __('Not Found'))
@section('code', '404')
@section('message')

@if(isset($_SESSION['wp_error']))
@php
    $wp_error = $_SESSION['wp_error'];
    $_SESSION['wp_error'] = 0;
@endphp
<div class="alert alert-danger" role="alert">
    {{ $wp_error }}
</div>
@endif


The page requested is not found. <a href="/dashboard">Go to the Dashboard <i class="fas fa-angle-right"></i></a>
@endsection
