@include('email.includes.header')

<p><strong>From:</strong> <a href="mailto:{{$email}}">{{$name}}</a> ({{$school}})</p>

<p><strong>Subject:</strong> {{$subject}}</p>

<hr style="border:0;border-top:solid #d9dfe9 1px;margin-top:2rem">
<br>
{!! $bodyMessage !!}

@include('email.includes.footer')
