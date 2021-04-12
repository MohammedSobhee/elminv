{{-- @auth
@php wp_footer(); @endphp
@endauth --}}

<script src="{{ mix('/assets/js/manifest.js') }}"></script>
<script src="{{ mix('/assets/js/vendor.js') }}"></script>
<script src="{{ mix('/assets/js/app.lvl.js') }}"></script>

@if(isset($uri) && Str::contains($uri, ['demo', 'login']) || !Auth::check())
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script type='text/javascript' src='/assets/js/jquery.validate.min.js'></script>
    <script type='text/javascript' src='/assets/js/validation.js?v=0.5'></script>
@endif

@if(isset($uri) && Str::contains($uri, ['edit/screenshot']))
<script>
    $('#screenshot').on('change',function(){
    var fileName = $(this).val().match(/[^\\/]*$/)[0];
    $(this).next('.custom-file-label').html(fileName);
})
</script>
@endif
