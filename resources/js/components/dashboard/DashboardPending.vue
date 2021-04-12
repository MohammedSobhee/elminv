<template>
<div class="dashboard-assignments mt-5">
    <div class="row">
        <div class="col-md-6">
            <h4 class="mt-0 mb-2">
                <i class="far fa-bell ml-1"></i> Task List: Submitted Assignments
            </h4>
        </div>
        <notification :msg="notice" class="col-md-6" />
    </div>
    <hr class="mt-0 mb-2">


    <div v-if="assignments.length" class="assignments pending">
        <grade-item
            type="pending"
            :assignments="assignments"
            name="assignment_name"
            @grade="gradeAssignment"
            @clear="clearAssignment">
        </grade-item>
    </div>

    <grade-assignment
        v-if="gradingAssignment"
        v-closeEsc
        type="pending"
        :assignment="gradingAssignment"
        :user="gradingUser"
        :classinfo="gradingClass"
        :project_id="gradingProjectID"
        class="gb-assignment"
        @close="closeAssignment"
        @submit-grade="submitGrade">
    </grade-assignment>
</div>
</template>

<script>
import { changeLayout } from '../../functions/utils';
import notify from '../../mixins/notify';
import apiRequest from '../../functions/apiRequest';
import closeEsc from '../../directives/closeEsc';

export default {
    name: 'DashboardPending',
    components: {
        GradeItem: () => import(/* webpackChunkName: 'grade-item' */ '../gradebook/GradeItem.vue'),
        GradeAssignment: () => import(/* webpackChunkName: 'grade-assignment' */ '../gradebook/GradeAssignment')
    },
    directives: {
        closeEsc
    },
    mixins: [notify],
    props: {
        assignmentList: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            assignments: this.assignmentList,
            gradingAssignment: null,
            gradingClass: null,
            gradingUser: null,
            gradingProjectID: null
        };
    },

    methods: {
        getUsersName(assignment, type = 'full') {
            if(assignment.groups) {
                return assignment.team_name
            } else {
                return type === 'full'
                    ? this.getFullName(assignment.first_name, assignment.last_name, false)
                    : assignment.first_name;
            }
        },

        //
        // Close assignment overlay
        // --------------------------------------------------------------------------
        closeAssignment() {
            this.gradingAssignment = null;
            changeLayout();
        },
        //
        // Clear data from assignment
        // --------------------------------------------------------------------------
        clearAssignment(emit) {
            const vm = this;
            const assignment = vm.assignmentList.find(obj => obj.id === emit.id);
            apiRequest('/delete/gradebook', { id: assignment.grade_id }).then(() => {
                vm.notify('Grade has been cleared.', 'danger');

                // Remove grade from assignments display
                assignment.grade = null;
                assignment.grade_id = null;
                assignment.status = null;

            });
        },
        //
        // Grade Assignment
        // --------------------------------------------------------------------------
        gradeAssignment(emit) {
            const vm = this;
            const assignment = vm.assignmentList.find(obj => obj.id === emit.id);
            const userType = assignment.groups ? 'team' : 'user';
            const asgmtType = assignment.project_id ? 'Activity' : 'Custom';

            vm.gradingAssignment = assignment;
            vm.gradingProjectID = assignment.project_id;
            vm.gradingClass = {
                id: assignment.class_id,
                userType: userType,
                asgmtType: asgmtType
            }
            vm.gradingUser = {
                id: userType === 'team' ? assignment.team_id : assignment.user_id,
                team_name: assignment.team_name,
                name: assignment.team_name,
                first_name: assignment.first_name,
                last_name: assignment.last_name,
                fullname: this.getFullName(assignment.first_name, assignment.last_name)
            }
            changeLayout('full');

        },
        //
        // Submit grade
        // --------------------------------------------------------------------------
        submitGrade(emit) {
            const vm = this;
            const postURL = emit.update ? '/update/gradebook' : '/save/gradebook';
            const aType = vm.gradingClass.asgmtType === 'Activity' ? 1 : 2; // James php
            const asgmtID = vm.gradingAssignment.id;
            let message = 'Grade has been saved.';
            let request = {};

            // Update grade
            if (emit.update) {
                request = {
                    id: vm.gradingAssignment.grade_id,
                    teacher_id: vm.user_id,
                    points: emit.grade === null ? emit.grade : +emit.grade,
                    message: emit.message === '<p></p>' ? null : emit.message
                }
            // Or Add grade
            } else {
                request = {
                    points: emit.grade === null ? emit.grade : +emit.grade, // grade
                    type: aType, // assignmentType
                    type_id: vm.gradingAssignment.assignment_id, // assignment ID
                    user_id: vm.gradingUser.id, // team/student ID
                    team: vm.gradingUser.team_name || 0,
                    category_id: vm.gradingAssignment.category_id, // assignment category ID
                    project_id: vm.gradingProjectID, // project ID
                    teacher_id: vm.user_id,
                    message: emit.message === '<p></p>' ? null : emit.message
                };
            }

            // Update layout if not adding message, etc
            if (!emit.continue) {
                changeLayout();
                vm.gradingAssignment = null;
            }

            if((emit.grade === vm.gradingAssignment.grade || emit.grade === null) && emit.message) {
                message = 'Message has been added.'
            }

            apiRequest(postURL, request).then(result => {
                vm.notify(message);
                let asgmt = vm.assignmentList.find(obj => obj.id === asgmtID);
                asgmt.grade = emit.grade === null ? emit.grade : +emit.grade;
                asgmt.message = emit.message;
                asgmt.grade_id = result.id;
            });
        }
    }
};
</script>
<style lang="scss" scoped>
</style>
