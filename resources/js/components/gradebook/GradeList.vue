<template>
    <div>
        <!-- Assignment List Activity -->
        <div v-if="((search && slcdProjectID) || (!search && cls.asgmtType === 'Activity'))">
            <ul v-if="hasAssignments(assignments)" class="list-group">
                <li
                    v-for="assignment in assignments"
                    :key="assignment.project_id"
                    :class="[(search || (slcdProjectID === assignment.project_id)) && 'list-group-item-opened']"
                    class="list-group-item"
                    @click="toggleProject(assignment.project_id)">
                    <span :class="[ (!search && (slcdProjectID === assignment.project_id)) && 'font-weight-bold', ((search && assignment.project_id) && 'list-group-item-label-project')]">
                        {{ assignment.team_id && 'Team ' }}Project: {{ assignment.project_name }} <span v-if="assignment.locked" class="text-muted">(Primary Project)</span>
                        <span v-if="assignment.members !== undefined && assignment.members.length" v-memberlist="assignment.members.join('<br>')" class="ml-2 badge badge-pill badge-secondary">Members</span>
                    </span>
                    <collapse>
                        <!-- v-if="hasActiveAssignments(assignment.worksheets)" -->
                        <grade-item
                            v-show="search || (slcdProjectID === assignment.project_id)"
                            type="activity"
                            name="title"
                            :project_id="assignment.project_id"
                            :locked="assignment.locked"
                            :assignments="assignment.worksheets"
                            @grade="gradeAssignment"
                            @clear="clearAssignment">
                        </grade-item>
                        <!-- <div v-else class="small text-muted">
                            There are no active or graded activity sheets for this project.
                        </div> -->
                    </collapse>
                </li>
            </ul>
            <div v-else class="text-muted small">
                <span v-if="cls.userType === 'team'">
                    {{ cls.slcdUser.team_name }}
                </span>
                <span v-else>
                    {{ getFullName(cls.slcdUser.first_name, cls.slcdUser.last_name, false) }}
                </span>
                    has no projects.
            </div>
        </div>
        <!-- Assignment List Custom -->
        <div v-else-if="cls.asgmtType === 'Custom' && assignments !== null" class="list-group-grading">
            <template v-if="assignments.length > 0">
                <!-- v-if="hasActiveAssignments(assignments)" -->
                <grade-item
                    type="custom"
                    :assignments="assignments"
                    name="label"
                    @grade="gradeAssignment"
                    @clear="clearAssignment">
                </grade-item>
                <!-- <div v-else class="small text-muted">
                    There are no active or graded custom assignments.
                </div> -->
            </template>
            <div v-else class="text-muted small">
                {{ `${cls.class_name} has no assignments for this category.` }}
            </div>
        </div>
    </div>
</template>

<script>
// import { getFullName } from '../../functions/utils';
export default {
    name: 'GradeList',
    components: {
        GradeItem: () => import(/* webpackChunkName: 'grade-item' */ './GradeItem.vue')
    },
    directives: {
        memberlist: {
            bind: (el, binding) => {
                $(el).tooltip({
                    title: binding.value,
                    trigger: 'hover focus',
                    html: true,
                    delay: {
                        show: 500,
                        hide: 2000
                    }
                });
            }
        }
    },
    props: {
        cls: {
            type: Object,
            required: true
        },
        userid: {
            type: Number,
            default: 0
        },
        category_id: {
            type: Number,
            default: 0
        },
        assignments: {
            type: Array,
            required: true
        },
        search: {
            type: String,
            default: ''
        },
        project_id: {
            type: Number,
            default: 0
        }

    },
    data() {
        return {
            slcdProjectID: this.project_id
        }
    },

    watch: {
        project_id(val) {
            this.slcdProjectID = val;
        }
    },

    methods: {
        hasAssignments(assignments) {
            return (Array.isArray(assignments) && assignments.length > 0)
        },
        hasActiveAssignments(assignments) {
            if(!this.search) {
                return typeof assignments !== 'undefined' && assignments.some(obj =>
                    obj.has_answers || obj.status || obj.grade
                )
            } else {
                return true;
            }
        },
        toggleProject(id) {
            this.slcdProjectID = this.slcdProjectID === id ? null : id;
            this.$emit('project', id);
        },
        clearAssignment(emit) {
            this.$emit('clear', {
                classID: this.cls.id,
                asgmtType: this.project_id ? 'Activity' : 'Custom',
                projectID: emit.project_id && emit.project_id,
                asgmtID: emit.id,
                userID: this.userid && this.userid
            });
        },
        // Triggered by GradeItem emit, emitting to Gradebook
        gradeAssignment(emit) {
            this.$emit('grade', {
                classID: this.cls.id,
                categoryID: this.category_id,
                asgmtType: emit.project_id ? 'Activity' : 'Custom',
                projectID: emit.project_id,
                asgmtID: emit.id,
                userID: this.userid && this.userid
            })
        }
    }
};
</script>
