<template>
    <div
        :id="name+id+'Modal'"
        v-stopFileDrag
        class="modal fade"
        tabindex="-1"
        role="dialog"
        :aria-labelledby="name+id+'ModalLabel'"
        aria-hidden="true">
        <div
            ref="modalDialog"
            class="modal-dialog modal-dialog-centered"
            role="document">
            <div
                v-show="dragging"
                ref="fileDrop"
                class="form-wrapper-file-dragging">
                <span>Drop files</span>
            </div>
            <div
                class="modal-content"
                @dragenter="openDragDrop"
                @dragover.prevent
                @drop.prevent="getFiles">
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
                    <button class="btn btn-sm btn-primary" @click="modalHandler">{{ actionLabel }}</button>
                    <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import stopFileDrag from '../../directives/stopFileDrag';
export default {
    name: 'ModalUpload',
    directives: {
        stopFileDrag
    },
    props: {
        id: {
            type: Number,
            required: true
        },
        type: {
            type: Number,
            required: true

        },
        name: {
            type: String,
            required: true
        },
        actionLabel: {
            type: String,
            default: 'Submit'
        },
        close: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            dragging: false,
            closeStatus: this.close,
            files: null
        }
    },

    methods: {
        openDragDrop() {
            const vm = this;
            vm.dragging = true;
            // Set height of file drop overlay to div wrapping the file list
            vm.$refs.fileDrop.style.height = vm.$refs.modalDialog.offsetHeight + 'px';
            vm.$refs.fileDrop.style.width = vm.$refs.modalDialog.offsetWidth + 'px';
        },

        getFiles(event) {
            this.dragging = false;
            this.$emit('files', event);
        },

        modalHandler() {
            this.$emit('submit', {
                id: this.id
            });
        }
    }
};
</script>
