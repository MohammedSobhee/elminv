<meta charset="utf-8">
<title>@yield('title') | Inventionland Institute</title>
<meta name="description" content="@yield('title') | Inventionland Institute"/>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3">
<meta name="csrf-token" content="{{ csrf_token() }}">

@if (app()->environment() === 'production')
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-179430737-2"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-179430737-2');
</script>
@endif

@if(Auth::check())
@php
    $edudata = [
        'user_id' => auth()->user()->id,
        'user_role' => auth()->user()->role->slug,
        'class_id' => auth()->user()->getclassID(),
        'teacher_name' => auth()->user()->getTeacherName()
    ]
@endphp
<meta name="eduiland-data" content="{{ base64_encode(json_encode($edudata)) }}">
@endif

<link rel="canonical" href="{{ URL::current() }}" />
<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,500,500i,600,600ii&display=swap" rel="stylesheet">
{{-- <script src="https://kit.fontawesome.com/15cc1ac790.js" crossorigin="anonymous"></script> --}}
<link rel='stylesheet' id='font-awesome-5-all-css'  href='/course/wp-content/plugins/elementor/assets/lib/font-awesome/css/all.min.css' type='text/css' media='all' />
<link rel='stylesheet' id='eduiland-style-css'  href='/course/wp-content/themes/eduiland/assets/css/site.css?ver={{ filemtime( public_path() . '/course/wp-content/themes/eduiland/assets/css/site.css' )}}' type='text/css' media='all' />

@include('includes.head-icons')

{{-- @if(Auth::check())
    @php wp_head(); @endphp
@else
    <link rel="stylesheet" href="/course/wp-content/themes/eduiland/assets/css/site.css" type="text/css" media="all" />
@endif --}}

{{-- @php
wp_head();
@endphp --}}
