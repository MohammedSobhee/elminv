<template>
    <div class="gradebook">
        <div v-if="hasAssignments || searchUserType" class="d-xl-flex justify-content-between align-items-center form-wrapper form-wrapper-search">
            <span>
                <button
                    v-tooltip:refresh="'Refresh and pull in newly submitted assignments and student comments.'"
                    class="btn btn-sm btn-primary btn-reset-gradebook ml-2"
                    @click="refreshData">
                    Refresh
                </button>
            </span>
            <span>
                <ul v-if="updatedClasses.length > 0" class="filter-all p-2">
                    <li class="filter-all-item">
                        <multiselect
                            v-model="searchForUser"
                            v-tooltip:searchuser="'Find graded, grade totals, pending, and messaged assignments by individual.'"
                            :options="searchForUserList"
                            :searchable="true"
                            :close-on-select="true"
                            class="multiselect-xs"
                            :show-labels="false"
                            track-by="id"
                            label="name"
                            placeholder="Search Students"
                            @select="searchUserSelect($event, 'user')">
                        </multiselect>
                    </li>
                    <!-- <li v-if="teamCount" class="filter-all-item">
                        <multiselect
                            v-model="searchForTeam"
                            v-tooltip:searchteam="'Find graded, grade totals, pending, and messaged assignments by team.'"
                            :options="searchForTeamList"
                            :searchable="true"
                            :close-on-select="true"
                            class="multiselect-xs"
                            :show-labels="false"
                            track-by="id"
                            label="name"
                            placeholder="Search Teams"
                            @select="searchUserSelect($event, 'team')">
                        </multiselect>
                    </li> -->
                    <li class="filter-all-item">
                        <multiselect
                            v-model="searchStatusByUser"
                            v-tooltip:statususer="'Find all students with graded, pending, or messaged assignments.'"
                            :options="searchByStatusOptions"
                            :searchable="false"
                            :close-on-select="true"
                            class="multiselect-xs"
                            :show-labels="false"
                            track-by="id"
                            label="name"
                            placeholder="Search by Status"
                            @select="searchStatusSelect($event, 'user')">
                            <template slot="singleLabel" slot-scope="{ option }">
                                <strong>Students:</strong>
                                <span>{{ option.name }}</span>
                            </template>
                        </multiselect>
                    </li>
                    <!-- <li v-if="teamCount" class="filter-all-item">
                        <multiselect
                            v-model="searchStatusByTeam"
                            v-tooltip:statusteam="'Find all teams with graded, pending, or messaged assignments.'"
                            :options="searchByStatusOptions"
                            :searchable="false"
                            :close-on-select="true"
                            class="multiselect-xs"
                            :show-labels="false"
                            track-by="id"
                            label="name"
                            placeholder="Teams by Status"
                            @select="searchStatusSelect($event, 'team')">
                            <template slot="singleLabel" slot-scope="{ option }">
                                <strong>Teams:</strong>
                                <span>{{ option.name }}</span>
                            </template>
                    </multiselect>
                    </li> -->
                    <li class="filter-all-item">
                        <button
                            id="searchreset"
                            v-tooltip:reset="'Reset all selections'"
                            type="button"
                            class="btn btn-sm btn-light btn-reset-gradebook"
                            @click="resetSelection(true)">
                            Reset
                        </button>
                    </li>
                </ul>
            </span>
        </div>

        <notification :msg="notice" class="medium" />

        <div v-show="loading" class="loading-circle mt-5 mb-5"></div>

        <div v-if="hasAssignments && !loading">
        <transition-group
            :name="(animate ? 'row' : 'page')"
            class="row-transition d-flex row">
            <!-- Class -->
            <div
                v-for="cls in updatedClasses"
                :key="cls.id"
                :class="classColWidth(cls)"
                class="col-xs-12 row-animate">
                <div v-if="(searchUserType && classHasAssignments(cls)) || !searchUserType">
                    <h4 class="mt-4">
                        {{ cls.class_name | capitalize }}
                        <div class="d-inline-block dropdown dropdown-class-edit">
                            <a
                                href="#"
                                role="button"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"><i class="fas fa-bars"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a class="dropdown-item" :href="`/edit/class/${cls.id}`">Edit Class</a></li>
                                <li><a class="dropdown-item" :href="`/edit/team/${cls.id}`">Edit / Add Teams</a></li>
                                <li><a class="dropdown-item" href="#" @click="downloadCSV(cls.id)">Export {{ searchUserType ? $options.filters.capitalize(searchUserType) : 'Class' }} Grades</a></li>
                            </ul>
                        </div>
                    </h4>
                    <!-- Total Grade / Points -->
                    <div v-if="(searchForUser || searchForTeam) && !Number.isNaN(searchTotalPercentage)" class="d-inline-block pl-3 mb-1 align-self-end">
                        Total Current Grade for
                        <span v-if="searchForUser !== null" class="text-primary">
                            {{ searchForUser.name }}:
                        </span>
                        <span v-else-if="searchForTeam !== null" class="text-primary">
                            {{ searchForTeam.name }}:
                        </span>
                        <span class="text-success">{{ searchTotalGrade }}/{{ searchTotalPoints }} (<strong>{{ searchTotalPercentage }}%</strong>)</span>
                    </div>
                </div>
                <!-- Accordion -->
                <div v-if="(cls.user_count > 0)" class="accordion">
                    <!-- Card -->
                    <template
                        v-for="cat in cls.categories">
                        <div
                            v-if="!searchUserType || (searchUserType && cat.categoryHasAsgmts === cls.id)"
                            :key="cat.id"
                            class="card"
                            :class="[(searchUserType === cls.userType || (slcdCat.id === cat.id && slcdClass.id === cls.id)) && 'collapse-selected']">
                        <!-- Card Header -->
                        <div
                            class="card-header"
                            @click.self="selectCategory(cls.id, cat.id)">
                            <div class="card-toggler">
                                <span
                                    v-if="slcdCat.id === cat.id && slcdClass.id === cls.id"
                                    class="card-icon align-self-start"
                                    @click.prevent.stop="resetSelection(true)">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                                <span
                                    v-else
                                    v-tooltip:open="'Click to open or reset selections for a selected class.'"
                                    class="card-icon align-self-start"
                                    @click="selectCategory(cls.id, cat.id)">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                                <span
                                    v-tooltip:categories="'These are the categories chosen in settings.'"
                                    class="card-text"
                                    @click="selectCategory(cls.id, cat.id)">
                                    {{ cat.name | capitalize }}
                                </span>
                                <span
                                    v-if="!searchUserType && cat.pendingAsgmts"
                                    v-btooltip="{delay:500,title:`${cat.pendingAsgmts} assignments awaiting grade`}"
                                    class="ml-2 badge badge-pill badge-pending small">
                                    {{ cat.pendingAsgmts }}
                                </span>
                            </div>
                            <!-- Grading  Menu -->
                            <div
                                v-if="!searchUserType && cls.userType && (slcdCat.id === cat.id) && (slcdClass.id === cls.id)"
                                class="menu-grade">
                                <!-- <span v-if="cls.team_count && cat.name === 'Classwork'">
                                    <span class="menu-grade-label">Grade by:</span>
                                    <i
                                        :class="cls.userType == 'team' && 'selected'"
                                        class="fas fa-users"
                                        @click.prevent.stop="selectUserType(cls.id, 'team', cat.id)"></i>
                                    <i
                                        :class="cls.userType == 'user' && 'selected'"
                                        class="fas fa-user"
                                        @click.prevent.stop="selectUserType(cls.id, 'user', cat.id)"></i>
                                </span> -->
                                <grade-menu-form
                                    :class-item="cls"
                                    :category-name="cat.name"
                                    @menuUpdate="menuGradeSelect">
                                </grade-menu-form>
                            </div>
                            <!-- End Grading menu -->
                        </div>
                        <!-- End Card Header -->
                        <collapse>
                        <div v-if="searchUserType === cls.userType || (slcdCat.id === cat.id && slcdClass.id === cls.id)">
                            <!-- Card Body -->
                            <div class="card-body">
                                <div clas="card">
                                    <!-- User Type - Currently unused -->
                                    <ul
                                        v-if="!cls.userType && !searchUserType"
                                        class="list-group list-group-horizontal">
                                        <li
                                            v-if="cls.team_count"
                                            class="list-group-item list-group-item-button"
                                            @click="selectUserType(cls.id, 'team', cat.id)">
                                            <span :class="cls.userType === 'team' && 'selected'">
                                                <i class="fas fa-users mr-2"></i>Team
                                                <!-- <span class="ml-2 badge badge-pill badge-secondary">{{ cls.team_count }}</span> -->
                                            </span>
                                        </li>
                                        <li class="list-group-item list-group-item-button" @click="selectUserType(cls.id, 'user', cat.id)">
                                            <span :class="cls.userType === 'user' && 'selected'">
                                                <i class="fas fa-user mr-2"></i>Individuals
                                                <!-- <span class="ml-2 badge badge-pill badge-secondary">{{ cls.user_count }}</span> -->
                                            </span>
                                        </li>
                                    </ul>
                                    <!-- User List -->
                                    <div v-if="searchUserType || !cls.asgmtType">
                                        <div v-show="cls.loading" class="loading-circle"></div>
                                        <!-- Assignment Type Team -->
                                        <ul v-if="cls.userType === 'team' && cls.teamList && !cls.loading" class="list-group">
                                            <template v-if="!searchUserType && cat.name === 'Classwork'">
                                            <li
                                                v-for="team in cls.teamList"
                                                :key="team.id"
                                                class="list-group-item"
                                                @click="selectAssignmentType(cls.id, team.id, 'Activity')">
                                                {{ team.team_name }}
                                                <span v-memberlist="team.members.join('<br>')" class="ml-2 badge badge-pill badge-secondary">Members</span>
                                            </li>
                                            </template>
                                            <template v-else>
                                                <template v-for="team in cls.teamList">
                                                <li
                                                    v-if="team['activity'].length > 0"
                                                    :ref="'member'+team.id"
                                                    :key="team.id"
                                                    class="list-group-item">
                                                    <span class="mb-1" :class="{'font-weight-bold pb-2 d-inline-block': searchUserType}">{{ team.team_name }}</span>
                                                    <span
                                                        v-memberlist="team.members.join('<br>')"
                                                        class="ml-2 badge badge-pill badge-secondary">
                                                        Members
                                                    </span>
                                                    <grade-list
                                                        v-if="searchUserType && team['activity'].length > 0 && cat.name === 'Classwork'"
                                                        :cls="cls"
                                                        :userid="team.id"
                                                        :assignments="team['activity']"
                                                        :project_id="1"
                                                        :category_id="cat.id"
                                                        :search="searchUserType"
                                                        class="mb-2"
                                                        @project="toggleProject"
                                                        @clear="clearAssignment"
                                                        @grade="gradeAssignment" />
                                                   <span v-if="cat.totalGrade" class="float-right mr-5 pt-1">
                                                        Total for {{ cat.name }}: <span class="text-success">{{ cat.totalGrade }}/{{ cat.totalPoints }} ({{ cat.totalGradePercentage }}%)</span>
                                                    </span>
                                                </li>
                                                </template>
                                            </template>
                                        </ul>
                                        <!-- Assignment Type User -->
                                        <ul v-if="cls.userType === 'user' && !cls.loading" class="list-group">
                                            <template v-if="!searchUserType && cat.name === 'Classwork'">
                                            <li
                                                v-for="user in cls.userList"
                                                :key="user.id"
                                                class="list-group-item">
                                                <span>{{ user.fullname }}</span>
                                                <span
                                                    v-if="user.pendingAsgmts"
                                                    v-btooltip="{delay:500,title:`${user.pendingAsgmts} assignments awaiting grade`}"
                                                    class="ml-2 badge badge-pill badge-pending small">
                                                    {{ user.pendingAsgmts }}
                                                </span>
                                                <div class="list-group-item-atype">
                                                    <span class="link" @click="selectAssignmentType(cls.id, user.id, 'Activity')">Activity Sheets</span>
                                                    <span class="link" @click="selectAssignmentType(cls.id, user.id, 'Custom')">Custom Assignments</span>
                                                </div>
                                            </li>
                                            </template>
                                            <template v-else>
                                                <template v-for="user in cls.userList">
                                                <li
                                                    v-if="(user[cat.name.toLowerCase()].length > 0) || (user['activity'].length > 0 && cat.name === 'Classwork') || !searchUserType"
                                                    :key="user.id"
                                                    :ref="'member'+user.id"
                                                    class="list-group-item"
                                                    :class="{ 'list-group-item-opened': searchUserType }"
                                                    @click="selectAssignmentType(cls.id, user.id, 'Custom')">
                                                    <span :class="{'font-weight-bold pb-2 d-block': searchUserType}">
                                                        {{ user.fullname }}
                                                        <a v-btooltip="{delay:500,title:`Login as ${user.fullname}`}" :href="`/dashboard/loginas/${user.id}`" @click.stop><i class="text-light-gray fas fa-sign-in-alt"></i></a>
                                                    </span>
                                                    <span
                                                        v-if="!searchUserType && user.pendingAsgmts"
                                                        v-btooltip="{delay:500,title:`${user.pendingAsgmts} assignments awaiting grade`}"
                                                        class="ml-2 badge badge-pill badge-pending small">
                                                        {{ user.pendingAsgmts }}
                                                    </span>
                                                    <grade-list
                                                        v-if="searchUserType && user['activity'].length > 0 && cat.name === 'Classwork'"
                                                        :cls="cls"
                                                        :userid="user.id"
                                                        :assignments="user['activity']"
                                                        :search="searchUserType"
                                                        :project_id="1"
                                                        :category_id="cat.id"
                                                        class="mb-2"
                                                        @project="toggleProject"
                                                        @clear="clearAssignment"
                                                        @grade="gradeAssignment" />
                                                    <grade-list
                                                        v-if="searchUserType && user[cat.name.toLowerCase()].length > 0"
                                                        :cls="cls"
                                                        :userid="user.id"
                                                        :category_id="cat.id"
                                                        :assignments="user[cat.name.toLowerCase()]"
                                                        :search="searchUserType"
                                                        @clear="clearAssignment"
                                                        @grade="gradeAssignment" />
                                                    <span v-if="cat.totalGrade" class="float-right mr-5 pt-1">
                                                        Total for {{ cat.name }}: <span class="text-success">{{ cat.totalGrade }}/{{ cat.totalPoints }} ({{ cat.totalGradePercentage }}%)</span>
                                                    </span>
                                                </li>
                                                </template>
                                            </template>
                                            <!-- End Assignment Type -->
                                        </ul>
                                    </div>
                                    <!-- End User List -->
                                    <div v-show="!searchUserType && cls.loading && cls.asgmtType" class="loading-circle"></div>
                                    <!-- Selected Assignment List Activity -->
                                    <grade-list
                                        v-if="slcdAssignments !== null"
                                        :cls="cls"
                                        :assignments="slcdAssignments"
                                        :search="searchUserType"
                                        :project_id="slcdProjectID"
                                        @project="toggleProject"
                                        @clear="clearAssignment"
                                        @grade="gradeAssignment" />
                                    <!-- End Assignment Type -->
                                </div>
                            </div>
                            <!-- End Card Body -->
                        </div>
                        </collapse>
                        <!-- End Collapse Div -->
                    </div>
                    </template>
                    <!-- End Card -->
                </div>
                <!-- End Accordion -->
                <div v-else class="text-muted">
                    No assigned students
                </div>
            </div>
            <!-- Class -->
        </transition-group>
        </div>
        <!-- Results notices -->
        <div v-else-if="!loading">
            <div v-if="searchUserType">
                <p class="mt-5 text-dark-secondary">
                    There are no results for
                    <span v-if="searchForUser !== null">
                        {{ searchForUser.name }}.
                    </span>
                    <span v-else-if="searchForTeam !== null">
                        {{ searchForTeam.name }}.
                    </span>
                    <span v-else>
                        {{ `${searchUserType}s with ${searchStatus} assignments.` }}
                    </span>
                </p>
            </div>
            <info-alert v-else>
                There is nothing to grade yet.
            </info-alert>
        </div>


        <grade-assignment
            v-if="gradingAssignment"
            v-closeEsc
            :assignment="gradingAssignment"
            :user="gradingUser"
            :classinfo="slcdClass"
            :project_id="slcdProjectID"
            class="gb-assignment"
            @close="closeAssignment"
            @submit-grade="submitGrade">
        </grade-assignment>
        <!-- <pre class="small">{{ classes }}</pre> -->
    </div>
    <!-- Gradebook -->
