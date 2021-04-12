@extends('layouts.sidebar-layout')
@section('title', "Support")
@include('includes.notify')
@section('content')

<div class="row">
    <div class="col-md py-3">
        <h4>Have any concerns?</h4>
        <p>Fill out the form below or call support if you have any comments, suggestions, support requests, or bug reports
            concerning Inventionland Institute. You should expect a response within 24 hours during week days.</p>
    </div>
    <div class="col-md">
        <div class="border-left p-3">
            <strong>Call support:</strong> 1-800-585-8434
            <br><br>
            <strong>Mailing Address:</strong><br>
            Inventionland Institute<br>
            585 Alpha Dr.<br>
            Pittsburgh, PA 15238<br>
        </div>
    </div>
</div>
<form method="post" action="/support" class="form">
{{ csrf_field() }}
<div class="row mt-5">
    <div class="col">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" name="name" id="name" value="{{$name}}">
        </div>
    </div>
    <div class="col">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" class="form-control" name="email" id="email" value="{{$email}}">
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="form-group">
            <label for="subject">Subject</label>
            <select class="custom-select" name="subject">
                <option>General Question</option>
                <option>Website Suggestion</option>
                <option {{ $subject === 'bug' ? 'selected' : ''}}>Bug Report</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="form-group">
            <label for="bodyMessage">Message</label>
            <textarea class="form-control" id="bodyMessage" rows="8" name="bodyMessage">{{old('bodyMessage')}}{{$message}}</textarea>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <input type="submit" class="btn btn-primary" value="Send to Support">
    </div>
</div>
</form>

@endsection
