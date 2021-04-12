@include('email.includes.header')

Welcome {{ ucfirst($first_name) }}!

<p>Activate your school administrator account for <strong>{{ $school_name }}</strong>: </p>

<div style="text-align:center">
<p style="margin-top:2.5rem"><a href="{{ $host }}/activate/school/" class="btn-primary">Activate Account</a></p>
<p style="margin-top:1.5rem">School Code: <strong>{{ $school_code }}</strong></p>
</div>

<hr style="border:0;border-top:solid #d9dfe9 1px;margin-top:2rem">
<div style="padding-top:.5rem;padding-bottom:.5rem">
    Teachers may activate their account at:<br><a href="{{ $host }}/activate/teacher/" style="color:#0072b6">{{ $host }}/activate/teacher/</a>
    <br><br>Teachers have a separate activation code: <strong>{{ $teacher_code }}</strong>
</div>
<hr style="border:0;border-top:solid #d9dfe9 1px">

<p style="margin-top:2rem">When your teachers create a class, they will be given an activation and a url to pass out to their students. This code will allow the students to be automatically placed into the correct class. There will be a separate code given out for each class created, but the activation URL will remain the same.</p>
<p>Your teachers will also have the ability to add assistant teachers to each individual class. Each class added will also generate an assistant teacher code to allow assistant teachers to be automatically placed in the correct class. The teacher dashboard also has the ability to add assistant teachers multiple classes.</p>

<br /><br />
Thank you,<br />
The Inventionland Institute Team


@include('email.includes.footer')
