@extends('layouts.default')
@section('masthead-margin', 'mb-0')
@section('content')

        <div class="justify-content-center align-self-center text-center">
            <img src="/assets/images/layout/error_chipper.png" class="error-page-image" />
            <h2 class="error-page-header">
                @yield('code')
            </h2>
            <div>
                @yield('message')
            </div>
        </div>

@endsection
