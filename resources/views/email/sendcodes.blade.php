@include('email.includes.header')

@if($first_name)
Hi {{ $first_name }},
@endif

@if($demo)
<p>You have been sent an activation code for creating a demo account on Inventionland Institute:</p>
@else
<p>You have been sent an activation code for creating an account with Inventionland Institute:</p>
@endif

<br>

<hr style="border:0;border-top:solid #d9dfe9 1px">

@if($demo)
<p>Demo Activation Code: <strong>{{ $activation_code }}</strong></p>
@else
<p>Activation Code: <strong>{{ $activation_code }}</strong></p>
@endif

<hr style="border:0;border-top:solid #d9dfe9 1px">

@if($note)
<p>{{ $note }}</p>
@endif
<br>

@if($demo)
<p align="center"><strong><a href="{{ $url }}" class="btn-primary">Activate your demo account</a></strong></p>
@else
<p align="center"><strong><a href="{{ $url }}" class="btn-primary">Activate your account</a></strong></p>
@endif


<br /><br />
Thank you,<br />
The Inventionland Institute Team

@include('email.includes.footer')
