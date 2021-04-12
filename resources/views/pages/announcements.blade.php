@extends('layouts.sidebar-layout')
@section('title', "Inventionland Institute Announcements")
@include('includes.notify')
@section('content')


@php
    $args = array(
        // 'cat' => 3, // Only source posts from a specific category
        'posts_per_page' => 5 // Specify how many posts you'd like to display
    );
    $latest_posts = new WP_Query( $args );
@endphp

@if($latest_posts->have_posts())
    @while( $latest_posts->have_posts())
        @php $latest_posts->the_post(); @endphp
        <div class="announcements">
        <h3>
            {{ the_title() }}
            @if($announcement_id == get_the_ID())
            <span class="text-success">(New!)</span>
            @endif
        </h3>
        <p><small class="text-muted">Posted on {{ the_time('l jS F, Y') }} </small></p>
        <div>
            {{ the_content() }}
        </div>
    </div>
    @endwhile
@else
    <p>There are no announcements available.</p>
@endif

@php
wp_reset_postdata();
@endphp




@endsection
