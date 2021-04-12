/* eslint-disable vue/html-closing-bracket-newline */
<template>
<div>
    <!-- <pre>{{ wsgroups }}</pre> -->
    <div v-if="!loadingComplete" id="loading-circle" class="loading-circle"></div>
    <div
        v-if="loadingComplete"
        id="form-worksheet"
        class="form-worksheet rounded"
        :class="'form-worksheet-'+wid">
        <vue-pure-lightbox
            :images="['/assets/images/examples/worksheet_' + wid + '.png']"
            class="form-worksheet-example" />
        <form
            :id="'worksheet' + wid"
            ref="form"
            class="worksheet"
            :class="'worksheet' + wid"
            @submit.prevent="onSubmit">
            <div
                v-for="(group, gindex) in wsgroups"
                :key="group.group_id"
                class="row flex-row"
                :class="{ 'form-worksheet-group p-3 mx-0 mb-3': group.settings.type }">
                <div v-if="group.settings.type" class="small text-muted mb-3 col-12">
                    Create as many of these as you need to:
                </div>
                <div
                    v-for="(field, findex) in group.fields"
                    :key="field.form_field_id"
                    class="form-field-wrapper"
                    :class="[field.colSize, field.wrapperID, field.groupID]">
                    <!-- Headings and Paragraphs -->
                    <div v-if="field.heading || field.description" class="form-worksheet-text">
                        <h4 v-if="field.heading" v-html="field.heading">
                            {{ field.heading }}
                        </h4>
                        <p
                            v-if="field.description"
                            v-html="field.description">
                            {{ field.description }}
                        </p>
                    </div>

                    <!-- Fields -->
                    <div
                        v-if="!field.heading && !field.description"
                        v-disableNotify
                        :class="[
                            fieldStatus[field.name].includes('Updated') && 'updated',
                            fieldStatus[field.name].includes('Saved') && 'saved'
                        ]"
                        class="form-group">
                        <!-- Label for text, file, textarea, select -->
                        <label
                            v-if="![0, 2, 3].includes(field.type) && field.question !== ''"
                            :for="field.name"
                            v-html="field.question">
                        </label>

                        <!-- Status for text, file, textarea, select -->
                        <span
                            v-if="![0, 2, 3].includes(field.type) && fieldStatus[field.name]"
                            class="form-request-status"
                            :class="[
                                fieldStatusChanged(fieldStatus[field.name]) && 'animated faster bounceInLeft complete',
                                fieldStatus[field.name].includes('File size') && 'text-danger d-block',
                                [undefined, null, ''].includes(field.question) && 'no-label']">
                                <span class="sr-only">{{ fieldStatus[field.name] }}</span>
                        </span>

                        <!-- Text Date -->
                        <input
                            v-if="field.type === 1 && field.question.includes('Date')"
                            v-model="field.answer"
                            v-flatpickr="dateOptions"
                            class="form-control form-flatpickr"
                            placeholder="Select date"
                            :disabled="disabled"
                            :class="{ saved: field.answer }"
                            @change="sendData(field.form_field_id, $event)" />

                        <!-- Text -->
                        <input
                            v-else-if="field.type === 1"
                            v-model="field.answer"
                            class="form-control"
                            :class="{ saved: field.answer }"
                            :disabled="disabled"
                            type="text"
                            @change="sendData(field.form_field_id, $event)" />

                        <!-- Checkbox -->
                        <input
                            v-else-if="field.type === 2"
                            :id="field.name"
                            v-model="field.answer"
                            class="form-check-input"
                            :class="{ saved: field.answer }"
                            :disabled="disabled"
                            type="checkbox"
                            @change="checkboxAnswer(field.form_field_id, $event, field.wrapperID)" />
                        <label
                            v-if="field.type !== 0 && field.type === 2"
                            :for="field.name"
                            class="form-check-label"
                            v-html="field.question">
                        </label>
                        <span
                            v-if="fieldStatus[field.name] && field.type !== 0 && field.type === 2"
                            class="form-request-status"
                            :class="fieldStatusChanged(fieldStatus[field.name])
                                && 'animated faster bounceInLeft complete'">
                        </span>

                        <!-- Radio -->
                        <div v-else-if="field.type === 3">
                            <div v-html="field.question">
                                {{ field.question }}
                            </div>
                            <span
                                v-show="fieldStatus[field.name]"
                                class="form-request-status form-request-status-radio"
                                :class="fieldStatusChanged(fieldStatus[field.name])
                                    && 'animated faster bounceInLeft complete'">
                            </span>
                            <ul>
                                <li v-for="(item, index) in field.value.items" :key="index">
                                    <input
                                        :id="'radio'+index"
                                        class="form-check-input"
                                        :class="{ saved: field.answer === item }"
                                        type="radio"
                                        :disabled="disabled"
                                        :checked="field.answer === item"
                                        :value="item"
                                        @change="radioAnswer(field.form_field_id, $event, field.wrapperID)" />
                                    <label
                                        :for="'radio'+index"
                                        class="form-check-label"
                                        v-html="item">{{ item }}</label>
                                </li>
                            </ul>
                        </div>

                        <!-- Select -->
                        <select
                            v-else-if="field.type === 4"
                            v-model="field.answer"
                            class="custom-select"
                            :disabled="disabled"
                            :class="{ saved: field.answer }"
                            @change="sendData(field.form_field_id, $event)">
                            <option v-if="!field.answer" disabled value="">{{ field.value.items[0] }}</option>
                            <option
                                v-for="(item, index) in field.value.items"
                                :key="index"
                                :value="item"
                                :selected="field.answer === item">
                                {{ item }}
                            </option>
                        </select>

                        <!-- File -->
                        <div
                            v-else-if="field.type === 5"
                            class="form-wrapper-file p-2"
                            :class="{ saved: field.answer }">
                            <div class="flex-row row align-items-center">
                                <div class="col-3 col-md-2">
                                    <span class="btn btn-sm btn-secondary btn-file">
                                        <span>Browse</span>
                                        <input
                                            :id="field.name"
                                            class="form-control-file"
                                            type="file"
                                            accept="image/*"
                                            :disabled="disabled"
                                            @change="fileAnswer(field.form_field_id, gindex, findex, $event)" />
                                    </span>
                                </div>
                                <!-- Upload Progress bar -->
                                <div class="col-9 col-md-10">
                                    <div
                                        v-if="field.type === 5"
                                        v-show="uploadPercentage[field.name] > 0
                                            && uploadPercentage[field.name] !== 100"
                                        class="progress">
                                        <div
                                            class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar"
                                            :aria-valuenow="uploadPercentage[field.name]"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                            :style="'width:' + uploadPercentage[field.name] + '%'"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Image upload preview -->
                            <div
                                v-if="isDataURLregex.test(field.answer)
                                    && !fieldStatus[field.name].includes('lower')"
                                    class="image-preview">
                                <vue-pure-lightbox
                                    :thumbnail="field.answer"
                                    :alternate-text="field.question"
                                    :images="[field.answer]" />
                            </div>
                            <!-- Image saved preview -->
                            <a v-else-if="field.answer">
                                <vue-pure-lightbox
                                    :thumbnail="'/uploads/worksheets/' + field.answer"
                                    :alternate-text="field.question"
                                    :images="['/uploads/worksheets/' + field.answer]" />
                            </a>
                        </div>

                        <!-- Textarea -->
                        <textarea
                            v-else-if="field.type === 6"
                            v-model="field.answer"
                            class="form-control"
                            :class="{ saved: field.answer }"
                            :disabled="disabled"
                            rows="4"
                            cols="40"
                            @change="sendData(field.form_field_id, $event)">
                        </textarea>
                    </div>
                </div>
                <!-- Add Group -->
                <div v-if="group.settings.type && !disabled" class="col-12">
                    <div class="pt-4 text-right">
                        <small
                            v-show="addGroupStatus"
                            class="pr-2 text-success">{{ addGroupStatus }}</small>
                        <button
                            v-if="group.settings.count > 1"
                            class="btn btn-light btn-sm btn-minus"
                            :disabled="disabled"
                            @click="removeGroup(group.group_id)">
                            Remove last
                        </button>
                        <button
                            class="btn btn-light btn-sm btn-add"
                            :disabled="disabled"
                            @click="addGroup(group.group_id)">
                            Add more
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div v-if="!grading" class="mt-4">
                <hr class="border-top mb-2">
                <div class="form-group">
                    <label v-if="message && submittedID" for="message">Would you like to update your comment to {{ teacher_name }}?</label>
                    <label v-else for="message">Are there any comments you'd like to add to {{ teacher_name }}?</label>
                    <textarea
                        id="message"
                        v-model="assignmentMessage"
                        class="form-control"
                        rows="2">
                    </textarea>
                </div>

                <div class="d-inline-block form-submit-button">
                    <span
                        v-if="formStatus.save"
                        class="form-request-status form-request-status-form"
                        :class="(formStatus.save === 'Saved') && 'complete'">
                        {{ formStatus.save }}
                    </span>
                    <button
                        class="btn btn-secondary mr-2"
                        type="button"
                        @click="saveForm">
                        Save Worksheet
                    </button>
                </div>
                <div class="d-inline-block form-submit-button">
                    <span
                        v-if="formStatus.send"
                        class="form-request-status form-request-status-form sent"
                        :class="(formStatus.send === 'Sent') && 'complete'">
                        {{ formStatus.send }}
                    </span>
                    <button
                        v-if="!['teacher', 'assistant-teacher'].includes(user_role) && ((has_locked && locked) || !has_locked)"
                        class="btn btn-primary"
                        type="button"
                        @click="sendToTeacher">
                        {{ sentStatus ? 'Resend' : 'Send' }} to {{ teacher_name || 'Teacher' }}
                    </button>
                </div>
                <!-- <div v-if="disabled" class="d-inline-block"></div> -->
                <div class="d-inline-block ml-2 float-right">
                    <!-- <a :href="team_id > 0 ? '/assignments/team' : '/assignments/'" class="btn btn-light btn-white">Close</a> -->
                    <a href="#" class="btn btn-light btn-white" @click.prevent.stop="close">Close</a>
                </div>
            </div>
        </form>
        <transition
            name="custom-classes-transition"
            enter-active-class="animated bounceInRight"
            leave-active-class="animated slow bounceOutRight">
            <div
                v-show="notify"
                class="alert alert-primary form-notify small"
                role="alert">
                <i class="fas fa-pencil-alt mr-2"></i> {{ notifyMessage }}
            </div>
        </transition>
        <!-- <br><br> <small><pre class="small">{{ wsgroups }}</pre></small> -->
    </div>
