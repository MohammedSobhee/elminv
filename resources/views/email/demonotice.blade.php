@include('email.includes.header')

<p>A demo account has been created:</p>
<hr style="border:0;border-top:solid #d9dfe9 1px">
<strong>First Name:</strong> {{ $first_name }}<br>
<strong>Last Name:</strong> {{ $last_name }}<br>
<strong>Email:</strong> {{ $email }}<br>
<strong>Role:</strong> {{ $role }}
<hr style="border:0;border-top:solid #d9dfe9 1px">
<br>
<p align="center"><strong><a href="{{ $url }}" class="btn-primary">View Latest Demo Accounts</a></strong></p>

@include('email.includes.footer')
