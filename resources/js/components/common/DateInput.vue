<template>
    <div class="date-input">
    <input
        :id="id"
        ref="date"
        v-model="input"
        v-flatpickr="dateOptions"
        :name="name"
        :placeholder="placeholder"
        :disabled="disabled"
        class="form-control form-flatpickr"
        :class="css_class"
        @focus="focused"
        @change="changed" />
        <i v-show="input" :id="'clear-'+id" class="far fa-trash-alt input-icon delete-action"></i>
    </div>
</template>

<script>
import flatpickr from 'flatpickr';
import { formatDateTime } from '../../functions/utils';
export default {
    name: 'DateInput',

    directives: {
        flatpickr: {
            inserted: (el, binding) => {
                el._flatpickr = new flatpickr(el, binding.value);
                document.getElementById('clear-'+el.id).addEventListener('click', () => el._flatpickr.clear());
            },
            unbind: el => el._flatpickr.destroy()
        }
    },

    props: {
        name: {
            type: String,
            default: 'date'
        },
        id: {
            type: String,
            default: ''
        },
        value: {
            type: String,
            default: ''
        },
        focus_placeholder: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: 'Select date'
        },
        css_class: {
            type: String,
            default: ''
        },
        disabled: {
            type: Boolean,
            default: false
        },
        allow_input: {
            type: Boolean,
            default: true
        },
        emit: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            input: this.value ? formatDateTime(this.value, 'long') : '',
            dateOptions: {
                // altInput: true,
                // altFormat: 'F j, Y',
                // dateFormat: 'm/d/Y',
                dateFormat: 'F j, Y',
                allowInput: this.allow_input
            }
        }
    },
    watch: {
        value(nVal) {
            this.input = nVal;
        }
    },
    methods: {
        changed(event) {
            this.emit && this.$emit('changed', event);
        },
        focused(event) {
            if(this.focus_placeholder) {
                this.$refs.date.setAttribute('placeholder', this.focus_placeholder);
            } else {
                this.$emit('focused', event.target.id);
            }
        }
    }
};
</script>