</template>

<script>
import {
    capitalizeWords,
    changeLayout,
    findObjMoveTop,
    getArrayTotal,
    formatDate
} from '../../functions/utils';

import * as gb from '../../functions/gradebookUtils';
import notify from '../../mixins/notify';
import apiRequest from '../../functions/apiRequest';
import downloadCSV from '../../functions/downloadCSV';
import closeEsc from '../../directives/closeEsc';

export default {
    name: 'Gradebook',

    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect'),
        GradeList: () => import(/* webpackChunkName: 'grade-list' */ './GradeList'),
        GradeMenuForm: () => import(/* webpackChunkName: 'grade-menu-form' */ './GradeMenuForm'),
        GradeAssignment: () => import(/* webpackChunkName: 'grade-assignment' */ './GradeAssignment')
    },

    directives: {
        closeEsc,
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

    mixins: [notify],

    props: {
        classes: {
            type: Array,
            required: true
        },
        pending_user_type: {
            type: String,
            default: ''
        },
        pending_user_id: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            animate: true,
            classData: this.classes.filter(obj => obj.user_count !== 0),
            updatedClasses: null,
            gradingAssignment: null,
            gradingUser: null,
            loading: false,
            loadingCookies: true,
            popoverDialogue: null,
            slcdProjectID: null,
            slcdAssignments: null,
            slcdCat: {},
            slcdClass: {},
            slcdUserType: null,

            searchForUser: null,
            searchForTeam: null,
            searchStatusByTeam: null,
            searchStatusByUser: null,
            searchTotalGrade: 0,
            searchTotalPoints: 0,
            searchTotalPercentage: 0,

            searchUserType: '',
            searchStatus: '',

            searchByStatusOptions: [
                { id: 'graded', name: 'Graded' },
                { id: 'pending', name: 'To be graded' },
                { id: 'messaged', name: 'Messaged' }
            ],
            searchForUserList: [],
            searchForTeamList: [],
            getAllCached: false
        }
    },

    computed: {
        hasAssignments() {
            return this.updatedClasses.reduce((totalAsgmts, cls) =>
                totalAsgmts + this.classHasAssignments(cls), 0);
        },
        teamCount() {
            return getArrayTotal(this.updatedClasses, 'team_count');
        }
    },
    watch: {
        slcdAssignments(val) {
            if(val) {
                const projectID = gb.preSelectProject(val)
                projectID && this.openProject(projectID)
            }
        }
    },

    created() {
        const vm = this;
        vm.cookie = vm.getCookie();

        // Add needed reactive props to classes array
        vm.prepClassData();

        // Clone classData to maintain original array for resetting searches
        vm.updatedClasses = _.cloneDeep(vm.classData.filter(obj => obj.user_count !== 0));

        // Load check
        this.loadCheck();

        // Set tooltip props
        vm.setupTooltipCookie(vm.cookie, {
            refresh: 0,
            reset: 0,
            open: 0,
            categories: 0,
            resetButton: 0,
            searchuser: 0,
            searchteam: 0,
            statususer: 0,
            statusteam: 0
        });
    },

    methods: {
        //
        // Prep incoming class data
        // --------------------------------------------------------------------------
        prepClassData() {
            const vm = this;
            // Add needed reactive props to classes array
            vm.classData = vm.classData.map(c => {
                const cls = c;
                Vue.set(cls, 'asgmtType', null);
                Vue.set(cls, 'filterType', 'All');
                Vue.set(cls, 'loading', false);
                Vue.set(cls, 'slcdUser', null);
                Vue.set(cls, 'userType', null);
                // 01/15/2021 - Team selection no longer used
                // Vue.set(cls, 'teamList', gb.setupUserList(cls.teamList, 'team'));
                Vue.set(cls, 'userList', gb.setupUserList(cls.userList, 'user'));
                // cls.categories = gb.updateAssignmentsCheck(cls, 'team');
                cls.categories = gb.updateAssignmentsCheck(cls, 'user');
                //cls.pendingAsgmts = gb.classPendingAssignments(cls);

                // Compile user and team lists for use in multiselect
                vm.searchForUserList.push(...cls.userList.map(obj =>
                    ({ id: obj.id, name: obj.fullname })));
                // vm.searchForTeamList.push(...cls.teamList.map(obj =>
                //     ({ id: obj.id, name: obj.team_name })));
                return cls
            })
        },

        //
        // Open Project
        // --------------------------------------------------------------------------
        openProject(projectID) {
            this.slcdProjectID = projectID;
            this.setCookie(this.cookie, { projectID });
        },
        //
        // Grade Assignment Methods
        // --------------------------------------------------------------------------
        gradeAssignment(emit) {
            const vm = this;
            // Assign grading user for use in grade assignment component
            if(emit.userID) {
                // Update selected class, user, assignment type if using get all
                vm.updateGetAllSelected(vm.classData, emit);
                vm.slcdCat = vm.slcdClass.categories.find(cat => cat.id === emit.categoryID);
                vm.gradingUser = vm.slcdClass.slcdUser;

            } else {
                // Selected class and user already selected if not get all
                vm.gradingUser = vm.updatedClasses.find(obj => obj.id === vm.slcdClass.id).slcdUser;
            }

            vm.slcdProjectID = emit.projectID;
            vm.gradingAssignment = vm.extractAssignment(emit.asgmtID, emit.projectID);

            changeLayout('full');
        },

        //
        // Clear data from assignment
        // --------------------------------------------------------------------------
        clearAssignment(emit) {
            if (emit.asgmtID === 'cancel') return;
            const vm = this;
            const nullAssignmentGrade = assignment => {
                assignment.grade = null;
                assignment.grade_id = null;
                assignment.status = null;
            }

            // Remove grade
            if(emit.userID) vm.updateGetAllSelected(vm.classData, emit);
            let assignment = vm.extractAssignment(emit.asgmtID, emit.projectID);

            vm.postData('/delete/gradebook', { id: assignment.grade_id }).then(() => {
                vm.notify('Grade has been cleared.', 'danger');

                // Remove grade from classData display
                nullAssignmentGrade(assignment);

                if(vm.searchUserType) {
                    // Remove grade from updatedClasses display if searching
                    if(emit.userID) vm.updateGetAllSelected(vm.updatedClasses, emit);
                    assignment = vm.extractAssignment(emit.asgmtID, emit.projectID);
                    nullAssignmentGrade(assignment);
                }

            });
        },

        //
        // Close assignment overlay
        // --------------------------------------------------------------------------
        closeAssignment() {
            this.gradingAssignment = null;
            changeLayout();
        },

        // Submit grade
        submitGrade(emit) {
            const vm = this;
            const postURL = emit.update ? '/update/gradebook' : '/save/gradebook';
            const aType = vm.slcdClass.asgmtType === 'Activity' ? 1 : 2; // James php
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
                };
                if(vm.gradingAssignment.message === emit.message) {
                    request.noemail = 1;
                }
            // Or Add grade
            } else {
                request = {
                    points: emit.grade === null ? emit.grade : +emit.grade, // grade
                    type: aType, // assignmentType
                    type_id: vm.gradingAssignment.id, // assignment ID
                    user_id: vm.slcdClass.slcdUser.id, // team/student ID
                    category_id: vm.slcdCat.id, // assignment category ID
                    project_id: vm.slcdProjectID, // project ID
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

            vm.postData(postURL, request).then(result => {
                vm.notify(message);
                // Update classData
                vm.updateClassDataAsgmts(vm.slcdClass);
                // Update updatedClasses with this assignment's new data for display
                vm.updateGetAllSelected(vm.updatedClasses, emit);
                // Assign grade props
                vm.updateAsgmtGrade(emit, result, asgmtID);
                // Update grade totals
                const user = gb.getAsgmtsByStatus(vm.slcdClass.slcdUser, vm.slcdClass, 'all');
                vm.setGradeTotals(user);
            });
        },
        updateAsgmtGrade(emt, rs, asgmtID) {
            const vm = this;
            let asgmt = vm.extractAssignment(asgmtID, vm.slcdProjectID);
            asgmt.grade = emt.grade === null ? emt.grade : +emt.grade;
            asgmt.message = emt.message;
            asgmt.grade_id = rs.id || vm.gradingAssignment.grade_id;

            // If team activity sheet, refresh to update other members' grade / pendingAsgmts
            if(asgmt.team_id) {
                vm.slcdClass.userList.forEach(u => {
                    const user = u;
                    const project = user.activity.find(p => p.project_id === vm.slcdProjectID);
                    const asgmtTeam = (project !== undefined)
                        && project.worksheets.find(a => a.id === asgmt.id);
                    if(asgmtTeam) {
                        asgmtTeam.grade_id = asgmt.grade_id;
                        asgmtTeam.message = asgmt.message;
                        asgmtTeam.grade = asgmt.grade;
                    }
                });
            }
            // Recalculate pending assignment count
            vm.slcdCat.pendingAsgmts = gb.catPendingAssignments(
                vm.slcdCat.name.toLowerCase(),
                vm.slcdClass.userList
            );
        },
        updateClassDataAsgmts(slcdClass) {
            const index = this.classData.findIndex(obj => obj.id == this.slcdClass.id);
            this.classData[index] = slcdClass;
        },
        //
        // Update selected class and its user
        // --------------------------------------------------------------------------
        updateGetAllSelected(classArray, emit) {
            const vm = this;
            vm.slcdClass = classArray.find(cls => cls.id === emit.classID);
            vm.slcdClass.asgmtType = emit.asgmtType;
            vm.slcdClass.slcdUser = vm.slcdClass[vm.slcdClass.userType + 'List']
                .find(usr => usr.id == emit.userID)
        },

        //
        // Extract assignment by ID
        // --------------------------------------------------------------------------
        extractAssignment(id, projectID) {
            const vm = this;
            let assignments;
            //project = { team: '' };

            if(vm.slcdClass.asgmtType === 'Activity') {
                const project = vm.slcdClass.slcdUser['activity'].find(obj =>
                    obj.project_id === projectID);
                assignments = project.worksheets;
            } else {
                const cats = vm.slcdClass.categories.map(c => c.name.toLowerCase());
                assignments = cats.flatMap(key => {
                    return vm.slcdClass.slcdUser[key];
                })
            }
            let assignment = assignments.find(asgmt => asgmt.id === id);
            //assignment.team = project.team || '';
            return assignment;
        },
        //
        // Select Class
        // --------------------------------------------------------------------------
        selectClass(classID) {
            const vm = this;
            // Assign selected class and move it to the top and scroll up
            vm.slcdClass = findObjMoveTop(vm.updatedClasses, classID);
        },

        //
        // Select Assignment Category Type
        // --------------------------------------------------------------------------
        selectCategory(classID, catID) {
            const vm = this;
            vm.searchUserType = '';

            // If only collapsing, do nothing
            if (vm.isCatCollapsing(classID, catID)) return;

            // Select Class
            vm.selectClass(classID);

            // Assign selected category
            vm.slcdCat = vm.slcdClass.categories.find(obj => obj.id == catID);
            vm.slcdClass.slcdCat = vm.slcdClass.categories.find(obj => obj.id == catID);

            // Recalculate pending assignment count
            vm.slcdCat.pendingAsgmts = gb.catPendingAssignments(
                vm.slcdCat.name.toLowerCase(),
                vm.slcdClass.userList
            );

            // Assign Category Cookie
            vm.assignDefinedCookie('catID', catID);

            // 01/14/20121 - Skip team/individual selection
            vm.selectUserType(classID, 'user', catID);

            // Return if loading cookies
            if (vm.loadingCookies) return;

            // Check if selected class has selected user
            if (vm.checkSelectedUser()) return;

            // Check if an assignment type switch is needed
            vm.checkAssignmentType(classID, catID);
        },
        //
        // Category select checks
        // --------------------------------------------------------------------------
        isCatCollapsing(classID, catID) {
            const vm = this;
            // When not switching categories or class deselect category if clicked
            // again to close collapse component
            if (vm.slcdCat.id === catID && vm.slcdClass.id === classID) {
                vm.slcdCat = {};
                return true;
            }
            return false;
        },
        checkSelectedUser() {
            const vm = this;

            // Select user type when switching classes if selected user in class doesn't exist
            if ([undefined, null].includes(vm.slcdClass.slcdUser)) {
                // Remove userID cookie if switching classes
                vm.removeDefinedCookie('userID');

                // If there are no teams or not classwork head straight to users/students
                // const userType = (!vm.slcdClass.team_count || vm.slcdCat.name !== 'Classwork')
                //     ? 'user'
                //     : vm.slcdUserType;


                return true;
            }
            return false;
        },
        checkAssignmentType(classID, catID) {
            const vm = this;
            // When category selected, select assignment type 'Custom' if switching from classwork
            // to another category that has no activity sheets
            if (vm.cookie.asgmtType === 'Activity' && vm.slcdCat.name !== 'Classwork') {
                // But assign Classwork's assignment type to Activity to remember its selection
                vm.slcdClass.categories.find(obj => obj.name == 'Classwork').asgmtType = 'Activity';


                // Switch to user if coming from Activity teams
                if(vm.cookie.asgmtType === 'Activity' && vm.slcdClass.userType === 'team') {
                    vm.slcdClass.userType = 'user';
                    vm.selectUserType(classID, vm.slcdClass.userType, catID);
                } else { // Use the below only if going back to custom having teams
                    vm.selectUserType(classID, vm.slcdClass.userType, catID).then(() => {
                        vm.selectAssignmentType(classID, vm.slcdClass.slcdUser.id, 'Custom');
                    });
                }

            } else {
                // If Classwork's assignment type was Activity, use it instead
                if (typeof vm.slcdCat.asgmtType !== 'undefined') {
                    vm.selectAssignmentType(
                        classID,
                        vm.slcdClass.slcdUser.id,
                        vm.slcdCat.asgmtType
                    );
                    // Remove Classwork's assignment type so that its not 'remembered' permanantly
                    delete vm.slcdCat.asgmtType;
                } else {
                    // Or select assignment type as normal
                    vm.selectAssignmentType(
                        classID,
                        vm.slcdClass.slcdUser.id,
                        vm.slcdClass.asgmtType
                    );
                }
            }
        },

        //
        // Select User Type
        // --------------------------------------------------------------------------
        async selectUserType(classID, userType, catID) {
            const vm = this;

            // Set Cookie
            vm.setCookie(vm.cookie, { classID, catID, userType });

            // Assign userType and catID arguments to targetted class
            // as selected values for later use
            vm.slcdClass.userType = userType;

            // Test remembering userType across classes instead of remembering per class
            vm.slcdUserType = userType;

            // Remove selected asgmtType value from targetted class so that user selection
            // shows up in template
            // vm.slcdClass.asgmtType = null; // Todo: decide whether to remember assignment by class or cat
            //vm.slcdCat.asgmtType = null;
        },

        //
        // Select Assignment and User Type
        // --------------------------------------------------------------------------
        selectAssignmentType(classID, userID, asgmtType) {
            const vm = this;

            // Update target class's selected user
            vm.slcdClass.slcdUser = vm.slcdClass[vm.slcdClass.userType + 'List'].find(
                obj => obj.id === userID
            );

            // No need to set asgmtType or cookie for getAll
            if(this.searchUserType) return;

            // Update target classes selcted assignment type
            vm.slcdClass.asgmtType = asgmtType;

            // Set Cookie
            vm.setCookie(vm.cookie, { asgmtType });

            // Set assignments
            vm.getAssignments(classID, userID, asgmtType);

            vm.loadingCookies = false;
        },

        //
        // Get Assignments from API (not API anymore)
        // --------------------------------------------------------------------------
        getAssignments(classID, userID, asgmtType) {
            const vm = this;
            const slcdCatProp = gb.getSelectedCatProp(vm.slcdCat, asgmtType);
            // Get user for selected class based on userID
            let slcdClassUser = gb.getSelectedClassUser(vm.slcdClass, userID);
            // Set userID Cookie
            vm.setCookie(vm.cookie, { userID });
            // Clear list from template
            vm.slcdAssignments = null;
            // Select previously retrieved assignments
            vm.slcdAssignments = slcdClassUser[slcdCatProp];
        },

        //
        // Select Project
        // --------------------------------------------------------------------------
        toggleProject(projectID) {
            const vm = this;
            vm.slcdProjectID = vm.slcdProjectID === projectID ? null : projectID;
            vm.setCookie(vm.cookie, { projectID: vm.slcdProjectID });
        },

        //
        // Reset all selections
        // --------------------------------------------------------------------------
        resetSelection(search = false) {
            const vm = this;
            vm.resetCookie(vm.cookie);

            // Selected class, category, and usertype
            vm.slcdClass = {};
            vm.slcdCat = {};
            vm.slcdUserType = null;

            // Search
            vm.searchUserType = '';
            if(search) {
                vm.searchStatusByUser = null;
                vm.searchStatusByTeam = null;
                vm.searchForUser = null;
                vm.searchForTeam = null;
            }

            // Selected project and assignments
            vm.slcdProjectID = null;
            vm.slcdAssignments = null;

            // Reset all classes' props
            vm.updatedClasses = _.cloneDeep(vm.classData);

            // Sort class list alpha
            vm.updatedClasses.sort((a, b) => a.class_name.localeCompare(b.class_name));
        },

        //
        // Get Data from API
        // --------------------------------------------------------------------------
        postData(postURL, request) {
            //this.slcdClass.loading = true;
            return apiRequest(postURL, request).then(res => {
                //this.slcdClass.loading = false;
                return res;
            });
        },

        //
        // Grade Menu Select dropdown
        // --------------------------------------------------------------------------
        menuGradeSelect(args) {
            const vm = this;
            const [value, classID, selectTarget] = [...args];
            switch (selectTarget) {
                case 'assignment':
                    vm.selectAssignmentType(classID, vm.slcdClass.slcdUser.id, value);
                    break;
                case 'user':
                    vm.selectAssignmentType(classID, value.id, vm.slcdClass.asgmtType);
                    break;
                default:
                    alert('Under Construction');
            }
        },
        //
        // Cookies
        // --------------------------------------------------------------------------
        assignDefinedCookie(prop, value) {
            const vm = this;
            if (typeof vm.cookie[prop] !== 'undefined') {
                vm.cookie[prop] = value;
                vm.setCookie(vm.cookie);
            }
        },
        removeDefinedCookie(prop) {
            const vm = this;
            if (typeof vm.cookie[prop] !== 'undefined') {
                vm.cookie[prop] = null;
                vm.setCookie(vm.cookie);
            }
        },
        //
        // Load Check
        // --------------------------------------------------------------------------
        loadCheck() {
            if(this.pending_user_type) {
            // Load pending from Teacher Dashboard
                this.loadPending();

            } else {
            // Load data based on cookie data
                this.loadCookies();
            }
        },
        //
        // Load Pending
        // --------------------------------------------------------------------------
        loadPending() {
            const vm = this;
            if(vm.pending_user_type === 'team') {
            // Set multiselect
                vm.searchStatusByTeam = vm.searchByStatusOptions[1];
            } else {
                vm.searchStatusByUser = vm.searchByStatusOptions[1];
            }
            // Filter data for pending
            vm.getAll(vm.pending_user_type, vm.searchByStatusOptions[1], 'status');
            // Scroll to user
            setTimeout(() => {
                const el = Array.isArray(vm.$refs['member' + vm.pending_user_id])
                    ? vm.$refs['member' + vm.pending_user_id][0]
                    : vm.$refs['member' + vm.pending_user_id];
                el.scrollIntoView({behavior: 'smooth', block: 'start', inline: 'nearest'})
            }, 200);
        },
        //
        // Load Cookies
        // --------------------------------------------------------------------------
        loadCookies() {
            const vm = this;
            if(vm.cookie.searchUserType) {
                // Fill Multiselect selection
                if(vm.cookie.searchType === 'status') {
                    vm.cookie.searchUserType === 'team'
                        ? (vm.searchStatusByTeam = vm.cookie.searchValue)
                        : (vm.searchStatusByUser = vm.cookie.searchValue)
                    vm.getAll(vm.cookie.searchUserType, vm.cookie.searchValue, vm.cookie.searchType);

                } else {
                    vm.cookie.searchUserType === 'team'
                        ? (vm.searchForTeam = vm.cookie.searchValue)
                        : (vm.searchForUser = vm.cookie.searchValue)
                    vm.getAll(vm.cookie.searchUserType, vm.cookie.searchValue, vm.cookie.searchType);
                }

            } else {
                if (!vm.cookie.classID || !vm.cookie.catID) return;
                // Check if class and category exist
                const classFound = vm.loadClassCookie();
                vm.loadUserCookie(classFound);
            }
        },
        loadClassCookie() {
            const vm = this;
            const classFound = vm.updatedClasses.find(obj => obj.id === vm.cookie.classID),
                catFound = classFound.categories.find(obj => obj.id == vm.cookie.catID);

            if (![classFound, catFound].includes(undefined)) {
                // Load data if it exists
                vm.slcdClass = classFound;
                vm.selectCategory(vm.cookie.classID, vm.cookie.catID, true);
            }
            return classFound;
        },
        loadUserCookie(cls) {
            const vm = this;
            if (vm.cookie.userType && cls !== undefined) {
                vm.selectUserType(vm.cookie.classID, vm.cookie.userType, vm.cookie.catID).then(
                    () => {
                        // Check if user exists
                        let userList = cls[vm.cookie.userType + 'List'],
                            userFound =
                                userList !== null &&
                                userList.find(obj => obj.id === vm.cookie.userID);

                        if (userFound) {
                            vm.selectAssignmentType(
                                vm.cookie.classID,
                                vm.cookie.userID,
                                vm.cookie.asgmtType
                            );
                            vm.slcdProjectID =
                                vm.cookie.asgmtType === 'Activity' ? vm.cookie.projectID : null;
                        }
                    }
                );
            }
        },

        //
        // Get All by status / userid / teamid
        // --------------------------------------------------------------------------
        getAll(userType, value, type) {
            const vm = this;
            const userList = userType + 'List';

            if(!vm.loadingCookies) {
                vm.resetSelection();
            }

            vm.searchUserType = userType;
            vm.searchStatus = type === 'status' ? value.id : 'active or graded';

            vm.setSearchCookie(userType, value, type);

            Object.keys(vm.updatedClasses).forEach(c => {
                const cls = vm.updatedClasses[c];

                // For use in updating selected class while searching. Need untouched classData to
                // have selected userType
                vm.classData[c].userType = userType;

                // Set userType
                cls.userType = userType;
                cls.asgmtType = 'Custom'; // Any asgmtType value is needed for template use

                if(type === 'status') {
                    cls[userList].map(u => {
                        let user = u;
                        user = gb.getAsgmtsByStatus(user, cls, value.id);
                        return user;
                    });

                } else {
                    cls[userList] = cls[userList].filter(obj => obj.id === value.id);
                    cls[userList].map(u => {
                        let user = u;
                        user = gb.getAsgmtsByStatus(user, cls, 'any');
                        // Set user's grade totals
                        vm.setGradeTotals(user);
                        return user;
                    });
                }

                // Update hasAssignments check
                cls.categories = gb.updateAssignmentsCheck(cls, userType);
            });

            vm.loadingCookies = false;
        },

        //
        // Set Grade Totals
        // --------------------------------------------------------------------------
        setGradeTotals(user) {
            this.searchTotalGrade = user.totalGrade;
            this.searchTotalPoints = user.totalPoints;
            this.searchTotalPercentage = user.totalPercentage;
        },

        //
        // Refresh data
        // --------------------------------------------------------------------------
        refreshData() {
            window.location.reload()
            // const vm = this;
            // vm.animate = false;
            // vm.loading = true;
            // apiRequest(`/get/gradebook/${vm.user_id}`).then(response => {
            //     vm.notify('Gradebook data refreshed.');
            //     vm.classData = response.filter(obj => obj.user_count !== 0);
            //     vm.prepClassData();
            //     if(vm.searchUserType) {
            //         vm.getAll(vm.cookie.searchUserType, vm.cookie.searchValue, vm.cookie.searchType);
            //     }
            //     vm.loading = false;
            //     vm.animate = true;
            // });
        },
        //
        //
        // --------------------------------------------------------------------------
        setSearchCookie(userType, value, type) {
            const searchUserType = userType;
            const searchValue = JSON.parse(JSON.stringify(value))
            const searchType = type;
            this.setCookie(this.cookie, { searchUserType, searchValue, searchType });
        },
        //
        // Determine class column width
        // --------------------------------------------------------------------------
        classColWidth(cls) {
            return (
                (this.searchUserType === cls.userType) ||
                (this.slcdClass.id === cls.id) ||
                (this.updatedClasses.length === 1)
            ) ? 'col-xl-12' : 'col-xl-6';
        },
        //
        // Determine if class has assignments
        // --------------------------------------------------------------------------
        classHasAssignments(cls) {
            return cls.categories.reduce((total, cat) =>
                total + cat.categoryHasAsgmts, 0);
        },

        //
        // Filter method for get all multiselect
        // --------------------------------------------------------------------------
        searchStatusSelect(event, userType) {
            const vm = this;
            vm.searchForUser = null;
            vm.searchForTeam = null;
            userType === 'team' && (vm.searchStatusByUser = null);
            userType === 'user' && (vm.searchStatusByTeam = null);
            vm.getAll(userType, event, 'status');
        },
        searchUserSelect(event, userType) {
            const vm = this;
            vm.searchStatusByUser = null;
            vm.searchStatusByTeam = null;
            userType === 'team' && (vm.searchForUser = null);
            userType === 'user' && (vm.searchForTeam = null);
            vm.getAll(userType, event, 'userID');
        },
        //
        // Download CSV
        // --------------------------------------------------------------------------
        downloadCSV(id) {
            let data = [],
                cls = this.updatedClasses.find(obj => obj.id === id); // Get class
            const userList = cls.userList; // Get class user list
            const setRowData = (user, assignment, propertyName) => { // Row data function
                return {
                    first_name: user.first_name,
                    last_name: user.last_name,
                    username: user.email,
                    category: capitalizeWords(propertyName),
                    assignment: assignment.title || assignment.label,
                    grade: assignment.grade,
                    date_graded: formatDate(assignment.updated_at)
                }
            }

            userList.forEach(u => { // Go through each user
                Object.keys(u).forEach(prop => {
                    if(Array.isArray(u[prop])) { // Go through props that are category arrays
                        let assignments = [];
                        if(prop === 'activity') {
                            // Projects -> Worksheets
                            assignments = u[prop]
                                .map(p => p.worksheets
                                    .map(w => w.grade !== null && p.locked
                                        ? setRowData(u, w, prop)
                                        : {}
                                    ).filter(obj => Object.keys(obj).length !== 0) // Filter out empty
                                ).reduce((o, i) => o.concat(i),[]); // Flatten

                        } else {
                            // Other assignments
                            assignments = u[prop].map(a => a.grade !== null
                                ? setRowData(u, a, prop)
                                : {}
                            )
                        }
                        data.push(assignments.filter(obj => Object.keys(obj).length !== 0));
                    }
                });
            });
            // Flatten data array
            data = data.reduce((o, i) => o.concat(i), []);

            // Create CSV and download
            data.length
                ? downloadCSV(data, cls.class_name.replace(' ', '_') + '_gradebook.csv')
                : this.notify(`There are no grades to export for ${cls.class_name}.`, 'danger');
        }
    }
};
</script>