</div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import flatpickr from 'flatpickr';
import apiRequest from '../functions/apiRequest';
import { numFields } from '../functions/utils';
import { shouldNotify, compareAnswers } from '../functions/worksheetUtils';

export default {
    name: 'Worksheet',

    components: {
        VuePureLightbox: () => import('vue-pure-lightbox')
    },

    directives: {
        flatpickr: {
            inserted: (el, binding) => {
                el._flatpickr = new flatpickr(el, binding.value);
            },
            unbind: el => el._flatpickr.destroy()
        },
        disableNotify: {
            bind: (el, binding, vnode) => {
                if(vnode.context.disabled) {
                    const title = vnode.context.user_role === 'student'
                        ? 'This activity sheet has been graded and all input, aside from adding or updating a comment below, is currently disabled.'
                        : 'Input disabled';
                    $(el).tooltip({
                        title: title,
                        delay: {
                            show: 1000,
                            hide: 0
                        }
                    });
                }
            }
        }
    },

    props: {
        worksheet: {
            type: Array,
            default: () => []
        },
        wid: {
            type: Number,
            required: true
        },
        pid: {
            type: Number,
            required: true
        },
        has_locked: {
            type: Boolean,
            default: false
        },
        locked: {
            type: Number,
            default: 0
        },
        asid: {
            type: Number,
            default: 0
        },
        message: {
            type: String,
            default: ''
        },
        project_name: {
            type: String,
            default: ''
        },
        team_id: {
            type: Number,
            default: 0
        },
        grading: {
            type: Boolean,
            default: false
        },
        grade: {
            type: Number,
            default: 0
        },
        status: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            getURL: `/worksheets/get/${this.wid}/${this.pid}`,
            disabled: false,
            addGroupStatus: '',
            loadingComplete: false,
            groups: this.worksheet,
            fieldLabel: 'wfield',
            uploadPercentage: {},
            dateOptions: {
                dateFormat: 'F j, Y'
            },
            notify: false,
            notifyMessage: '',
            polling: null,
            fieldStatus: {},
            formStatus: {
                save: '',
                send: ''
            },
            submittedID: this.asid,
            assignmentMessage: this.message,
            sentStatus: this.status,
            // eslint-disable-next-line no-useless-escape
            isDataURLregex: /^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+\=[a-z\-]+)?)?(;base64)?,[a-z0-9\!\$\&\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i
        };
    },

    computed: {
        wsgroups() {
            const vm = this;
            vm.groups = vm.groups.map(g => {
                const group = g;
                group.fields.map(f => {
                    const field = f;
                    // JSON.parse answer values that has json
                    field.value.length && Vue.set(field, 'value', JSON.parse(field.value));

                    // Create field name for selection purpose
                    field.name = vm.fieldLabel + field.form_field_id;

                    // Create field status for user notification
                    if (vm.fieldStatus[field.name] === undefined) {
                        Vue.set(vm.fieldStatus, field.name, '');
                    }

                    // Create field wrapper ID for DOM selection
                    field.wrapperID = `${vm.fieldLabel}wrapper${field.form_field_id}`;

                    // Create col size class to determine question row length
                    field.colSize = `col-md-${field.display_size}`;

                    // Create reactive answer property if none exists
                    if ([undefined, null].includes(field.answer)) {
                        field.type === 3 // radio
                            ? Vue.set(field, 'answer', false)
                            : Vue.set(field, 'answer', null);
                    }
                    // Create image upload progress indicator
                    if (field.type === 5) {
                        Vue.set(vm.uploadPercentage, field.name, 0);
                    }
                    return field
                })
                return group;
            })
            return vm.groups;
        }
    },

    created() {
        const vm = this;
        if(_.isEmpty(vm.groups)) {
            vm.getData();
        } else {
            vm.showForm();
        }

        // Disable if grading or graded
        if(vm.grading || vm.grade) vm.disabled = true;
        //  || vm.status

        // Poll changes if team worksheet and not disabled
        if (vm.team_id && !vm.grading) this.pollData();
    },

    methods: {
        close() {
            if(document.referrer.indexOf('assignments') !== -1)
                window.location.href = '/assignments';
            else
                window.history.back();
        },
        //
        // On Submit - prevent default (page reload)
        // --------------------------------------------------------------------------
        onSubmit(event) {
            event.preventDefault();
        },

        //
        // Send to teacher button - disable on click and show fake notification
        // --------------------------------------------------------------------------
        sendToTeacher() {
            const vm = this,
                request = {
                    type: 1, // 1 is worksheets and 2 is custom
                    type_id: vm.wid,
                    user_id: vm.user_id, //vm.team_id || vm.user_id,
                    project_id: vm.pid,
                    assignment_submitted_id: vm.submittedID,
                    comments: vm.assignmentMessage
                };
            //event.target.setAttribute('disabled', 'disabled');
            vm.formStatus.send = 'Sending...';
            setTimeout(() => {
                vm.sentStatus = 1;
                vm.formStatus.send = 'Sent'
            }, 1000);
            apiRequest('/submit/assignment', request).then(response => {
                if(response.assignment_submitted_id)
                    vm.submittedID = response.assignment_submitted_id
            });
        },

        //
        // Save form - send fake notification
        // --------------------------------------------------------------------------
        saveForm() {
            const vm = this;
            vm.formStatus.save = 'Saving...';
            setTimeout(() => (vm.formStatus.save = 'Saved'), 1000);
        },

        //
        // Field Stats Changed check for v-show
        // --------------------------------------------------------------------------
        fieldStatusChanged(status) {
            return ['Saved', 'Updated'].includes(status);
        },

        //
        // Show Form - and remove loading indicator
        // --------------------------------------------------------------------------
        showForm() {
            this.loadingComplete = true;
        },

        //
        // Process Radio Answer
        // --------------------------------------------------------------------------
        radioAnswer(fieldID, event, fieldWrapperID) {
            $(`.${fieldWrapperID}`)
                .find('input')
                .not(event.target)
                .prop('checked', false);
            this.sendData(fieldID, event);
        },

        //
        // Process Checkbox  Answer
        // --------------------------------------------------------------------------
        checkboxAnswer(fieldID, event, fieldWrapperID) {
            $(`.${fieldWrapperID}`)
                .find('input')
                .not(event.target)
                .removeClass('completed');
            const et = event.target,
                answer = !$(et).is(':checked') ? '' : et.value;
            this.sendData(fieldID, event, answer);
        },

        //
        // Add Group
        // --------------------------------------------------------------------------
        addGroup(gid) {
            const vm = this,
                request = {
                    user_id: vm.user_id,
                    project_id: vm.pid,
                    worksheet_id: vm.wid,
                    group_id: gid
                };

            vm.addGroupStatus = 'Adding...';
            apiRequest('/create/fields', request).then(response => {
                vm.groups = response;
                vm.addGroupStatus = '';
            })
        },

        //
        // Add Group
        // --------------------------------------------------------------------------
        removeGroup(gid) {
            const vm = this,
                request = {
                    user_id: vm.user_id,
                    project_id: vm.pid,
                    worksheet_id: vm.wid,
                    group_id: gid
                };

            vm.addGroupStatus = 'Removing...';
            apiRequest('/remove/fields', request).then(response => {
                vm.groups = response;
                vm.addGroupStatus = '';
            })
        },

        //
        // Process Image
        // --------------------------------------------------------------------------
        fileAnswer(fieldID, gidx, fidx, event) {
            const vm = this,
                input = event.target;
            vm.fieldStatus[fieldID] = 'Saving...'; // Set field status
            if (input.files && input.files[0]) {
                // Use dataURL for image preview
                const reader = new FileReader();
                reader.onload = e => {
                    vm.wsgroups[gidx].fields[fidx].answer = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
                // Build form data request object
                const answer = new FormData();
                answer.append('user_id', this.user_id);
                answer.append('form_field_id', fieldID);
                answer.append('answer', input.files[0]);
                // Send image
                vm.sendData(fieldID, event, answer);
            }
        },

        //
        // Poll Data - check for new data every 10 seconds
        // --------------------------------------------------------------------------
        pollData() {
            this.polling = setInterval(() => {
                apiRequest(this.getURL).then(response => this.processPollData(response));
            }, 10000);
        },

        //
        // Get API Data
        // --------------------------------------------------------------------------
        getData() {
            const vm = this;
            apiRequest(vm.getURL).then(response => {
                vm.groups = response;
                vm.grading && vm.showForm();
            })
        },

        //
        // Process Polled data
        // --------------------------------------------------------------------------
        processPollData(response) {
            const vm = this;
            let dataChanged = 0;

            // Check if notification is needed
            if(!shouldNotify(vm.fieldStatus, response, vm.user_id)) return;

            // If a new row has been added, send notification
            if (numFields(response) > numFields(vm.wsgroups)) {
                dataChanged = vm.sendNotification('A team mate has added a new row.');
            }

            // Check incoming data (answers) for differences and notify
            const diffAnswers = compareAnswers(vm.wsgroups, response);
            if(diffAnswers.length) {
                Object.keys(diffAnswers).forEach(f => {
                    vm.fieldStatus[vm.fieldLabel + diffAnswers[f].form_field_id] = 'Updated';
                    const text = diffAnswers.length > 2 ? 'answers.' : 'an answer.';
                    dataChanged = vm.sendNotification(`A team mate has updated ${text}`);
                });
            }

            // Update groups if data has changed
            dataChanged && (vm.groups = response);

            // Remove notification
            setTimeout(() => (vm.notify = false), 3000);

        },
        sendNotification(message) {
            this.notify = true;
            this.notifyMessage = message;
            return true
        },

        //
        // Store Answer
        // --------------------------------------------------------------------------
        sendData(fieldID, event, answer) {
            const vm = this,
                et = event.target,
                fieldname = vm.fieldLabel + fieldID;

            let request = {};

            // Check if image upload and assign its object
            if (typeof answer === 'object') {
                request = answer;
            // Else build request object
            } else {
                const selectedAnswer = answer !== undefined ? answer : et.value;
                request = {
                    user_id: this.user_id,
                    form_field_id: fieldID,
                    answer: selectedAnswer
                };
            }

            vm.fieldStatus[fieldname] = 'Saving...';

            apiRequest(`/worksheets/store/${this.wid}/${this.pid}`, request, {
                onUploadProgress: event => {
                    vm.uploadPercentage[vm.fieldLabel + fieldID] = parseInt(
                        Math.round((event.loaded * 100) / event.total), 10);
                }
            })
                .then(() => {
                    vm.fieldStatus[fieldname] = 'Saved';
                })
                .catch(error => {
                    if (error.response.status === 413)
                        vm.fieldStatus[fieldname] = 'File size needs to be 10 MB or lower.';
                    else
                        vm.fieldStatus[fieldname] = `Something went wrong. Please contact
                            <a href="/support">support.</a>`;
                });
        },
        beforeDestroy() {
            clearInterval(this.polling);
        }
    }
};
</script>

<style lang="scss" scoped>
</style>
