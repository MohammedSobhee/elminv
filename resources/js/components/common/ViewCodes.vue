<template>
<div>
    <div
        v-if="class_type !== '1'"
        class="row mt-3">
        <!-- School -->
        <div v-if="school_code" class="col-lg-6">
            <div class="input-border rounded px-2 py-1 position-relative">
                <strong class="mr-2">School Code:</strong>
                <input
                    type="text"
                    :value="school_code"
                    class="form-input-copy" />
                <button
                    v-clipboard
                    type="button"
                    class="btn-copy"
                    title="Copy to clipboard">
                    <i class="far fa-clipboard"><span class="sr-only">Copy</span></i>
                </button>
            </div>
            <div class="input-border rounded px-2 py-1 mt-3 position-relative">
                <input
                    type="text"
                    :value="`http://${host}/activate/school`"
                    class="small form-input-copy copy-link w-100" />
                <button
                    v-clipboard
                    type="button"
                    class="btn-copy"
                    data-container="body"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Copy to clipboard">
                    <i class="far fa-clipboard"><span class="sr-only">Copy</span></i>
                </button>
            </div>
        </div>
        <!-- Teacher -->
        <div v-if="teacher_code" class="col-lg-6">
            <div class="input-border rounded px-2 py-1 position-relative">
                <strong class="mr-2">Teacher Code:</strong>
                <input
                    type="text"
                    :value="teacher_code"
                    class="form-input-copy" />
                <button
                    v-clipboard
                    type="button"
                    class="btn-copy"
                    title="Copy to clipboard">
                    <i class="far fa-clipboard"><span class="sr-only">Copy</span></i>
                </button>
            </div>
            <div class="input-border rounded px-2 py-1 mt-3 position-relative">
                <input
                    type="text"
                    :value="`http://${host}/activate/teacher`"
                    class="small form-input-copy copy-link w-100" />
                <button
                    v-clipboard
                    type="button"
                    class="btn-copy"
                    data-container="body"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Copy to clipboard">
                    <i class="far fa-clipboard"><span class="sr-only">Copy</span></i>
                </button>
            </div>
        </div>
        <!-- Student -->
        <div v-if="student_code" class="col-lg-6">
            <div class="input-border rounded px-2 py-1 position-relative">
                <strong class="mr-2">Student Code:</strong>
                <input
                    type="text"
                    :value="sCode"
                    class="form-input-copy"
                    :class="{ 'text-success': refreshsCode }" />
                <button
                    v-clipboard
                    type="button"
                    class="btn-copy"
                    title="Copy to clipboard">
                    <i class="far fa-clipboard"><span class="sr-only">Copy</span></i>
                </button>
                <i
                    v-if="class_id"
                    v-btooltip
                    title="Refresh Codes"
                    class="fas fa-redo icon-refresh"
                    @click="refreshCodes(1)">
                    <span class="sr-only">Refresh Codes</span>
                </i>
            </div>
            <div class="input-border rounded px-2 py-1 mt-3 position-relative">
                <input
                    type="text"
                    :value="`http://${host}/activate/student`"
                    class="small form-input-copy copy-link w-100" />
                <button
                    v-clipboard
                    type="button"
                    class="btn-copy"
                    data-container="body"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Copy to clipboard">
                    <i class="far fa-clipboard"><span class="sr-only">Copy</span></i>
                </button>
            </div>
        </div>
        <!-- Assistant Teacher -->
        <div v-if="assistant_code" class="col-lg-6 mt-3 mt-lg-0">
            <div class="border rounded px-2 py-1 position-relative">
                <strong class="mr-2">Asst. Teacher Code:</strong>
                <input
                    type="text"
                    :value="aCode"
                    class="form-input-copy"
                    :class="{ 'text-success': refreshaCode }" />
                <button
                    v-clipboard
                    type="button"
                    class="btn-copy"
                    data-container="body"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Copy to clipboard">
                    <i class="far fa-clipboard"><span class="sr-only">Copy</span></i>
                </button>
                <i
                    v-if="class_id"
                    v-btooltip
                    title="Refresh Codes"
                    class="fas fa-redo icon-refresh"
                    @click="refreshCodes(2)">
                    <span class="sr-only">Refresh Codes</span>
                </i>
            </div>
            <div class="input-border rounded px-2 py-1 mt-3 position-relative">
                <input
                    type="text"
                    :value="`http://${host}/activate/assistant`"
                    class="small form-input-copy copy-link w-100" />
                <button
                    v-clipboard
                    type="button"
                    class="btn-copy"
                    data-container="body"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Copy to clipboard">
                    <i class="far fa-clipboard"><span class="sr-only">Copy</span></i>
                </button>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import apiRequest from '../../functions/apiRequest';

export default {
    name: 'ViewCodes',
    directives: {
        clipboard: {
            inserted: el => {
                $(el).tooltip({
                    container: 'body'
                });
                const copy = () => {
                    el.setAttribute('data-original-title', 'Copied');
                    $(el).tooltip('show');
                    el.previousElementSibling.select();
                    document.execCommand('copy');
                }
                el.addEventListener('click', () => copy());
                el.$destroy = () => el.removeEventListener('click', copy);
            },
            unbind: el => el.$destroy()
        }
    },
    props: {
        host: {
            type: String,
            required: true
        },
        class_id: {
            type: Number,
            default: 0
        },
        class_type: {
            type: Number,
            default: 0
        },
        school_code: {
            type: String,
            default: ''
        },
        teacher_code: {
            type: String,
            default: ''
        },
        student_code: {
            type: String,
            default: ''
        },
        assistant_code: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            aCode: this.assistant_code,
            sCode: this.student_code,
            refreshaCode: false,
            refreshsCode: false
        }
    },

    watch: {
        aCode(oldVal, newVal) {
            if(oldVal !== newVal) {
                this.refreshaCode = true;
                setTimeout(() => (this.refreshaCode = false), 5000);
            }
        },
        sCode(oldVal, newVal) {
            if(oldVal !== newVal) {
                this.refreshsCode = true;
                setTimeout(() => (this.refreshsCode = false), 5000);
            }
        }
    },

    methods: {
        refreshCodes(type) {
            const request = {
                type: type,
                class_id: this.class_id
            };

            apiRequest('/update/codes', request).then(res => {
                type === 1 && (this.sCode = res.success);
                type === 2 && (this.aCode = res.success);
            })
        }
    }
};
</script>
