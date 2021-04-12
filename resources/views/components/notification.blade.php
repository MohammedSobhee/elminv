    <div class="alert alert-{{$type}} alert-dismissible mb-4">
        {{ $slot }}
        @if(isset($use) && $use == 'close')
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        @endif
    </div>
