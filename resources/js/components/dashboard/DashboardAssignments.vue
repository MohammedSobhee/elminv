<template>
<div v-if="hasAnyAssignments" class="dashboard-assignments">
    <h4 class="mb-2">
        <i class="fas fa-edit ml-1"></i> Assignments recently updated or due soon:
    </h4>
    <dashboard-activity
        v-if="worksheets.length > 0"
        class="border rounded pt-3 px-3 pb-0 mb-4"
        :worksheet-list="worksheets"
        :team_id="team_id">
    </dashboard-activity>

    <dashboard-custom
        v-if="assignments.length > 0"
        class="border rounded pt-3 px-3 pb-0 mb-4"
        :assignment-list="assignments">
    </dashboard-custom>
</div>
</template>

<script>
import { formatDateTime, shortenString } from '../../functions/utils';
export default {
    name: 'DashboardAssignments',
    props: {
        assignmentList: {
            type: Array,
            required: true
        },
        worksheetList: {
            type: Array,
            required: true
        },
        team_id: {
            type: Number,
            default: 0
        },
        user_role: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            assignments: this.assignmentList,
            worksheets: this.worksheetList
        }
    },

    computed: {
        hasAnyAssignments() {
            return (this.worksheetList.length > 0 || this.assignmentList.length > 0)
        }
    },

    created() {
        this.setProps(this.assignments);
        this.setProps(this.worksheets);
    },

    methods: {
        setProps(array) {
            array.forEach(a => {
                if(a.label) {
                    a.label = shortenString(a.label, 45);
                }
                if(a.title) {
                    a.title = shortenString(a.title, 45);
                }

                a.dueDateDiffDays = a.due_date ? formatDateTime(a.due_date, 'diffDays') : null;
                a.dueDateDiffHuman = a.due_date ? formatDateTime(a.due_date, 'dueDateDiffHuman') : null;
                a.dueDateFormatted = a.due_date ? formatDateTime(a.due_date, 'long') : null;
            });
        }
    }
}
</script>
