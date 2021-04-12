<template>
    <div @click="close">
        <div class="page-overlay">
        <div class="page-overlay-wrapper">
        <div class="page-overlay-content">
            <template v-if="assignment.disabled">
                <div class="alert alert-warning" role="alert">
                    This content has been disabled for demo accounts.
                </div>
            </template>
            <template v-else>
            <div class="page-overlay-btns" @click.stop="">
                <template v-if="classinfo.asgmtType !== 'Activity' && !(type === 'viewing' && project_id > 0)">
                    <a
                        v-if="isDownloadable(assignment)"
                        class="btn btn-sm btn-primary pl-1"
                        :download="getAsgmtDownloadTitle(assignment)"
                        :href="'/uploads/assignments/' + getAsgmtLocation(assignment)"
                        target="_blank">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                    <a
                        v-if="assignment.type === 2"
                        class="btn btn-sm btn-primary pl-1"
                        :href="assignment.teacher_file_location"
                        target="_blank">
                        <i class="fas fas fa-external-link-alt mr-1"></i> Open Link (new tab)
                    </a>
                </template>
                <button
                    type="button"
                    class="btn btn-sm btn-primary pl-1"
                    @click="close">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
            <!-- Grade Assignment Form -->
            <div v-if="assignment.locked || !project_id" class="gb-assignment-form row-animate" @click.prevent.stop="">
                <div v-if="type !== 'viewing'" class="gb-assignment-form-header">
                    <h3 v-if="classinfo.userType == 'team'">
                        Team: <span>{{ user.team_name }}</span>
                    </h3>
                    <h3 v-else>
                        Student: <span>{{ user.fullname }}</span>
                    </h3>
                </div>
                <div class="gb-assignment-form-body">
                    <!-- Assignment Name -->
                    <div v-if="type === 'pending' || type === 'viewing'" class="text-muted small text-center py-2">
                        <strong v-if="assignment.project_name">{{ assignment.project_name }}:</strong>
                        {{ assignment.assignment_name }}
                    </div>
                    <div v-else class="text-muted small text-center py-2">
                        <strong v-if="assignment.project_name">{{ assignment.project_name }}:</strong>
                        {{ classinfo.asgmtType === 'Activity' ? assignment.title : assignment.label }}
                    </div>

                    <!-- Comments -->
                    <div v-if="assignment.comments" class="gb-assignment-student-message small">
                        <hr class="my-1">
                        <strong>{{ user.first_name }}'s Comment:</strong> {{ assignment.comments }}
                        <hr class="my-1 mb-3">
                    </div>

                    <!-- Points -->
                    <div v-if="assignment.status === 1 && type !== 'viewing'">
                        <div class="input-group gb-assignment-input">
                            <input
                                v-model="grade"
                                type="text"
                                class="form-control"
                                :placeholder="0"
                                aria-label="Points"
                                aria-describedby="points"
                                @change="submitGrade">
                            <div class="input-group-append">
                                <span id="points" class="input-group-text text-muted">Max: {{ assignment.points || 0 }} Points</span>
                            </div>
                        </div>
                        <!--- Indicate team grading -->
                        <div v-if="assignment.team_id" class="mt-1">
                            <p class="text-center text-success small">This will apply to all members of {{ assignment.team_name }}.</p>
                        </div>
                    </div>

                    <!-- Worksheet active notice -->
                    <div v-else-if="assignment.has_answers && type !== 'viewing'" class="pt-2">
                        <p class="text-center text-success">This assignment is actively being worked on.</p>
                    </div>

                    <!-- Custom not submitted notice -->
                    <div v-else-if="!assignment.student_file_location && type !== 'viewing'" class="pt-2">
                        <p class="text-center text-success">This has not been submitted yet.</p>
                    </div>

                    <!-- Grade actions -->
                    <div v-if="type !== 'viewing'" class="text-right text-muted small mt-3 gb-assignment-buttons">
                        <u @click="addMessage = !addMessage">{{ addMessage ? 'Close' : (message ? 'Edit' : 'Add') }} Message</u>
                        <button
                            v-if="assignment.status"
                            v-tooltip:grade="'Press enter to save or click save below.'"
                            class="ml-2 btn btn-s btn-primary"
                            :class="[saved ? 'btn-secondary font-weight-bold' : 'btn-primary']">
                            {{ saved ? 'Saved' : 'Save' }}
                        </button>
                    </div>

                    <!-- Add Message Form -->
                     <collapse v-if="classinfo.userType">
                     <div v-show="addMessage">
                        <div class="form-group">
                            <hr class="mb-0 mt-1">
                            <label for="message" class="small text-muted">
                                Message to {{ (classinfo.userType == 'team' || assignment.team_id) ? 'team' : 'student' }}:
                                <!-- <span v-if="message" @click="clearMessage">(<u>Clear</u>)</span> -->
                            </label>
                            <editor-wysiwyg
                                class="editor-wysiwyg gb-assignment-message"
                                :message="message"
                                :simple="true"
                                :save-button-text="['Save Message', 'Message saved']"
                                @saved="saveMessage" />
                        </div>
                    </div>
                    </collapse>
                </div>
            </div>
            <div v-else-if="!assignment.locked && project_id && type !== 'viewing'" class="gb-assignment-form row-animate text-center text-success p-2 bg-white" @click.prevent.stop="">
                Viewing a non-primary project activity sheet.
            </div>
            <!-- Assignment Type Worksheet / Activity -->
            <div v-if="classinfo.asgmtType === 'Activity' || (type === 'viewing' && project_id > 0)" @click.prevent.stop="">
                <worksheet
                    :pid="project_id"
                    :wid="assignment.category_id"
                    :grading="true"
                    :team_id="classinfo.userType == 'team' ? user.id : 0">
                </worksheet>
            </div>
            <!-- Assignment type link -->
            <div v-else-if="assignment.type === 2">
                <div class="text-center">
                    <img
                        v-if="assignment.student_file_location && fileIsImage(getAsgmtLocation(assignment))"
                        :src="'/uploads/assignments/' + assignment.student_file_location"
                        class="rounded d-block w-auto h-auto mx-auto">
                    <div
                        v-if="assignment.student_file_location && assignment.student_file_location.indexOf('pdf') !== -1"
                        id="pdf"
                        v-pdfViewer:url="'/uploads/assignments/' + getAsgmtLocation(assignment)"
                        class="gb-assignment-pdfviewer">
                    </div>
                    <iframe
                        v-if="assignment.student_file_location && assignment.student_file_location.indexOf('doc') !== -1"
                        :src="'https://view.officeapps.live.com/op/embed.aspx?src=https://edu2.inventionlandinstitute.com/uploads/assignments/' + getAsgmtLocation(assignment)"
                        class="gb-assignment-docviewer"
                        width="100%"
                        height="800px"
                        frameborder="0">
                        This is an embedded <a target="_blank" href="http://office.com">Microsoft Office</a> document, powered by <a target="_blank" href="http://office.com/webapps">Office Online</a>.
                    </iframe>
                    <div
                        v-if="!assignment.student_file_location && assignment.file_screenshot"
                        class="text-center"
                        @click.stop="">
                        <div class="gb-assignment-screenshot">
                            <a v-btooltip="{title:'Opens in new tab'}" :href="assignment.teacher_file_location" target="_blank"><img :src="'/uploads/assignments/' + assignment.file_screenshot" /></a>
                            <div v-if="['teacher', 'assistant-teacher'].includes(user_role)" class="text-right">
                                <a v-btooltip="{title:`Some websites don't allow screenshots to be generated of them automatically. Submit a request for a manual replacement.`}" :href="'/support/screenshot/'+assignment.id" class="small text-gray">Image malformed?</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Assignment file pdf -->
            <div v-else-if="getAsgmtLocation(assignment).toLowerCase().indexOf('pdf') !== -1">
                <div id="pdf" v-pdfViewer:url="'/uploads/assignments/' + getAsgmtLocation(assignment)" class="gb-assignment-pdfviewer"></div>
            </div>
            <!-- Assignment file doc -->
            <div v-else-if="getAsgmtLocation(assignment).toLowerCase().indexOf('doc') !== -1">
                <iframe
                    :src="'https://view.officeapps.live.com/op/embed.aspx?src=https://edu2.inventionlandinstitute.com/uploads/assignments/' + getAsgmtLocation(assignment)"
                    class="gb-assignment-docviewer"
                    width="100%"
                    height="800px"
                    frameborder="0">
                    This is an embedded <a target="_blank" href="http://office.com">Microsoft Office</a> document, powered by <a target="_blank" href="http://office.com/webapps">Office Online</a>.
                </iframe>
            </div>
            <!-- Assignment file image -->
            <div v-else-if="fileIsImage(getAsgmtLocation(assignment))" class="position-relative">
                <div class="text-center">
                    <img :src="'/uploads/assignments/' + getAsgmtLocation(assignment)" class="rounded d-block w-auto h-auto mx-auto">
                </div>
            </div>
        </template>
        </div>
        </div>
        </div>
    </div>
