@include('email.includes.header')

Hello {{ ucfirst(auth()->user()->first_name) }},

<p>Class name: <strong>{{ $class }}</strong>
<hr style="border:0;border-top:solid #d9dfe9 1px">
<p>Your students may visit the link below to create their account and use the code below that will automatically place them into this class. There will be a unique code for each class. </p>
<p><a href="{{ $host }}/activate/student/" style="color:#0072b6">{{ $host }}/activate/student/</a></p>
<p>Student Code: <strong>{{$student_code}}</strong> </p><br />
<hr style="border:0;border-top:solid #d9dfe9 1px">


<p>Your assistant teachers may activate their account at the following URL:<br/>
<a href="{{ $host }}/activate/assistant/" style="color:#0072b6">{{ $host }}/activate/assistant/</a></p>
<p>Assistant Teacher Code: <strong>{{ $teacher_code }}</strong></p>

<br /><br />
Thank you,<br />
The Inventionland Institute Team

@include('email.includes.footer')
