@extends('layouts.login-layout')
@section('title', 'Sign-in')
@section('masthead-margin', 'mb-lg-5')
@include('includes.notify')

@section('content')
@if (session('status'))
<div class="alert alert-success" role="alert">
    {{ session('status') }}
</div>
@endif
<div class="row">
    <div class="col-md-6 m-auto center">

        <div class="box box-login">
            <form id="form-login" action="" method="post" class="form-login form-placeholder">
            @csrf
                <h3>Inventionland Institute Login</h3>

                @if (count($errors))
                    @notification(['type' => 'danger'])
                        @foreach($errors->all() as $error)
                        {{ $error }}<br>
                        @endforeach
                    @endnotification
                 @endif
                <div class="form-group">
                    <input type="text" name="email" id="email" class="form-control" placeholder="Username / Email" autocomplete="username" value="{{ $username }}">
                    <label for="email">Username</label>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" autocomplete="current-password">
                    <label for="password">Password</label>
                </div>
                <div class="form-group">
                    <button type="Submit" class="btn btn-block btn-action">Login</button>
                    <a href="{{ route('login.provider', 'google') }}" class="mt-4 btn btn-sm btn-block btn-primary">{{ __('Sign in with Google') }}</a>
                    {{-- <a href="{{ route('login.provider', 'apple') }}" class="mt-4 btn btn-sm btn-block btn-primary">{{ __('Sign in with Apple') }}</a> --}}
                    {{-- <a href="{{ route('login.provider', 'clever') }}" class="btn btn-sm btn-block btn-primary">{{ __('Sign in with Clever') }}</a> --}}
                    {{-- <a href="/loginusername/clever" class="btn btn-sm btn-block btn-primary">{{ __('Sign in with Clever') }}</a> --}}
                </div>
                <a href="/password/reset" class="small text-muted">Forgot your password?</a>
            </form>

        </div>
        <div class="box-login p-2">
            <a href="/activate/student" class="small">Activating account?</a>
        </div>
        <div class="mt-5 box small text-left"><small>THIS SYSTEM AND THE CURRICULUM TO BE ACCESSED THROUGH IT ARE RESTRICTED TO AUTHORIZED USERS FOR LICENSED, EDUCATIONAL USE ONLY. UNAUTHORIZED ACCESS IS STRICTLY PROHIBITED AND MAY BE PUNISHABLE UNDER THE COMPUTER FRAUD AND ABUSE ACT OF 1986 AND OTHER APPLICABLE LAWS. IF YOU ARE NOT AUTHORIZED TO ACCESS THIS SYSTEM, DISCONNECT NOW. BY CONTINUING, YOU CONSENT TO YOUR IDENTIFYING PERSONAL INFORMATION, KEYSTROKES AND DATA CONTENT BEING MONITORED AND RECORDED. THE USE OF THIS SYSTEM CONSTITUTES CONSENT TO MONITORING, RECORDING AND AUDITING. NO REPRODUCTION, REDISTRIBUTION, COPYING (REGARDLESS OF THE FORM OF COPY) OR OTHER USE OF THIS MATERIAL IS AUTHORIZED EXCEPT PURSUANT TO THE TERMS OF A LICENSE AGREEMENT AND TERMS OF USE, WHICH MUST BE ENTERED INTO BEFORE ACCESSING THIS SYSTEM. THE ADMINISTRATORS OF THIS SYSTEM RESERVE THE RIGHT TO CANCEL ACCESS TO THE SYSTEM UPON SUSPICION OF VIOLATION OF THE LICENSE AGREEMENT OR ABUSE OF THE SYSTEM. ALL TERMS DESCRIBED ABOVE ARE SUBJECT TO CHANGE WITHOUT NOTICE. IF YOU DO NOT AGREE TO THESE TERMS, DISCONNECT NOW!</small></div>
    </div>
</div>
@endsection
