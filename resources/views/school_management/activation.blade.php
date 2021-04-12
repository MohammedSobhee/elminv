@extends('layouts.default')
@section('title', $type_name . ' Activation')
@section('content')
@section('masthead-margin', 'mb-lg-5')
@include('includes.notify')


        <div class="box box-login">
            <form id="activation-code" action="/activate/process" method="post" class="form-login form-placeholder">
                {{ csrf_field() }}
                <h3>Enter your activation code:</h3>

                @if (count($errors))
                    @notification(['type' => 'danger'])
                        @foreach($errors->all() as $error)
                            - {{ $error }}<br>
                        @endforeach
                    @endnotification
                @endif
                <div class="form-group">
                    <input type="text" name="activation_code" id="activation-code" class="form-control" placeholder="Activation Code">
                    <input type="hidden" name = "type" value = "{{ $type }}" />
                    <label for="activation-code">Activation Code</label>
                </div>
                <div class="form-group">
                    <input type="submit" id="activation-submit" class="btn btn-primary btn-block" value="Activate your Account">
                </div>
            </form>
        </div>


@endsection
