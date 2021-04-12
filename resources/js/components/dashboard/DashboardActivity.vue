<template>
<div>
    <strong class="text-primary">Activity Sheets</strong>
    <div v-if="worksheets.length !== 0" class="assignments">
        <ul class="list-group-flush">
            <template
                v-for="(worksheet, index) in worksheets">
                <li
                    v-if="worksheet.active"
                    :key="index"
                    class="list-group-item list-group-item-assignment">
                    <a :href="'/worksheets/' + (team_id > 0 ? 'team/' : '') + worksheet.id + '/' + worksheet.project_id">
                    <div style="cursor:pointer">
                        <span
                            v-btooltip
                            :title="getTitle(worksheet)"
                            :class="getIconBackground(worksheet)"
                            class="card-icon align-self-start">
                            <i :class="getIcon(worksheet)"></i>
                        </span>
                        <span class="list-group-item-label">
                            <span v-if="worksheet.team_id">Team Project: </span>
                            {{ worksheet.project_name }} - {{ worksheet.title }}
                            <span
                                v-if="worksheet.due_date && !worksheet.assignment_submitted_date"
                                v-btooltip="{title: worksheet.dueDateFormatted, delay: 200}"
                                :class="worksheet.dueDateDiffDays > 2 ? 'text-faded' : 'text-faded-danger'"
                                class="ml-2">
                                (Due {{ worksheet.dueDateDiffHuman }})
                            </span>
                        </span>
                    </div>
                    </a>
                    <div class="right-actions">
                        <span v-if="worksheet.grade !== null" class="status status-graded"><strong>{{ worksheet.grade }}</strong> / {{ worksheet.category_value }} Points</span>
                        <span
                            v-if="worksheet.message"
                            v-bpopover="worksheet.message"
                            class="icon-message"
                            tabindex="0"
                            data-popover-title="Message">
                            <i class="far fa-comment-alt"></i>
                        </span>
                    </div>
                </li>
            </template>
        </ul>
    </div>
</div>
</template>

<script>
import gradeIcons from '../../mixins/gradeIcons';
import { formatDate } from '../../functions/utils';

export default {
    name: 'DashboardActivity',
    mixins: [gradeIcons],
    props: {
        worksheetList: {
            type: Array,
            required: true
        },
        team_id: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            worksheets: this.worksheetList,
            optionsType: '',
            projectName: '',
            type: this.team_id === 0 ? 'user' : 'team'
        };
    },
    methods: {
        //
        // Format Date
        // --------------------------------------------------------------------------
        formatDate(date, type) {
            return formatDate(date, type);
        }
    }
};
</script>
<style lang="scss" scoped>
.popover-dialogue {
    top: -2.5rem;
    right: auto;

    &::after {
        right: auto;
        left: 2.3rem;
    }
}
</style>
