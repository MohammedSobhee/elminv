<template>
<div>
    <!-- <pre>{{ categories }}</pre> -->
    <!-- <button @click="totalGrade">Hi</button> -->
    <h4 v-if="showWorksheets">Other Assignments</h4>
    <view-file
        v-if="viewFile"
        :view-file="viewFile"
        :view-id="viewId"
        :submitted="getSubmittedID(viewId) && true"
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
    <div v-if="categories.length > 0">
        <div id="projects" class="accordion custom">
            <template v-for="category in categories">
            <div
                v-if="hasAssignments(category)"
                :key="category.id"
                class="card"
                :class="categorySelected(category.id) && 'collapse-selected'">
                <div
                    class="card-header rounded"
                    @click.self="selectCategory(category.id)">
                    <i class="fas fa-folder"></i>
                    {{ category.name }}
                    <span v-show="showGrades" class="medium text-success float-right">
                        <!-- <span v-if="!hasTotalsData(category)">0/0</span> -->
                        <span>{{ category.totalCustomGrade }}/{{ category.totalCustomPoints }}</span>
                        Points
                        <i v-btooltip="{delay:100,title: `${category.totalCustomGradePercentage}% out of ${category.category_value}%`}" class="fas fa-question-circle"></i>
                        <!-- (<strong>{{ category.totalCustomGradePercentage }}%</strong>) -->
                        <!-- <strong>({{ category.totalCustomGradePercentage }}% out of {{ category.category_value }}%)</strong> -->
                    </span>
                </div>
                <collapse>
                <div
                    v-show="categorySelected(category.id)">
                    <div class="card-body">
                        <ul class="list-group-flush">
                            <!-- <pre>{{ category.assignments }}</pre> -->
                            <li
                                v-for="assignment in category.assignments"
                                :key="assignment.id"
                                class="list-group-item list-group-item-assignment">
                                <div @click.self="defaultAction(assignment)">
                                    <div class="title">
                                        <span
                                            v-btooltip="{title:getTitle(assignment)}"
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
                                            <span @click="defaultAction(assignment)">{{ assignment.label }}
                                                <span
                                                    v-if="assignment.due_date && !(assignment.assignment_submitted_id || assignment.grade)"
                                                    v-btooltip="{title: assignment.dueDateFormatted, delay: 200}"
                                                    :class="assignment.dueDateDiffDays > 2 ? 'text-faded' : 'text-faded-danger'"
                                                    class="ml-2">
                                                    (Due {{ assignment.dueDateDiffHuman }})
                                                </span>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="d-inline-block float-right">
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
                                        <span v-show="showGrades">
                                            <span v-if="assignment.grade !== null" class="status status-graded">{{ assignment.grade }}/{{ assignment.points }} Points (<strong>{{ assignment.gradePercentage }}%</strong>)</span>
                                            <span v-else class="status text-muted">{{ assignment.points }} Points</span>
                                        </span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                </collapse>
            </div>
            </template>
        </div>
    </div>
    <div v-else>
        There are no custom assignments.
    </div>
    <template v-if="selectedCategory.id">
        <template v-for="assignment in selectedCategory.assignments">
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
    </template>
</div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import assignments from '../../mixins/assignmentsMixin';
import gradeIcons from '../../mixins/gradeIcons';

export default {
    name: 'AssignmentsCustom',
    components: {
        ViewFile: () => import(/* webpackChunkName: 'view-file' */ '../common/ViewFile')
    },
    mixins: [gradeIcons, assignments],
    props: {
        assignmentList: {
            type: Array,
            required: true
        },
        assignmentId: {
            type: Number,
            default: 0
        },
        assignmentCategoryId: {
            type: Number,
            default: 0
        },
        showGrades: {
            type: Boolean,
            required: true
        },
        showWorksheets: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            categories: this.assignmentList
        };
    },

    created() {
        this.pageLoadActions();
    },

    methods: {
        //
        // View on page load
        // --------------------------------------------------------------------------
        pageLoadActions() {
            const vm = this;
            // Get cookie and select if it exists
            vm.cookie = vm.getCookie();
            if (vm.assignmentId && vm.assignmentCategoryId) {
                vm.selectCategory(vm.assignmentCategoryId);
                vm.showUpload(vm.assignmentId);
                vm.viewAssignment(vm.selectedAsgmt, 'teacher');
            } else {
                vm.rememberCustom();
            }
        },
        //
        // Get projct from cookie
        // --------------------------------------------------------------------------
        rememberCustom() {
            const vm = this;
            vm.categories.find(obj => obj.id === vm.cookie.category) !== undefined
                && vm.selectCategory(vm.cookie.category);
        },
        //
        // Select category and add its cookie
        // --------------------------------------------------------------------------
        selectCategory(id) {
            const vm = this;
            if (vm.selectedCategory.id === id) {
                vm.selectedCategory = {};
            } else {
                vm.selectedCategory = vm.categories.find(obj => obj.id === id);
            }
            vm.selectedCategory.id &&
                vm.setCookie(vm.cookie, { category: vm.selectedCategory.id });
        },

        //
        // Determine if project selected
        // --------------------------------------------------------------------------
        categorySelected(id) {
            return this.selectedCategory.id && this.selectedCategory.id === id;
        }
    }
};
</script>
<style lang="scss" scoped>
</style>
