<template>
<ul :class="gradeItemClass">
    <template
        v-for="assignment in assignments">
        <!-- v-if="assignment.has_answers || assignment.status || assignment.grade" -->
        <li
            :key="assignment.id"
            class="list-group-item list-group-item-assignment"
            @click.prevent.stop="toggleAssignment(assignment.id)">
            <span
                v-btooltip
                :title="getTitle(assignment)"
                :class="getIconBackground(assignment)"
                class="card-icon align-self-start">
                <i :class="getIcon(assignment, 'far fa-bell')"></i>
            </span>
            <!-- Student / Team Name & Assignment Name for Teacher Pending Dashboard -->
            <span v-if="type === 'pending'" class="list-group-item-label">
                <strong
                    v-btooltip
                    :title="assignment.class_name"
                    class="usersname">{{ getUsersName(assignment, 'full') }}</strong>
                <span class="ml-2 d-inline-block">{{ assignment.assignment_name }}</span>
                <span
                    v-btooltip
                    :title="assignment.updated_long"
                    class="date">({{ assignment.updated }})</span>
            </span>
            <!-- Assignment name for Gradebook -->
            <span
                v-else
                :class="[assignment.grade && 'text-graded-name', toggle === assignment.id ? 'font-weight-bold' : '']"
                class="list-group-item-label">
                {{ assignment[name] }}
            </span>

            <div v-if="((project_id || assignment.project_id ) && (locked || assignment.locked)) || !(project_id || assignment.project_id)" class="list-group-item-grading">
                <!-- Student Comments -->
                <a
                    v-if="assignment.comments"
                    v-bpopover="assignment.comments"
                    class="grading-item icon-message"
                    :data-popover-title="`Message${type === 'pending' ? ' from ' + getUsersName(assignment) : ''}`"
                    tabindex="0"
                    @click.prevent.stop>
                    <i class="far fa-comment-alt"></i>
                </a>
                <!-- Grade / Points -->
                <span
                    v-if="assignment.grade !== null"
                    class="grading-item graded-item status status-graded"
                    @click.prevent.stop="toggleAssignment(assignment.id)">
                    <span class="text-grade">{{ assignment.grade }}</span>/{{ assignment.points || 0 }}
                    (<strong>{{ getPercentage(assignment.grade, assignment.points) }}%</strong>)
                </span>
                <!-- Or Grade Edit Icon -->
                <span
                    v-else
                    class="grading-item"
                    @click.prevent.stop="toggleAssignment(assignment.id)">
                    <i class="fas fa-edit" title="View/Grade"></i></span>
                <!-- Grade Clear Icon    -->
                <span
                    v-if="assignment.grade !== null"
                    class="grading-item"
                    @click.prevent.stop="clearConfirm = clearConfirm === assignment.id ? false : assignment.id">
                    <i v-btooltip="{title: 'Clear grade and send back'}" class="fas fa-times"></i></span>

                <!-- Popover Dialogue -->
                <popover-dialogue v-if="clearConfirm === assignment.id" :id="assignment.id" @answer="clearAssignment">
                    Are you sure? This cannot be undone and the message associated with the grade will be deleted.
                </popover-dialogue>
            </div>
        </li>
    </template>
</ul>
</template>

<script>
import gradeIcons from '../../mixins/gradeIcons';
import { getPercentage } from '../../functions/utils';

export default {
    name: 'GradeItem',
    mixins: [gradeIcons],
    props: {
        type: {
            type: String,
            default: 'custom'
        },
        name: {
            type: String,
            default: 'name'
        },
        project_id: {
            type: Number,
            default: 0
        },
        assignments: {
            type: Array,
            required: true
        },
        locked: {
            type: Number,
            default: 0
        },
        user: {
            type: Object,
            default: () => {}
        }
    },
    data() {
        return {
            popoverDialogue: null,
            clearConfirm: false,
            toggle: null
        };
    },

    computed: {
        gradeItemClass() {
            let cssClass = this.type === 'custom' ? 'list-group' : 'list-group-flush';
            cssClass = this.type === 'activity' ? cssClass + ' mt-2' : cssClass;
            return cssClass;
        }
    },

    methods: {
        getPercentage(val, total) {
            return getPercentage(val, total);
        },

        getUsersName(assignment, type = '') {
            if(assignment.groups) {
                return assignment.team_name
            } else {
                return type === 'full'
                    ? this.getFullName(assignment.first_name, assignment.last_name, false)
                    : assignment.first_name;
            }
        },

        clearAssignment(id) {
            this.$emit('clear', {
                id: id,
                project_id: this.project_id
            });
            this.clearConfirm = false;
        },

        // Emitting to GradeList
        toggleAssignment(id) {
            const vm = this;
            // let args = [vm.pid];
            // args.push(arguments);
            vm.toggle = vm.toggle === id ? null : id;
            vm.$emit('grade', {
                id: id,
                project_id: this.project_id
            });
        }
    }
};
</script>
