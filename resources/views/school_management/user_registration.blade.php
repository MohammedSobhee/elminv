@extends('layouts.default')
@section('title', ucfirst($type_name) . ' Activation')
@section('content')
@section('masthead-margin', 'mb-lg-5')

@if (count($errors))
@notification(['type' => 'danger'])
    @foreach($errors->all() as $error)
        - {{ $error }}<br>
    @endforeach
@endnotification
@endif

    <form method="post" action="/activate/user" class="form" id="form-activation-contactinfo">
        <input type="hidden" name="activation_code" value="{{ $activation_code }}">
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="school_id" value="{{ $school_id }}">
        <input type="hidden" name="class" value="{{ $class }}">
        <input type="hidden" name="class_id" value="{{ $class_id }}">
        {{ csrf_field() }}

        <h4 class="form-heading">{{ ucfirst($type_name) }} Information </h4>

        @if($type_name == 'Teacher')
        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="subject">Class Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="form-control" placeholder="Subject" />
            </div>
        </div>
        @endif

        @if($type_name == 'School')
            <p>Please provide the information of the administrator that will be using the system:</p>
            <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" name="dual" id="dual" class="custom-control-input" placeholder="dual" />
                <label class="custom-control-label" for="dual"><strong>Do you want this school administrator account to also function as a teacher?</strong></label>
            </div>
        @elseif($dual)
            <input type="hidden" name="dual" value="1" />
        @endif
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" value="{{ $first_name ?: old('first_name') }}" {{ $first_name ? 'readonly' : '' }} />
            </div>
            <div class="form-group col-md-6">
                <label for="last_name"> Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name" value="{{ $last_name ?: old('last_name') }}" {{ $last_name ? 'readonly' : '' }} />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="email">Username / Email</label>
                <input type="text" name="email" id="email" class="form-control" placeholder="Email / Username" value="{{ $email ?: old('email') }}" {{ $last_name ? 'readonly' : '' }}/>
                @error('email')<em class="error">Email required</em>@enderror
            </div>
            <div class="form-group col-md-3">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" />
            </div>
            <div class="form-group col-md-3">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" name="password_confirm" id="password_confirm" autocomplete="new-password" class="form-control" placeholder="Confirm Password"/>
            </div>
        </div>


        <input type="submit" name="submit" value="Create Account" class="btn btn-primary">




    </form>

@endsection
