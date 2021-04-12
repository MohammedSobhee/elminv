@if($teacher_class_count)

    @if( !isset($teacher_settings['chat_class']) && !isset($teacher_settings['chat_team']) && !isset($teacher_settings['chat_private']) )
    <info-alert type="chat">Enable live class room discussion in the <a href="/edit/settings">settings</a> area.</info-alert>
    @endif

    @if( !isset($teacher_settings['videocon_google']) && !isset($teacher_settings['videocon_zoom']))
        <info-alert type="videocon">Enable video conferencing in the <a href="/edit/settings">settings</a> area.</info-alert>
    @endif

    @if(!$teacher_message_count && !isset($teacher_settings['message_class']))
        <info-alert type="message">Add a welcome message to students' dashboard in the <a href="/messages">messaging</a> area.</info-alert>
    @endif

@else
    @php the_field('dashboard_introduction_teacher', 677) @endphp
    <info-alert>Start by <a href="/edit/class#modal-classAdd">adding</a> your first class.</info-alert>
    @if(auth()->user()->canSchoolAdmin())
    <info-alert><a href="/schooladmin">School Administration</a> is under Teacher Tools in the top menu.</info-alert>
    @endif
@endif

@if(count($teacher_pending))
<dashboard-pending :assignment-list="{{ json_encode($teacher_pending) }}"></dashboard-pending>
@endif

{{-- @if(!$chat_settings && !$videocon_settings && !$teacher_message_count && !$teacher_class_count)
    @php the_field('dashboard_introduction_teacher', 677) @endphp
@endif --}}
