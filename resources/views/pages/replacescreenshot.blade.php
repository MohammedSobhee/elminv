@extends('layouts.default')
@section('title', "Replace Assignment Screenshot")
@include('includes.notify')
@section('content')

<form method="post" action="/eduadmin/edit/screenshot" class="form" enctype="multipart/form-data">
{{ csrf_field() }}
<div class="row mt-5">
    <div class="col-2">
        <div class="form-group">
            <label for="name">Assignment ID</label>
            <input type="text" class="form-control" name="assignment_id" id="assignment_id">
        </div>
    </div>
    <div class="col-10">
        <label for="name" class="text-muted">Upload PNG only</label>
        <div class="custom-file">
            <input type="file" class="custom-file-input" name="screenshot" id="screenshot" accept="image/x-png">
            <label class="custom-file-label" for="customFile">Choose file</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <input type="submit" class="btn btn-primary" value="Submit">
    </div>
</div>
</form>
@endsection
