@if(session('success') || session('warning') || session('danger') || session('error') || session('info') || session('primary'))
    @section('notification')

        @if(session('success'))
            @notification(['type' => 'success', 'use' => 'close'])
                {!! session('success') !!}
            @endnotification
        @endif

        @if(session('warning'))
            @notification(['type' => 'warning', 'use' => 'close'])
                {!! session('warning') !!}
            @endnotification
        @endif

        @if(session('danger'))
            @notification(['type' => 'danger', 'use' => 'close'])
                {!! session('danger') !!}
            @endnotification
        @endif

        @if(session('error'))
            @notification(['type' => 'danger', 'use' => 'close'])
            {!! session('error') !!}
            @endnotification
        @endif

        @if(session('info'))
            @notification(['type' => 'info', 'use' => 'close'])
            {!! session('info') !!}
            @endnotification
        @endif

        @if(session('primary'))
            @notification(['type' => 'primary', 'use' => 'close'])
            {!! session('primary') !!}
            @endnotification
        @endif

    @endsection
@endif
