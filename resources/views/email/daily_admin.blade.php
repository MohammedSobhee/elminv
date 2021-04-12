@include('email.includes.header')


@if($expiring->count())
<p><strong>The following school(s) are now expiring in 6 months:</strong></p>
<table style="width:100%">
    @foreach($expiring as $item)
        @if($item->user_count > 0)
        <tr>
            <td><a href="{{ $host }}/eduadmin/edit/school/{{$item->id}}" style="color:#0072b6">{{ $item->name }}</a></td>
            <td>{{ $item->contract_expiration_date }}</td>
        </tr>
        @endif
    @endforeach
</table>
<br><hr style="border:0;border-top:solid #d9dfe9 1px">
@endif


@if($payment_due->count())
<p><strong>As a reminder, the following school(s) have payments due:</strong></p>
<table style="width:100%">
    @foreach($payment_due as $item)
    <tr>
        <td><a href="{{ $host }}/eduadmin/edit/school/{{$item->id}}" style="color:#0072b6">{{ $item->name }}</a></td>
    </tr>
    @endforeach
</table>
<br>
<hr style="border:0;border-top:solid #d9dfe9 1px">
@endif



<p><a href="{{ $host }}/eduadmin/" style="color:#0072b6">Go to admin</a></p>



@include('email.includes.footer')
