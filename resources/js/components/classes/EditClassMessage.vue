<template>
    <div>
        <h4 v-if="class_id">
            Set Dashboard Message for this class:
        </h4>
        <h4 v-else>
            Set Dashboard Message for all classes:
        </h4>
        <editor-wysiwyg
            class="editor-wysiwyg"
            :message="message"
            :notice="editorNotice"
            @saved="saveMessage" />
        <div v-if="synced" class="text-success">
            <p>Currently all classes have the same dashboard message.</p>
        </div>
  </div>
</template>

<script>
import apiRequest from '../../functions/apiRequest';
export default {
    name: 'EditClassMessage',

    props: {
        class_id: {
            type: Number,
            default: 0
        },
        class_message: {
            type: String,
            default: ''
        },
        synced: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            editorNotice: false,
            message: this.class_message
        };
    },

    created() {
        if(!this.class_message) {
            this.message = !this.class_id
                ? 'Message to display for all classes. This will update the dashboard message of each class.'
                : 'Message to display for all students of this class.'
        }
    },

    methods: {
        saveMessage(message) {
            const vm = this;
            const postURL = !vm.class_id ? '/update/classmessages' : '/update/class';
            const request = {
                class_id: vm.class_id,
                message: message
            };
            vm.editorNotice = false;
            message && apiRequest(postURL, request).then(() => {
                vm.editorNotice = true;
            })
        }
    }
};
</script>
