<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="copyright col-md-8 align-self-center">
                &copy; {{ $copyright }} {{ config('app.name') }} Courseware
                {{-- @auth
                <span class="footer-user"><span class="separator">|</span> {!! auth()->user()->email .' <span class="small"><i class="fas fa-chevron-right"></i></span> '. auth()->user()->role->name !!}</span>
                @endauth --}}
            </div>
            <div class="footer-social col-md-4">
            <ul class="footer-social-buttons">
                <li><a href="https://www.facebook.com/pages/Inventionland-Institute/727086124070664?fref=ts" class="facebook" target="_blank"><span class="sr">Facebook</span></a></li>
                <li><a href="https://twitter.com/IL_Institute" class="twitter" target="_blank"><span class="sr">Twitter</span></a></li>
                <li><a href="https://www.instagram.com/il_institute" class="instagram" target="_blank"><span class="sr">Instagram</span></a></li>
            </ul>
            </div>
        </div>
    </div>
</footer>
