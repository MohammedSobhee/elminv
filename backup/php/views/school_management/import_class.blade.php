@extends('layouts.sidebar-right')
@section('title', 'Import Class')
@section('content')

    <p>Upload a file in CSV format with the following fields in the following order:</p>
    <ol>
        <li><strong>User Type</strong> - Either "student" or "teacher" (without quotes) will be acceptable.</li>
        <li><strong>Username</strong> - The user will login with this number as their username, if left blank the system will set the username to lastname.firstname</li>
        <li><strong>Email Address</strong> - If this field is left blank the system will generate an e-mail address using the following criteria: lastname.firstname@schoolname.com</li>
        <li><strong>First Name</strong> - The first name of the student. Required.</li>
        <li><strong>Last Name</strong> - The last name of the student. Required.</li>
    </ol>
    <ul>
        <li><a href="/files/import_sample_full.csv">Download a sample CSV file</a> with all fields filled in.</li>
        <li><a href="/files/import_sample.csv">Download a CSV file</a> with some fields left blank.</li>
    </ul>

    <form method="POST" action="/register">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="csv_file">Choose file</label>
            <input type="file" id="csv_file" class="form-control-file">
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary">
        </div>
    </form>

@endsection
