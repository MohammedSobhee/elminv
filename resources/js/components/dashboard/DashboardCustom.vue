<template>
<div>
    <strong class="text-primary">Assignments</strong>
    <view-file
        v-if="viewFile"
        :view-file="viewFile"
        :view-id="viewId"
        @close="closeAssignment"
        @upload="showUpload" />
    <div v-if="responseMessage" class="notification alert alert-success alert-dismissible">
        <span v-html="responseMessage"></span>
        <button
            type="button"
            class="close"
            data-dismiss="alert"
            aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div v-if="assignments.length !== 0" class="assignments custom">
        <ul class="list-group-flush">
            <template v-for="assignment in assignments">
                <!-- Hide Request by Nathan 9/9/2020 -->
                <li
                    v-if="!assignment.hide"
                    :key="assignment.id"
                    class="list-group-item list-group-item-assignment">
                    <div @click.self="defaultAction(assignment)">
                        <span
                            v-btooltip
                            :title="getTitle(assignment)"
                            :class="getIconBackground(assignment)"
                            class="card-icon align-self-start">
                            <i :class="getIcon(assignment)"></i>
                        </span>
                        <a
                            v-if="assignment.type === 1"
                            v-btooltip="{title: 'Download assignment'}"
                            :href="'/uploads/assignments/' + assignment.teacher_file_location"
                            :download="sanitizeFileName(assignment.label)">
                            <span class="card-icon card-icon-action"><i class="fas fa-download"></i></span>
                        </a>
                        <a
                            v-if="assignment.type === 2"
                            v-btooltip="{title: 'View assignment'}"
                            :href="assignment.teacher_file_location"
                            target="_blank">
                            <span class="card-icon card-icon-action"><i class="fas fas fa-external-link-alt"></i></span>
                        </a>
                        <a
                            v-btooltip="{title: 'Submit assignment'}"
                            @click="showUpload(assignment.id)">
                            <span class="card-icon card-icon-action"><i class="fas fa-arrow-up"></i></span>
                        </a>
                        <span class="list-group-item-label">
                            <span @click.self="defaultAction(assignment)">{{ assignment.label }}
                                <span
                                    v-if="assignment.due_date && !assignment.assignment_submitted_date"
                                    v-btooltip="{title: assignment.dueDateFormatted, delay: 200}"
                                    :class="assignment.dueDateDiffDays > 2 ? 'text-faded' : 'text-faded-danger'"
                                    class="ml-2">
                                    (Due {{ assignment.dueDateDiffHuman }})
                                </span>
                            </span>
                        </span>
                    </div>
                    <div class="right-actions">
                        <span v-if="assignment.grade !== null" class="status status-graded"><strong>{{ assignment.grade }}</strong> / {{ assignment.points }} Points</span>
                        <span
                            v-if="assignment.message"
                            v-bpopover="assignment.message"
                            class="icon-message"
                            data-popover-title="Message"
                            tabindex="0">
                            <i class="far fa-comment-alt"></i>
                        </span>
                        <a
                            v-if="assignment.student_file_location"
                            v-btooltip="{title: 'View submitted assignment'}"
                            tabindex="0"
                            class="icon-message"
                            @click="viewAssignment(assignment, 'student')">
                            <i class="far fa-eye"></i>
                        </a>
                    </div>
                </li>
            </template>
        </ul>
    </div>
    <template v-for="assignment in assignments">
        <modal-upload
            v-show="selectedAsgmt.id"
            :id="assignment.id"
            :key="assignment.id"
            :type="assignment.type"
            name="upload"
            action-label="Complete Assignment"
            @files="getFiles"
            @submit="submit">
            <template v-slot:header>
                <span class="text-primary">{{ `${assignment.assignment_submitted_id ? 'Resubmit' : 'Submit'} Assignment` }}</span>
            </template>
            <template v-slot:body>
                <strong>Assignment:</strong>
                <template v-if="assignment.type === 2">
                    <a :href="assignment.teacher_file_location" target="_blank">{{ selectedAsgmt.label }}</a>
                </template>
                <template v-else>
                    {{ selectedAsgmt.label }}
                </template>
                <br><br>
                <div class="mb-3">
                    <textarea
                        v-model="assignment.comments"
                        class="form-control form-control-sm"
                        placeholder="Optionally add a comment to the teacher">
                    </textarea>
                </div>
                <div v-show="uploadPercentage > 0 && uploadPercentage !== 100" class="progress mb-1">
                    <div
                        class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar"
                        :aria-valuenow="uploadPercentage"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        :style="'width:' + uploadPercentage + '%'"></div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="btn btn-sm btn-secondary btn-file">
                            <span>Browse Files</span>
                            <input
                                class="form-control-file"
                                type="file"
                                accept="application/msword, application/pdf, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/rtf, image/png, image/jpeg, image/gif"
                                @change="getFiles">
                        </span>
                    </div>
                    <div class="col-md-8 mb-3" style="line-height:1">
                        <span v-if="notice.message" :class="`small text-${notice.type} m-0`">{{ notice.message }}</span>
                        <span v-else-if="assignment.type === 2" class="small text-muted m-0">Optionally browse or drag and drop a file into this window.</span>
                        <span v-else class="small text-muted m-0">Browse files or drag and drop them into this window.</span>
                    </div>
                </div>
            </template>
        </modal-upload>
    </template>
</div>
</template>

<script>
import assignments from '../../mixins/assignmentsMixin';
import gradeIcons from '../../mixins/gradeIcons';
export default {
    name: 'DashboardCustom',
    components: {
        ViewFile: () => import(/* webpackChunkName: 'view-file' */ '../common/ViewFile')
    },
    mixins: [gradeIcons, assignments],
    props: {
        assignmentList: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            assignments: this.assignmentList
        };
    }
};
</script>
