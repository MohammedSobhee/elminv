@include('email.includes.header')

@if($first_name)
Hi {{ $first_name }},
@endif

@if($demo)
<p>You have been sent login information for a demo account on Inventionland Institute:</p>
@else
<p>You have been sent login information for Inventionland Institute:</p>
@endif

<br>

<hr style="border:0;border-top:solid #d9dfe9 1px">
<p>Username: <strong><a name="email" class="notlink">{{ $email }}</a></strong></p>
<hr style="border:0;border-top:solid #d9dfe9 1px">

@if($note)
<p>{{$note}}</p>
@endif
<br>

@if($demo)
<p align="center"><strong><a href="{{ $url }}" class="btn-primary">Log in to your demo account</a></strong></p>
@else
<p align="center"><strong><a href="{{ $url }}" class="btn-primary">Log in to your account</a></strong></p>
@endif

<br>

<p style="font-size:smaller">If you're not sure what your password is, you can <a href="https://edu.inventionlandinstitute.com/password/reset">reset your password.</p>
<br /><br />
Thank you,<br />
The Inventionland Institute Team

@include('email.includes.footer')
