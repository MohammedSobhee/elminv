<template>
    <div
        :class="{confirm: confirm}"
        class="popover-dialogue">
        <div class="popover-dialogue-header">
            <i class="fas" :class="[confirm ? 'fa-question-circle' : 'fa-exclamation-circle']"></i><slot>Clear submission and grade?</slot>
        </div>
        <div v-if="confirm" class="popover-dialogue-body">
            <span class="clear-yes" @click.prevent.stop="popoverConfirm(yes)">Yes</span>
            <span class="clear-cancel" @click.prevent.stop="popoverConfirm('cancel')">Cancel</span>
        </div>
    </div>
</template>
<script>
export default {
    name: 'PopoverDialogue',
    props: {
        id: {
            type: Number,
            default: 0
        },
        str: {
            type: String,
            default: ''
        },
        confirm: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            yes: this.id || this.str
        }
    },
    methods: {
        popoverConfirm(answer) {
            this.$emit('answer', answer);
        }
    }
};
</script>
