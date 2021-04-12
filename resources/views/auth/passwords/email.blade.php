@extends('layouts.default')
@section('title', 'Reset Password')
@section('masthead-margin', 'mb-lg-5')
@include('includes.notify')

@section('content')
<div class="row">
    <div class="col-md-6 m-auto">
        <div class="box box-login">

            @if (session('status'))
                <div class="alert alert-success medium" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="form-login form-placeholder">
            <h3>{{ __('Reset Password') }}</h3>
            <hr class="border-top my-2">
            <p class="medium m-0"><strong>{{ __('Students:') }}</strong> {{ __('If your username is not an email address, please contact your teacher to reset your password.') }}</p>
            <hr class="border-top mt-2 mb-4">
                @csrf

                <div class="center">
                    <div class="form-group">
                        <label for="email">{{ __('Email Address') }}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('Email Address') }}">

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection
