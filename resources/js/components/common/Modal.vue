<template>
    <div
        :id="name+id+'Modal'"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        :aria-labelledby="name+id+'ModalLabel'"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form @submit.prevent="modalHandler(id)">
                <div class="modal-header">
                    <h5 :id="name+id+'ModalLabel'" class="modal-title">
                        <slot name="header"></slot>
                    </h5>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <slot name="body" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                    <button v-if="actionLabel" type="submit" class="btn btn-sm btn-primary">{{ actionLabel }}</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Modal',
    props: {
        id: {
            type: Number,
            required: true
        },
        name: {
            type: String,
            required: true
        },
        actionLabel: {
            type: String,
            default: ''
        }
    },
    methods: {
        modalHandler() {
            this.$emit('action', { id: this.id, name: this.name });
        }
    }
};
</script>
