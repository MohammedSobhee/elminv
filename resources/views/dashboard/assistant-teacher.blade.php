@if(count($teacher_pending))
    <dashboard-pending :assignment-list="{{ json_encode($teacher_pending) }}"></dashboard-pending>
@else
    @php the_field('dashboard_introduction_teacher', 677) @endphp
@endif
