@if($user_messages || $team_messages || $class_messages)
    <dashboard-messages
        class="mb-5"
        :user_messages="{{ json_encode($user_messages) }}"
        :team_messages="{{ json_encode($team_messages) }}"
        :class_messages="{{ json_encode($class_messages) }}">
    </dashboard-messages>
@else
    @php the_field('dashboard_introduction_student', 677) @endphp
    <br>
@endif

@if($usersess->class_type == 99)
    @notification(['type' => 'primary']) You are not in a class yet. @endnotification
@else
    <dashboard-assignments
        class="mb-5"
        :assignment-list="{{ json_encode($assignments) }}"
        :worksheet-list="{{ json_encode($worksheets) }}"
        :team_id="{{ $team_id }}"
        user_role="{{ $usersess->role }}">
    </dashboard-assignments>
@endif
