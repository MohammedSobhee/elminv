@extends('layouts.sidebar-layout')
@if($class_name != '')
    @section('title', 'Class: ' . $class_name)
    @section('title-helper', $class_type_name)
@else
    @section('title', 'Classes')
@endif
@include('includes.notify')
@section('content')

{{-- Add Class Modal --}}
<create-class
    :classes="{{json_encode($classes)}}"
    :class_types="{{json_encode($class_types)}}">
</create-class>

{{-- Add Class / Select Class --}}
<div class="flex-row row no-gutters {{ ($class_count > 1 || $usersess->role == 'teacher' || $usersess->role == 'admin') ? 'mb-lg-4 p-2 action-top rounded' : '' }}">
    @role('teacher', 'admin')
        <div class="col-4 col-md-3 col-lg-2">
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#classAddModal">
            Add class
        </button>
        </div>
    @endrole

    @if($class_count > 1)
    <div class="pl-md-2 pl-xl-0
        col-{{ $usersess->role == 'assistant-teacher' ? '12' : '8' }}
        col-md-{{ $usersess->role == 'assistant-teacher' ? '12' : '9' }}
        col-lg-{{ $usersess->role == 'assistant-teacher' ? '12' : '10' }}">
        <form>
            <vue-select
                :options="{{ json_encode($classes) }}"
                css_class="multiselect-sm"
                placeholder="{{ $usersess->role == 'assistant-teacher' ? 'Select a class to edit:' : 'Or search for a class to edit:' }}"
                :selected="{{ $class_id }}"
                select_url="/edit/class/"
                added_options_data="class_type_name"
                id_by="id"
                label_by="class_name">
            </vue-select>
        </form>
    </div>
    @endif
</div>

{{-- Class List  --}}
{{-- @if($class_count > 1 && !$class_id && $classes_has_members) - Requested removed by Clay --}}
@if($class_count > 1 && !$class_id)

<ul class="list-group">
    @foreach($classes as $item)
    <li class="list-group-item medium">
        <a href="/edit/class/{{$item->id}}" class="row">
            <div class="col-4">{{$item->class_name}}</div>
            <div class="col-4 text-dark">{{$item->type->name}}</div>
            <div class="col-4 text-dark">
                @if($item->users->count()-1)
                <span data-toggle="tooltip" title="@foreach($item->users as $user)
                                @if(!$loop->first)
                                    {{$user->full_name}}{{!$loop->last ? ',' : ''}}
                                @endif
                            @endforeach">{{$item->users->count() - 1}} Members
                </span>
                @else
                <span>{{$item->users->count() - 1}} Members</span>
                @endif
            </div>
        </a>
    </li>
    @endforeach
</ul>
@endif

{{-- Class not yet selected --}}
@if(!$class_id)
    @if(!$class_count > 0 && auth()->user()->role->slug !== 'assistant-teacher')
        <info-alert>Begin by adding a class.</info-alert>
    @endif
@endif


{{-- Class Selected --}}
@if($class_id > 0 || $class_count == 1)
    @if($show_users)
        @if($class_count > 1 || auth()->user()->role->slug == 'teacher')
            <hr class="mt-0 mb-3" />
        @endif
        {{-- Class Member List --}}
        <member-list
            :users='@json($users)'
            :users_assigned='@json($assigned)'
            :class_id="{{ $class_id }}"
            class_name="{{ $class_name }}"
            :student_id="{{ $student_id }}">
        </member-list>
    @else
        <info-alert>Begin by adding students and/or assistant teachers manually or via activation codes.</info-alert>
        <info-alert><a href="/messages">Set a dashboard message</a> for classes.</a></info-alert>
    @endif

    {{-- Edit Class Settings --}}
    <edit-class
        class="mt-5 pt-1"
        host="{{ Request::getHost() }}"
        :classes="{{json_encode($classes)}}"
        :class_id="{{ $class_id }}"
        class_name="{{ $class_name }}"
        :class_type="{{ $class_type }}"
        :class_types='@json($class_types)'
        student_code="{{ $student_code }}"
        assistant_code="{{ $assistant_code }}"
        :show_codes="{{ $show_users ? 0 : 1 }}">
    </edit-class>
@endif
@endsection
