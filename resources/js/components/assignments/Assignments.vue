<template>
<div class="assignments">
    <!-- Action buttons -->
    <div :class="{'flex-row row no-gutters mb-5 p-2 bg-light-primary rounded align-items-center': show_worksheets || ((totalCustomGrade || totalWorksheetGrade) && user_role === 'student' )}">
        <button
            v-if="show_worksheets"
            type="button"
            class="btn btn-sm btn-primary mr-2"
            data-toggle="modal"
            data-target="#create1Modal">
            Add Project
        </button>
        <button
            v-if="(totalCustomGrade || totalWorksheetGrade) && user_role === 'student'"
            type="button"
            class="btn btn-sm btn-secondary"
            @click="showGrades = !showGrades">
            {{ showGrades ? 'Hide' : 'View' }} Grades
        </button>
    </div>
    <div v-if="(!hasAssignments && !hasProjects) && user_role === 'student'">
        <p>You have no assignments yet.</p>
    </div>

    <!-- Notification -->
    <notification :msg="notice" />

    <!-- Grade totals -->
    <div v-if="showGradeTotals && showGrades" class="mb-5 border rounded assignments-grade-totals card-box-shadow p-4">
        <i class="fas fa-times" @click="showGradeTotals = false"></i>
        <h4 class="text-success">Grade Totals</h4>
        <ul class="list-group list-group-flush list-group-flush-dotted">
            <li v-for="category in assignments" :key="category.id" class="list-group-item p-1 px-0">
                {{ category.name }}
                <span v-if="category.name === 'Classwork' && show_worksheets" class="text-gray small">
                    (Includes activity sheets)
                </span>
                <span class="text-muted small">{{ category.category_value }}% of Grade</span>

                <!-- <span v-if="!hasTotalsData(category)" class="text-success float-right medium">
                    0/0 (<strong>{{ category.totalGradePercentage }}% out of {{ category.category_value }}%</strong>)
                </span> -->
                <span class="text-success float-right medium">
                    {{ category.totalGrade }}/{{ category.totalPoints }} Points
                    <i v-btooltip="{delay:100,title: `${category.totalGradePercentage}% out of ${category.category_value}%`}" class="fas fa-question-circle"></i>
                    <!-- (<strong>{{ category.totalGradePercentage }}% out of {{ category.category_value }}%</strong>) -->
                </span>
            </li>
        </ul>
        <template v-if="totalGrade">
            <hr class="m-0">
            <div class="text-right mt-2 pr-1">
                <span class="text-primary">Total overall grade earned so far:</span>
                <span class="text-success">
                    {{ totalGrade }}/{{ totalPoints }}
                    <!-- <span v-if="!isNaN(totalGradePercentage)">(<strong>{{ totalGradePercentage }}%</strong>)</span> -->
                    <i v-if="!isNaN(totalGradePercentage)" v-btooltip="{delay:100,title: `${totalGradePercentage}% out of 100%`}" class="fas fa-question-circle"></i>
                </span>
            </div>
        </template>
    </div>

    <p v-if="!projectLocked && showGrades" class="bg-light-secondary text-dark-secondary medium p-3 rounded mb-4">
        <strong>Note:</strong> Your project will start counting towards your grade once you've submitted your first activity sheet to {{ teacher_name }}. That project will be selected as your <em>primary project.</em> From then on, any additional projects created will not count towards your grade.
        You can switch a project to your primary project in its project actions.
    </p>

    <assignments-activity
        v-if="show_worksheets"
        :project-list="projects"
        :team_id="team_id"
        :show-grades="showGrades"
        @message="notify">
    </assignments-activity>

    <assignments-custom
        v-if="user_role === 'student' && hasAssignments"
        :show-worksheets="show_worksheets"
        :assignment-list="assignments"
        :assignment-id="assignmentId"
        :assignment-category-id="assignmentCategoryId"
        :show-grades="showGrades"
        :class="{'mt-5': show_worksheets || ((totalCustomGrade || totalWorksheetGrade) && user_role === 'student' )}"
         @message="notify">
    </assignments-custom>
</div>
</template>

<script>
import notify from '../../mixins/notify';
import {
    getNestedArrayTotal,
    getArrayTotal,
    //shortenString,
    formatDateTime
} from '../../functions/utils';

export default {
    name: 'Assignments',
    mixins: [notify],
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
        projectList: {
            type: Array,
            required: true
        },
        team_id: {
            type: Number,
            required: true
        },
        user_role: {
            type: String,
            required: true
        },
        show_worksheets: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            assignments: this.assignmentList,
            projects: this.projectList,
            projectLocked: null,
            showGrades: false,
            showGradeTotals: false
        }
    },
    computed: {
        hasAssignments() {
            return getNestedArrayTotal(this.assignments, 'assignments', 'id');
        },
        hasProjects() {
            return this.projectList.length;
        },
        //
        // Project totals
        // --------------------------------------------------------------------------
        // Removed 'hasProp' 'grade' at request of Clay
        totalWorksheetGrade() {
            return this.projectLocked ? getArrayTotal(this.projectLocked.worksheets, 'grade') : 0;
        },
        totalWorksheetPoints() {
            return this.projectLocked ? getArrayTotal(this.projectLocked.worksheets, 'category_value') : 0;
        },

        // Custom Assignment Totals
        // --------------------------------------------------------------------------
        totalCustomGrade() {
            return getArrayTotal(this.assignments, 'totalCustomGrade');
        },
        totalCustomPoints() {
            return getArrayTotal(this.assignments, 'totalCustomPoints');
        },

        //
        // Totals from both
        // --------------------------------------------------------------------------
        totalGrade() {
            return this.totalWorksheetGrade + this.totalCustomGrade;
        },

        totalPoints() {
            return this.totalWorksheetPoints + this.totalCustomPoints;
        },

        totalGradePercentage() {
            return getArrayTotal(this.assignments, 'totalGradePercentage');
        }
    },

    watch: {
        showGrades(val) {
            val === true && (this.showGradeTotals = true);
        }
    },

    created() {
        //
        // Process Locked Project
        // --------------------------------------------------------------------------
        this.processProjects();

        //
        //  Create Custom Assignment Individual Totals
        // --------------------------------------------------------------------------
        this.assignments.forEach(cat => {
            // Set Date Properties and shorten title
            this.setProps(cat.assignments);
        });
    },

    methods: {
        processProjects() {
            const vm = this;
            vm.projectLocked = vm.projects.find(proj => proj.locked)
            if(!vm.projectLocked) return;
            vm.setProps(vm.projectLocked.worksheets);
        },

        hasTotalsData(data) {
            return data.totalGrade !== 1 && data.totalPoints !== 1;
        },

        setProps(array) {
            array.forEach(a => {
                a.dueDateDiffDays = a.due_date && formatDateTime(a.due_date, 'diffDays');
                a.dueDateDiffHuman = a.due_date && formatDateTime(a.due_date, 'dueDateDiffHuman');
                a.dueDateFormatted = a.due_date && formatDateTime(a.due_date, 'long');
            });
        }
    }

}
</script>

<style>

</style>
