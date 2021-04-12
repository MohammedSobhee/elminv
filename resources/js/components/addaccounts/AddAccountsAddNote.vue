<template>
<div class="d-inline-block position-relative">
    <div v-if="sendingEmail" :class="{'add-note-editing': editing}" class="add-note">
        <div class="form-inline small">
            Add note to email?
            <div class="form-check mx-3">
                <input
                    id="add_note_yes"
                    v-model="addNote"
                    value="yes"
                    type="radio"
                    class="form-check-input"
                    @click="action">
                <label class="form-check-label" :class="{'text-danger': error, 'text-success': !addNote}" for="add_note_yes">Yes</label>
            </div>
            <div class="form-check">
                <input
                    id="add_note_no"
                    v-model="addNote"
                    value="no"
                    type="radio"
                    class="form-check-input"
                    @click="action">
                <label class="form-check-label" :class="{'text-danger': error, 'text-success': !addNote}" for="add_note_no">No</label>
            </div>
        </div>
        <popover-dialogue v-if="popover" :str="'yes'" @answer="answer">
            Are you sure? You'll need to provide a description of the password in another way since it won't be included in the email.
        </popover-dialogue>
    </div>
    <button
        v-if="editing"
        type="button"
        class="btn btn-sm btn-light"
        @click="cancel">
        Stop Editing
    </button>
    <button
        v-if="editing"
        type="button"
        class="btn btn-sm btn-primary"
        :disabled="sendingEmail"
        @click="sendingEmail = !sendingEmail">
        Save Edits
    </button>
    <button
        v-if="!sent && !editing"
        type="button"
        :class="`btn btn-sm btn-${buttonStyle}`"
        @click="sendingEmail = !sendingEmail">
        {{ sendingEmail ? 'Cancel Sending ' : 'Send ' }} Their Login Info By Email
    </button>
</div>
</template>
<script>
export default {
    name: 'UploadAccountsAddNote',
    props: {
        users: {
            type: Array,
            required: true
        },
        editing: {
            type: Boolean,
            default: false
        },
        error: {
            type: Boolean,
            default: false
        },
        buttonStyle: {
            type: String,
            default: 'light'
        },
        sent: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            sendingEmail: false,
            addNote: false,
            popoverAnswered: false
        }
    },
    computed: {
        popover() {
            return !this.popoverAnswered && this.addNote === 'no' && this.users.some(obj => obj.password);
        }
    },
    methods: {
        action() {
            const vm = this;
            setTimeout(() => {
                if(vm.popover && !vm.popoverAnswered) return;
                vm.sendingEmail = false;
                vm.$emit('action', vm.addNote)
            }, 100);
        },

        answer(answer) {
            this.popoverAnswered = answer;
            answer === 'yes' && this.action();
        },

        cancel() {
            this.$emit('cancel', true)
        }
    }
}
</script>
