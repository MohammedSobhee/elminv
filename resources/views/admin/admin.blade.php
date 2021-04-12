@extends('layouts.full-layout')
@section('title', 'Admin Dashboard')
@section('title-helper', auth()->user()->name . ' - ' . auth()->user()->role->name)
@section('content')
@include('includes.notify')
@role('admin', 'manager', 'developer')


@if(!$school_id)
<div class="mt-4 mb-3">
    <a href="/eduadmin/create/school" class="btn btn-sm btn-primary">Create School <i class="fas fa-angle-right"></i></a>
</div>

{{-- Search forms --}}
<div class="form-wrapper form-wrapper-search p-2">
    <div class="row">
        <div class="col">
            <div class="form-group">
                <label for="search-school" class="d-none">Search schools:</label>
                <school-select
                    :options='@json($schools)'
                    :selected="{{ isset($school_id) ? $school_id : ''}}">
                </school-select>
            </div>
        </div>
        <div class="col">
            <form action="/eduadmin/search/users" method="post">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="search" class="d-none">Search Users:</label>
                    <input type="text" name="search" class="form-control" id="search" placeholder="{{ $search_term ? $search_term : 'Search Users' }}">
                </div>
            </form>
        </div>
    </div>
</div>
@endif
{{-- Search Results --}}
@isset($results)
    @if($results->count())
    <h4 class="mt-4">{{ count($results) }} Search Results</h4>
    <edit-member
        :users='@json($results)'
        :enable_search='@json(true)'
        class="mt-3">
    </edit-member>
    @else
    <h4 class="mt-5">No results for {{ $search_term }}.</h4>
    @endif
    <img src="/assets/images/layout/search-by-algolia.svg" class="search-by-algolia">
@endisset


{{-- Expiring and Payment Due --}}
@if(!$school_id && !$search_term)
<div class="row eduadmin-lists">
    @if($expiring->count())
    <div class="col-md-6">
        <h4 class="mt-5">Expiring Schools:</h4>
        <ul class="list-group">
            @foreach($expiring as $item)
                @if($item->user_count > 0)
                <li class="list-group-item">
                    <a href="/eduadmin/edit/school/{{$item->id}}" class="row">
                        <div class="col-6">{{$item->name}}</div>
                        <div class="col-6 text-dark">{{$item->contract_expiration_date}}</div>
                    </a>
                </li>
                @endif
            @endforeach
        </ul>
    </div>
    @endif
    @if($payment_due->count())
    <div class="col-md-6">
        <h4 class="mt-5">Payment Due:</h4>
        <ul class="list-group">
            @foreach($payment_due as $item)
            <li class="list-group-item">
                <a href="/eduadmin/edit/school/{{$item->id}}" class="d-block">{{$item->name}}</a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endif

{{-- Edit School --}}
@if($school_id)
    <edit-school
        :schools='@json($schools)'
        :school='@json($school)'
        :school_id="{{ $school_id }}"
        :school_users='@json($school_users)'
        :school_types='@json($school_types)'
        :color_codes='@json($color_codes)'
        host="{{ request()->getHost() }}"
        :countries='@json($countries)'
        :standards='@json($standards)'
        :search_term='@json($search_term)'
        :admin_school='@json(intval(config('app.admin_school')))'>
    </edit-school>
@endif


{{-- Kristi's Stuff --}}
@if(!$school_id && !isset($results))
<div class="row eduadmin-lists">
    <div class="col-md-6">
        <h4 class="mt-5">Test Schools:</h4>
        <ul class="list-group">
            <li class="list-group-item"><a href="/eduadmin/edit/school/30">School of Rock</a> <span class="text-gray">(All Grades, lots of test data)</span></li>
            <li class="list-group-item"><a href="/eduadmin/edit/school/37">Copper Grove Academy</a> <span class="text-gray">(4-5 Grades, 9-12+ grades, some test data)</span></li>
            <li class="list-group-item"><a href="/eduadmin/edit/school/53">Fresh Academy</a> <span class="text-gray">(4-5 grades, little test data)</span></li>
            <li class="list-group-item"><a href="/eduadmin/edit/school/54">Fresh School 2</a> <span class="text-gray">(All Grades, no test data)</span></li>
            <li class="list-group-item"><a href="/eduadmin/edit/school/65">Clean Conservatory</a> <span class="text-gray">(9-12+, clean)</span></li>
            <li class="list-group-item"><a href="/eduadmin/edit/school/40">Semi-Empty School</a> <span class="text-gray">(9-12+, little data, no messages)</span></li>
            <li class="list-group-item"><a href="/eduadmin/edit/school/57">Iacon Elementary School</a> <span class="text-gray">(K-3 and 4-5 Grades, some test data)</span></li>
            <li class="list-group-item"><a href="/eduadmin/edit/school/36">Cybertron Public Schools</a> <span class="text-gray">(9-12+, some test data)</span></li>
        </ul>
    </div>
    <div class="col-md-6">
        <h4 class="mt-5">Course Demo</h4>
        <ul class="list-group">
            <li class="list-group-item"><a href="/dashboard/loginas/{{config('app.demo.admin')}}">Login as Demo School Admin</a></li>
            <li class="list-group-item"><a href="/eduadmin/edit/school/{{config('app.demo.school')}}">View Demo School</a></li>
        </ul>
    </div>
</div>
@endif







@endrole
@endsection
