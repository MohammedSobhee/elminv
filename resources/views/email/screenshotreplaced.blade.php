@include('email.includes.header')

@if($first_name)
Hi {{ $first_name }},
@endif

<p>The screenshot for <strong>{{ $assignment_name }}</strong> has been replaced with:</p>
<hr style="border:0;border-top:solid #d9dfe9 1px">

<img src="{{ $message->embed(public_path() . '/uploads/assignments/' . $assignment_screenshot) }}" style="width:550px;height:auto">


<br /><br />
Thank you,<br />
The Inventionland Institute Team

@include('email.includes.footer')
