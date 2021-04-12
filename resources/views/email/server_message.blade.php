@include('email.includes.header')

<p><strong>Message:</strong><br>{{ $server_message }}</p>
<hr style="border:0;border-top:solid #d9dfe9 1px">
@if($server_file)
<p><strong>File:</strong><br>{{ $server_file }}</p>
@endif

@if($server_trace)
    <p><strong>Trace:</strong><br>{{ $server_trace }}</p>
@endif

@include('email.includes.footer')
