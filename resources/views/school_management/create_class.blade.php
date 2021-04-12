@extends('layouts.sidebar-layout')
@section('title', 'Create Class')
@include('includes.notify')
@section('content')

@if (count($errors))
@notification(['type' => 'danger'])
    @foreach($errors->all() as $error)
        - {{ $error }}<br>
    @endforeach
@endnotification
@endif

    <form method="POST" action="/create/class">
        {{ csrf_field() }}
        <div class="row">
            <div class="col form-group">
                <label for="class_name">Class Name:</label>
                <input type="text"  class="form-control" name="class_name" id="class_name">
            </div>
            <div class="col form-group">
                <label for="class_grade_level">Grade Level:</label>
                <select class="custom-select" name="grade_level" id="class_grade_level">
                    <option selected>Choose...</option>
                    <option value="1">K-3 Grades (No Student login)</option>
                    <option value="2">4-5 Grades</option>
                    <option value="3">6-8 Grades</option>
                    <option value="4">9-12+ Grades</option>
                </select>
            </div>
        </div>
        <div>
            <input type="submit" name="submit" value="Create Class" class="btn btn-primary">
        </div>
    </form>

@endsection
