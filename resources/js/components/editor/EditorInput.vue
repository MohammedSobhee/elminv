<template>
<div>
    <editor-wysiwyg
        class="editor-input editor-wysiwyg"
        :message="text"
        :disable-clear="true"
        :save-button-text="buttonText"
        @saved="saveText" />
        <input
            v-if="input"
            type="hidden"
            :name="name"
            :value="input">
        <notification :msg="notice" />
</div>
</template>

<script>
import apiRequest from '../../functions/apiRequest';
import notify from '../../mixins/notify';
export default {
    name: 'EditorInput',

    mixins: [notify],

    props: {
        id: {
            type: Number,
            default: 0
        },
        postUrl: {
            type: String,
            default: ''
        },
        name: {
            type: String,
            default: ''
        },
        text: {
            type: String,
            default: ''
        },
        buttonText: {
            type: Array,
            default: () => ['Edit Notes', 'Notes Edited']
        }
    },

    data() {
        return {
            input: ''
        }
    },

    methods: {
        saveText(emit) {
            const vm = this;
            if(vm.postUrl) {
                const request = {
                    id: this.id,
                    text: emit
                };
                apiRequest(vm.postUrl, request).then(response => {
                    const msg = response.success;
                    vm.notify(msg);
                });
            } else {
                vm.input = emit;
            }
        }
    }
}
</script>