</template>

<script>
import { fileNameIsImage } from '../../functions/utils';
import { pdfViewer } from '../../directives/pdfViewer';

export default {
    name: 'GradeAssignment',

    components: {
        EditorWysiwyg: () => import(/* webpackChunkName:"editor-wysiwyg" */ '../editor/EditorWysiwyg')
    },

    directives: {
        pdfViewer
    },

    props: {
        assignment: {
            type: Object,
            required: true
        },
        user: {
            type: Object,
            default: () => {
                return {
                    fullname: ''
                }
            }
        },
        classinfo: {
            type: Object,
            default: () => {
                return {
                    userType: 0,
                    asgmtType: 0
                }
            }
        },
        project_id: {
            type: Number,
            default: 0
        },
        type: {
            type: String,
            default: 'gradebook'
        }
    },

    data() {
        return {
            addMessage: this.assignment.message || false,
            message: this.assignment.message,
            grade: this.assignment.grade,
            update: false,
            saved: false,
            pdfOpenParams: {
                pagemode: 'thumbs',
                navpanes: 1,
                toolbar: 1,
                statusbar: 0,
                view: 'FitV'
            }
        };
    },

    computed: {
        keepOpen() {
            // return false;
            return true;
        }
    },

    watch: {
        message(oldVal, newVal) {
            if(oldVal !== newVal) {
                this.saved = false;
            }
        }
    },


    created() {
        const vm = this;
        vm.cookie = vm.getCookie();
        vm.update = this.assignment.grade_id || false;
        vm.setupTooltipCookie(vm.cookie, { grade: 0 });
    },

    beforeDestroy() {
        $('.tooltip').tooltip('hide');
    },

    methods: {
        getAsgmtLocation(assignment) {
            return assignment.student_file_location || assignment.teacher_file_location
        },
        getAsgmtDownloadTitle(assignment) {
            if(this.type === 'viewing') {
                return assignment.assignment_name;
            } else {
                return this.user.fullname + ' - ' + (
                    this.classinfo.asgmtType === 'Activity' ? assignment.title : assignment.label
                );
            }
        },

        isDownloadable(assignment) {
            return (this.getAsgmtLocation(assignment).toLowerCase().indexOf('pdf') !== -1)
                || (this.getAsgmtLocation(assignment).toLowerCase().indexOf('doc') !== -1)
                || (this.fileIsImage(this.getAsgmtLocation(assignment)))
        },

        fileIsImage(fileName) {
            return fileNameIsImage(fileName);
        },
        close() {
            this.$emit('close', true);
        },
        saveMessage(message) {
            this.message = message;
            this.submitGrade();
        },
        submitGrade() {
            const vm = this;
            setTimeout(() => {
                vm.$emit('submit-grade', {
                    continue: vm.keepOpen,
                    userID: vm.user.id,
                    classID: vm.classinfo.id,
                    asgmtType: vm.classinfo.asgmtType,
                    update: vm.update,
                    grade: vm.grade,
                    message: vm.message
                });
                vm.saved = true;
                vm.update = true;
            }, 500);
        }
    }
};
</script>
