<!-- Modal -->
<div class="modal fade" id="{{ $name }}Modal" tabindex="-1" role="dialog" aria-labelledby="{{ $name }}ModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $name }}ModalLabel">{!! $title !!}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            @isset($form_action)
            <form action="{{ $form_action }}" method="post">
            {{ csrf_field() }}
            @endisset

            @isset($project_id)
                <input type="hidden" name="project_id" value="{{ $project_id }}">
            @endisset


            <div class="modal-body">
                {{ $slot }}
            </div>

            @isset($form_action)
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                <!-- <button type="submit" class="btn btn-primary" data-dismiss="modal">Save changes</button> -->
                <input type="submit" class="btn btn-sm btn-primary" value="{{ isset($form_button) ? $form_button : 'Save' }}">
            </div>
            </form>
            @endisset

        </div>
    </div>
</div>
