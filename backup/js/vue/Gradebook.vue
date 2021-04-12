<template>
    <div class="gradebook">
        <ul v-if="compClasses.length > 0" class="filter-all">
            <li class="filter-all-item">
                <strong><span class="text-graded">Reset:</span></strong>
                <a href="#" @click="resetAllSelections">All selections</a>
            </li>
            <li class="filter-all-item">
                <strong><span class="text-graded">All graded:</span></strong>
                <a href="#" @click="getAll('team', 'graded')">Teams</a>
                <a href="#" @click="getAll('user', 'graded')">Individuals</a>
            </li>

            <li class="filter-all-item">
                <strong><span class="text-pending">All pending:</span></strong>
                <a href="#" @click="getAll('team', 'pending')">Teams</a>
                <a href="#" @click="getAll('team', 'pending')">Individuals</a>
            </li>
        </ul>
        <hr v-if="compClasses.length > 0" class="mt-2 mb-2">

        <transition
            name="message"
            enter-active-class="animated bounceInLeft"
            leave-active-class="animated fadeOut">
            <div
                v-if="displayMessage"
                :class="messageColor"
                class="gradebook-message">
                {{ message }}
            </div>
        </transition>

        <div v-show="loading" class="loading-circle mt-5 mb-5"></div>

        <div v-if="compClasses.length > 0">
        <transition-group name="row" class="row-transition d-flex row">
            <!-- Class -->
            <div
                v-for="cls in compClasses"
                :key="cls.id"
                :class="classColWidth(cls)"
                class="col-xs-12 row-animate">
                <h4 class="mt-4">
                    {{ cls.class_name | capitalize }}
                </h4>
                <!-- Accordion -->
                <div v-if="(cls.user_count > 0)" class="accordion">
                    <!-- Card -->
                    <template
                        v-for="cat in cls.categories">
                        <div
                            v-if="!showAllUsers || (showAllUsers && cat.hasAssignmentsForClass === cls.id)"
                            :key="cat.id"
                            class="card"
                            :class="[(showAllUsers === cls.userType || (slcdCat.id === cat.id && slcdClass.id === cls.id)) && 'collapse-selected']">
                        <!-- Card Header -->
                        <div
                            class="card-header"
                            @click.self="selectCategory(cls.id, cat.id)">
                            <div class="card-toggler">
                                <span
                                    v-if="slcdCat.id === cat.id && slcdClass.id === cls.id"
                                    class="card-icon align-self-start"
                                    @click.prevent.stop="resetSelection()">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                                <span
                                    v-else
                                    v-tooltip:open="'Click to open or reset selections for a selected class.'"
                                    class="card-icon align-self-start"
                                    @click="selectCategory(cls.id, cat.id)">
                                    <i class="fas fa-bars"></i>
                                </span>
                                <span
                                    v-tooltip:categories="'These are the categories chosen in settings.'"
                                    class="card-text"
                                    @click="selectCategory(cls.id, cat.id)">
                                    {{ cat.name | capitalize }}
                                </span>
                            </div>
                            <!-- Grading  Menu -->
                            <div
                                v-if="!showAllUsers && cls.userType && (slcdCat.id === cat.id) && (slcdClass.id === cls.id)"
                                class="menu-grade">
                                <span v-if="cls.team_count && cat.name === 'Classwork'">
                                    <span class="menu-grade-label">Grade by:</span>
                                    <i
                                        :class="cls.userType == 'team' && 'selected'"
                                        class="fas fa-users"
                                        @click.prevent.stop="selectUserType(cls.id, 'team', cat.id)"></i>
                                    <i
                                        :class="cls.userType == 'user' && 'selected'"
                                        class="fas fa-user"
                                        @click.prevent.stop="selectUserType(cls.id, 'user', cat.id)"></i>
                                </span>
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
                        <div v-if="showAllUsers === cls.userType || (slcdCat.id === cat.id && slcdClass.id === cls.id)">
                            <!-- Card Body -->
                            <div class="card-body">
                                <div clas="card">
                                    <!-- User Type -->
                                    <ul
                                        v-if="!cls.userType && !showAllUsers"
                                        class="list-group list-group-horizontal">
                                        <li
                                            v-if="cls.team_count"
                                            class="list-group-item list-group-item-button"
                                            @click="selectUserType(cls.id, 'team', cat.id)">
                                            <span :class="cls.userType === 'team' && 'selected'">
                                                <i class="fas fa-users mr-2"></i>Team
                                                <span class="ml-2 badge badge-pill badge-secondary">{{ cls.team_count }}</span>
                                            </span>
                                        </li>
                                        <li class="list-group-item list-group-item-button" @click="selectUserType(cls.id, 'user', cat.id)">
                                            <span :class="cls.userType === 'user' && 'selected'">
                                                <i class="fas fa-user mr-2"></i>Individuals
                                                <span class="ml-2 badge badge-pill badge-secondary">{{ cls.user_count }}</span>
                                            </span>
                                        </li>
                                    </ul>
                                    <!-- User List -->
                                    <div v-if="showAllUsers || !cls.asgmtType">
                                        <div v-show="cls.loading" class="loading-circle"></div>
                                        <!-- Assignment Type Team -->
                                        <ul v-if="cls.userType === 'team' && cls.teamList && !cls.loading" class="list-group">
                                            <template v-if="!showAllUsers && cat.name === 'Classwork'">
                                            <li
                                                v-for="team in cls.teamList"
                                                :key="team.id"
                                                class="list-group-item"
                                                @click="selectAssignmentType(cls.id, team.id, 'Activity')">
                                                {{ team.team_name }}
                                                <span v-memberlist="team.members.join('<br>')" class="ml-2 badge badge-pill badge-secondary">{{ team.members.length }}</span>
                                                <!-- <div class="list-group-item-atype">
                                                    <span class="link" @click="selectAssignmentType(cls.id, team.id, 'Activity')">Activity Sheets</span>
                                                    <span class="link" @click="selectAssignmentType(cls.id, team.id, 'Custom')">Custom Assignments</span>
                                                </div> -->
                                            </li>
                                            </template>
                                            <template v-else>
                                                <li
                                                    v-for="team in cls.teamList"
                                                    :key="team.id"
                                                    class="list-group-item"
                                                    @click="selectAssignmentType(cls.id, team.id, 'Custom')">
                                                    {{ team.team_name }}
                                                    <span
                                                        v-if="!showAllUsers"
                                                        v-memberlist="team.members.join('<br>')"
                                                        class="ml-2 badge badge-pill badge-secondary">
                                                        {{ team.members.length }}
                                                    </span>
                                                    <grade-list
                                                        v-if="showAllUsers && ![null, undefined].includes(team['activity']) && team['activity'].length > 0 && cat.name === 'Classwork'"
                                                        :cls="cls"
                                                        :assignments="team['activity']"
                                                        :projectid="1"
                                                        :show-all="showAllUsers"
                                                        class="mb-2"
                                                        @project="toggleProject"
                                                        @clear="clearAssignment"
                                                        @grade="gradeAssignment" />
                                                    <!-- <grade-list
                                                        v-if="showAllUsers && ![null, undefined].includes(team[cat.name.toLowerCase()]) && team[cat.name.toLowerCase()].length > 0"
                                                        :cls="cls"
                                                        :assignments="team[cat.name.toLowerCase()]"
                                                        :show-all="showAllUsers"
                                                        @clear="clearAssignment"
                                                        @grade="gradeAssignment" /> -->
                                                </li>
                                            </template>
                                        </ul>
                                        <!-- Assignment Type User -->
                                        <ul v-if="cls.userType === 'user' && cls.userList && !cls.loading" class="list-group">
                                            <template v-if="!showAllUsers && cat.name === 'Classwork'">
                                            <li
                                                v-for="user in cls.userList"
                                                :key="user.id"
                                                class="list-group-item">
                                                {{ user.fullname }}
                                                <div class="list-group-item-atype">
                                                    <span class="link" @click="selectAssignmentType(cls.id, user.id, 'Activity')">Activity Sheets</span>
                                                    <span class="link" @click="selectAssignmentType(cls.id, user.id, 'Custom')">Custom Assignments</span>
                                                </div>
                                            </li>
                                            </template>
                                            <template v-else>
                                            <li
                                                v-for="user in cls.userList"
                                                :key="user.id"
                                                class="list-group-item"
                                                @click="selectAssignmentType(cls.id, user.id, 'Custom')">
                                                {{ user.fullname }}
                                                <grade-list
                                                    v-if="showAllUsers && ![null, undefined].includes(user['activity']) && user['activity'].length > 0 && cat.name === 'Classwork'"
                                                    :cls="cls"
                                                    :assignments="user['activity']"
                                                    :show-all="showAllUsers"
                                                    :projectid="1"
                                                    class="mb-2"
                                                    @project="toggleProject"
                                                    @clear="clearAssignment"
                                                    @grade="gradeAssignment" />
                                                <grade-list
                                                    v-if="showAllUsers && ![null, undefined].includes(user[cat.name.toLowerCase()]) && user[cat.name.toLowerCase()].length > 0"
                                                    :cls="cls"
                                                    :assignments="user[cat.name.toLowerCase()]"
                                                    :show-all="showAllUsers"
                                                    @clear="clearAssignment"
                                                    @grade="gradeAssignment" />
                                            </li>
                                            </template>
                                            <!-- End Assignment Type -->
                                        </ul>
                                    </div>
                                    <!-- End User List -->
                                    <div v-show="cls.loading && cls.asgmtType" class="loading-circle"></div>
                                    <!-- Selected Assignment List Activity -->
                                    <grade-list
                                        v-if="slcdAssignments !== null"
                                        :cls="cls"
                                        :assignments="slcdAssignments"
                                        :show-all="showAllUsers"
                                        :projectid="slcdProjectID"
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
        <div v-else-if="!loading">
            <div v-if="showAllUsers">
                {{ `No results for ${showAllUsers}s with ${showAllStatus} assignments.` }}
            </div>
            <div v-else>
                Start by <a href="/edit/class">adding classes and students.</a>
            </div>
        </div>

        <grade-assignment
            v-if="gradingAssignment"
            v-closeEsc
            :assignment="gradingAssignment"
            :user="gradingUser"
            :classinfo="cookie"
            :projectid="slcdProjectID"
            class="gb-assignment"
            @close="closeAssignment"
            @send-back="sendBack"
            @submit-grade="submitGrade">
        </grade-assignment>
        <!-- <pre class="small">{{ compClasses }}</pre> -->
    </div>
    <!-- Gradebook -->
</template>

<script>
import { changeLayout, findObjMoveTop } from '../functions/utils';
import * as gb from '../functions/gradebookUtils';
import lg from '../functions/logging';
import apiRequest from '../functions/apiRequest';

export default {
    name: 'Gradebook',

    directives: {
        closeEsc: {
            bind: (el, binding, vnode) => {
                const close = e => e.code === 'Escape' && vnode.context.closeAssignment();
                document.addEventListener('keydown', e => close(e));
                el.$destroy = () => el.removeEventListener('keydown', close);
            },
            unbind: el => el.$destroy()
        },
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

    // components: {
    //     GradeItem: () => import('./GradeItem.vue'),
    //     GradeMenuForm: () => import('./GradeMenuForm.vue'),
    //     GradeAssignment: () => import('./GradeAssignment.vue')
    // },

    props: {
        classes: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            classData: this.classes.filter(obj => obj.user_count !== 0),
            displayMessage: false,
            gradingAssignment: null,
            message: null,
            gradingUser: null,
            loading: false,
            popoverDialogue: null,
            slcdProjectID: null,
            slcdAssignments: null,
            slcdCat: {},
            slcdClass: {},
            slcdUserType: null,
            showAllUsers: '',
            showAllStatus: '',
            getAllCached: false
        }
    },

    computed: {
        compClasses() {
            let clses = this.classData;

            if (this.showAllUsers) {
                const userList = this.showAllUsers + 'List';
                const getCatAsgmtsByStatus = (userCatAssignments, status) => {
                    return status === 'graded'
                        ? userCatAssignments.filter(obj => obj.grade !== null)
                        : userCatAssignments.filter(obj => !obj.grade && obj.status);
                }
                const hasGradedWorksheets = proj => proj.worksheets.some(obj => obj.grade !== null);
                const getGradedWorksheets = proj => {
                    const project = proj;
                    project.worksheets = project.worksheets.filter(obj => obj.grade !== null);
                    return project;
                };
                const hasSentWorksheets = proj => proj.worksheets.some(obj => !obj.grade && obj.status);
                const getSentWorksheets = proj => {
                    const project = proj;
                    project.worksheets = project.worksheets.filter(obj => !obj.grade && obj.status)
                    return proj;
                };

                clses = clses
                    .filter(cls => cls[userList] !== null)
                    .map(c => {
                        const cls = c;
                        cls[userList].map(u => {
                            const user = u;

                            // Filter Custom assignments
                            for (const cat of cls.categories) {
                                const category = cat.name.toLowerCase();
                                if (![null, undefined].includes(user[category])) {
                                    user[category] = getCatAsgmtsByStatus(
                                        user[category], this.showAllStatus
                                    )
                                    cat.hasAssignmentsForClass = user[category].length > 0 ? cls.id : false;
                                }
                            }

                            // Filter worksheets
                            if (![null, undefined].includes(u['activity']) && u['activity'].length > 0) {

                                user['activity'] = this.showAllStatus === 'graded'
                                    ? u['activity']
                                        .filter(proj => hasGradedWorksheets(proj))
                                        .map(proj => getGradedWorksheets(proj))

                                    : u['activity']
                                        .filter(proj => hasSentWorksheets(proj))
                                        .map(proj => getSentWorksheets(proj))

                                cls.categories
                                    .find(obj => obj.name === 'Classwork')
                                    .hasAssignmentsForClass = user['activity'].length > 0 ? cls.id : false;
                            }

                            return user;
                        });
                        return cls;
                    });
            }


            return clses;
        }
    },
    // else {
    //     this.showMessage(`
    //         There are no ${this.showAllStatus}
    //         assignments for ${this.showAllUsers}s.
    //     `, 'danger')
    // }
    watch: {
        slcdAssignments(val) {
            if(val) {
                const projectID = gb.isSingleProject(val)
                projectID && this.openProject(projectID)
            }
        }
    },

    created() {
        const vm = this;
        vm.cookie = vm.getCookie();

        vm.showAllCached = {
            team: {
                pending: false,
                graded: false
            },
            user: {
                pending: false,
                graded: false
            }
        }

        // Add needed reactive props to classes array
        vm.classData = vm.classData.map(c => {
            const cls = c;
            Vue.set(cls, 'asgmtType', null);
            Vue.set(cls, 'filterType', 'All');
            Vue.set(cls, 'loading', false);
            Vue.set(cls, 'slcdUser', null);
            Vue.set(cls, 'teamList', null);
            Vue.set(cls, 'userList', null);
            Vue.set(cls, 'userType', null);
            return cls
        })

        // Exclude classes that have no students
        //vm.classData = vm.classData.filter(obj => obj.user_count !== 0);

        // Set tooltip props
        vm.setupTooltipCookie(vm.cookie, {
            reset: 0,
            open: 0,
            categories: 0
        });

        // Load data based on cookie data
        vm.loadCookies();
    },

    methods: {
        openProject(projectID) {
            this.slcdProjectID = projectID;
            this.setCookie(this.cookie, { projectID });
        },
        //
        // Grade Assignment Methods
        // --------------------------------------------------------------------------
        gradeAssignment(asgmtID) {
            const vm = this;
            // Assign selected user to grading user for use in grade assignment component
            vm.gradingUser = vm.classData.find(obj => obj.id === vm.slcdClass.id).slcdUser;

            vm.gradingAssignment = vm.extractAssignment(asgmtID);

            changeLayout('full');
            vm.gradeMessage = null;
        },

        // Clear data from assignment
        clearAssignment(asgmtID) {
            if (asgmtID === 'cancel') return;
            const vm = this;
            const assignment = vm.extractAssignment(asgmtID);

            vm.postData('/api/delete/gradebook', { id: assignment.grade_id }).then(() => {
                vm.showMessage('Grade has been cleared.', 'danger');
                // Remove grade
                assignment.grade = null;
                assignment.grade_id = null;
            });
        },

        // Close assignment overlay
        closeAssignment() {
            this.gradingAssignment = null;
            changeLayout();
        },
        // Send back
        sendBack(message) {
            const vm = this;
            const postURL = '/api/save/gradebook';
            const request = {
                id: vm.gradingAssignment.grade_id,
                message: message,
                sendback: 1
            };
            vm.postData(postURL, request).then(() => {
                vm.showMessage('Grade has been sent back to student.');
                this.closeAssignment();
            });
        },

        // Submit grade
        submitGrade(data) {
            const vm = this;
            const postURL = data.update ? '/api/update/gradebook' : '/api/save/gradebook';
            const aType = gb.getPHPAssignmentType(vm.slcdClass.asgmtType); // James php
            const asgmtID = vm.gradingAssignment.id;
            let message = 'Grade has been saved.';
            let request = {};

            // Update grade
            if (data.update) {
                request = {
                    id: vm.gradingAssignment.grade_id,
                    points: +data.grade,
                    message: data.message
                };
                if(vm.gradingAssignment.message === data.message) {
                    request.noemail = 1;
                }
            // Or Add grade
            } else {
                request = {
                    points: +data.grade, // grade
                    type: aType, // assignmentType
                    type_id: asgmtID, // assignment ID
                    user_id: vm.cookie.userID, // team/student ID
                    category_id: vm.slcdCat.id, // assignment category ID
                    project_id: vm.slcdProjectID, // project ID
                    message: data.message // message
                };
            }

            // Update layout if not adding message, etc
            if (!data.continue) {
                changeLayout();
                vm.gradingAssignment = null;
            }

            if((data.grade == vm.gradingAssignment.grade || !data.grade) && data.message) {
                message = 'Message has been sent.'
            }

            vm.postData(postURL, request).then(result => {
                vm.showMessage(message);
                const assignment = vm.extractAssignment(asgmtID);
                // Assign grade props
                assignment.grade = +data.grade;
                data.message && (assignment.message = data.message);
                result.id && (assignment.grade_id = result.id);
            });
        },
        //
        // Select Class
        // --------------------------------------------------------------------------
        selectClass(classID) {
            const vm = this;
            // Assign selected class and move it to the top and scroll up
            vm.slcdClass = findObjMoveTop(vm.classData, classID);
            lg.log('%cselectClass > selected class', 'color:#2D8CFF', vm.slcdClass);
        },

        //
        // Select Assignment Category Type
        // --------------------------------------------------------------------------
        selectCategory(classID, catID, loadingCookie = false) {
            const vm = this;
            vm.showAllUsers = '';

            // If only collapsing, do nothing
            if (vm.isCatCollapsing(classID, catID)) return;

            // Select Class
            vm.selectClass(classID);

            // Assign selected category
            vm.slcdCat = vm.slcdClass.categories.find(obj => obj.id == catID);
            vm.slcdClass.slcdCat = vm.slcdClass.categories.find(obj => obj.id == catID);

            // Assign Category Cookie
            vm.assignDefinedCookie('catID', catID);

            // Return if loading cookies
            if (loadingCookie) return;

            lg.log(
                '%cswitching categories > selected user',
                'color:#529df8',
                vm.slcdClass.slcdUser
            );

            // Check if selected class has selected user
            if (vm.checkSelectedUser(classID, catID)) return;

            // Check if an assignment type switch is needed
            vm.checkAssignmentType(classID, catID);
        },
        checkSelectedUser(classID, catID) {
            const vm = this;
            // Select user type when switching classes if selected user in class doesn't exist

            if ([undefined, null].includes(vm.slcdClass.slcdUser)) {
                // Remove userID cookie if switching classes
                vm.removeDefinedCookie('userID');

                // If there are no teams head straight to users/students
                let userType = !vm.slcdClass.team_count ? 'user' : vm.slcdUserType;

                // If not classwork, head straight to user
                userType = vm.slcdCat.name !== 'Classwork' ? 'user' : vm.slcdUserType;

                vm.selectUserType(classID, userType, catID);
                return true;
            }
            return false;
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
            const postURL = `/api/show/gradebook/${classID}/${vm.user_id}`;
            const userTypeList = userType + 'List';

            // Set Cookie
            vm.setCookie(vm.cookie, { classID, catID, userType });

            // Assign userType and catID arguments to targetted class
            // as selected values for later use
            vm.slcdClass.userType = userType;

            // Test remembering userType across classes instead of remembering per class
            vm.slcdUserType = userType;

            // Remove selected asgmtType value from targetted class so that user selection
            // shows up in template
            vm.slcdClass.asgmtType = null; // Todo: decide whether to remember assignment by class or cat
            //vm.slcdCat.asgmtType = null;

            // Get data if a class's user type list haven't previously been retrieved
            if (vm.slcdClass[userTypeList] === null) {
                // Return promise for use in loading data from cookies and switching categories
                return vm.postData(postURL, { user_type: userType }).then(result => {
                    // Setup user lists
                    vm.slcdClass[userTypeList] = gb.setupUserList(result, userType);
                    return result;
                });
            }
        },

        //
        // Select Assignment and User Type
        // --------------------------------------------------------------------------
        selectAssignmentType(classID, userID, asgmtType) {
            const vm = this;

            // Update target classes selcted assignment type
            vm.slcdClass.asgmtType = asgmtType;
            // console.log(asgmtType)
            // this.classData.filter(obj => obj.id === classID)[0].asgmtType = asgmtType;

            // Update target class's selected user
            vm.slcdClass.slcdUser = vm.slcdClass[vm.slcdClass.userType + 'List'].find(
                obj => obj.id === userID
            );

            // Set Cookie
            vm.setCookie(vm.cookie, { asgmtType });

            // Get assignments
            vm.getAssignments(classID, userID, asgmtType);
        },

        //
        // Get Assignments from API
        // --------------------------------------------------------------------------
        getAssignments(classID, userID, asgmtType) {
            const vm = this;
            const postURL = `/api/show/gradebook/${classID}/${vm.user_id}`;
            const aType = gb.getPHPAssignmentType(asgmtType); // James wants 1 or 2 for req
            const slcdCatProp = gb.getSelected.catProp(vm.slcdCat, asgmtType);

            // Get user for selected class based on userID
            let slcdClassUser = gb.getSelected.classUser(vm.slcdClass, userID);

            const request = {
                assignment_type: aType,
                category: vm.slcdCat.id,
                user_type: vm.slcdClass.userType,
                user_id: userID
            };

            // Set userID Cookie
            vm.setCookie(vm.cookie, { userID });

            // Clear list from template
            vm.slcdAssignments = null;

            // Get data if targeted user's assignments haven't previously been retrieved
            if ([undefined, null].includes(slcdClassUser[slcdCatProp])) {
                vm.postData(postURL, request).then(result => {
                    // Assign retrieved data to selected user's assignments
                    slcdClassUser[slcdCatProp] = result;
                    // Add to selected assignments for template use
                    vm.slcdAssignments = slcdClassUser[slcdCatProp];
                    lg.log('%cpost results user', 'color: orange', slcdClassUser);
                });
            } else {
                // Select previously retrieved assignments
                vm.slcdAssignments = slcdClassUser[slcdCatProp];
                lg.log('%ccached results user', 'color: lightgreen', slcdClassUser);
            }
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
        // Reset Selection
        // --------------------------------------------------------------------------
        resetSelection() {
            const vm = this;
            vm.resetCookie(vm.cookie);
            // Remove selected user and userType from selected class
            // before assigning empty object to collapse category
            // vm.slcdClass.slcdUser = null;
            // vm.slcdClass.userType = null;
            vm.slcdClass = {};
            // Remove global userType selection
            vm.slcdUserType = null;
        },

        //
        // Reset all selections
        // --------------------------------------------------------------------------
        resetAllSelections() {
            const vm = this;
            vm.showAllUsers = false;
            // Sort class list alpha
            vm.classData.sort((a, b) => a.class_name.localeCompare(b.class_name));

            // Reset cookies, selected class, etc
            vm.resetSelection();

            // Reset all classes' props
            Object.keys(vm.classes).forEach(c => {
                const cls = vm.classes[c];
                cls.asgmtType = null;
                cls.filterType = 'All';
                cls.slcdUser = null;
                //cls.teamList = null;
                //cls.userList = null;
                cls.userType = null;
            });
        },

        //
        // Get Data from API
        // --------------------------------------------------------------------------
        postData(postURL, request) {
            this.slcdClass.loading = true;
            return apiRequest(postURL, request).then(res => {
                this.slcdClass.loading = false;
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
        // Show Message
        // --------------------------------------------------------------------------
        showMessage(msg, color = 'success', duration = 8000) {
            const vm = this;
            const colors = {
                success: 'text-secondary',
                danger: 'text-danger',
                highlight: 'text-primary'
            };
            vm.displayMessage = true;
            vm.message = msg;
            vm.messageColor = colors[color];
            setTimeout(() => (vm.displayMessage = false), duration);
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
        // Load Cookies
        // --------------------------------------------------------------------------
        loadCookies() {
            const vm = this;
            if (!vm.cookie.classID || !vm.cookie.catID) return;
            // Check if class and category exist
            const classFound = vm.loadClassCookie();
            vm.loadUserCookie(classFound);
        },
        loadClassCookie() {
            const vm = this;
            const classFound = vm.classData.find(obj => obj.id === vm.cookie.classID),
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
        // Extract assignment by ID
        // --------------------------------------------------------------------------
        extractAssignment(id) {
            const vm = this;
            const assignments =
                vm.slcdClass.asgmtType === 'Activity'
                    ? vm.slcdAssignments.find(obj => obj.project_id === vm.slcdProjectID).worksheets
                    : vm.slcdAssignments;

            return assignments.find(obj => obj.id === id);
        },

        //
        // Get All graded
        // --------------------------------------------------------------------------
        getAll(userType, status) {
            const vm = this;
            //lg.logging = false;
            vm.resetAllSelections();
            vm.showAllUsers = userType;
            vm.showAllStatus = status;

            if(!vm.getAllCached) {
                vm.loading = true;
                apiRequest(`/api/get/gradebook/${vm.user_id}`, {}).then(response => {
                    vm.classData = response.filter(obj => obj.user_count !== 0);
                    vm.classData = vm.classData.map(c => {
                        const cls = c;
                        cls.userType = userType;
                        cls.asgmtType = 'Custom';
                        return cls
                    })

                    const userList = userType + 'List';
                    vm.classData = vm.classData
                        .filter(cls => cls[userList] !== null)
                        .map(c => {
                            const cls = c;
                            cls[userList] = gb.setupUserList(cls[userList], userType)
                            return cls;
                        });

                    // console.log(vm.classData)
                    vm.slcdAssignments = null;
                    vm.getAllCached = true;
                    vm.loading = false;
                });
            }


            // const loadUp = new Promise(res => {
            //     function getUserAssignmentData() {
            //         vm.showMessage('Loading...', 'success', 10000);

            //         vm.classData.reduce(async (promise, cls) => {

            //             await promise;
            //             cls.userType = userType;
            //             cls.asgmtType = 'Custom'; // this neccessity needs to be corrected

            //             if(userType === 'team' && !cls.team_count) {
            //                 return
            //             } else {

            //                 gb.loadAssignments(
            //                     cls.categories, cls.id, userType, 'Custom', vm.user_id
            //                 ).then(result => {
            //                     cls[userType + 'List'] = result;
            //                     res();
            //                     vm.showAllCached[userType][status] = true;
            //                     vm.$forceUpdate();
            //                 })

            //             }
            //         }, Promise.resolve());
            //     }

            //     function getUserCachedData() {
            //         vm.classData = vm.classData.map(c => {
            //             const cls = c;
            //             cls.userType = userType;
            //             cls.asgmtType = 'Custom';
            //             return cls
            //         })
            //         res();
            //     }

            //     // Retrieve data from api if not yet available
            //     !vm.showAllCached[userType][status] ? getUserAssignmentData() : getUserCachedData();
            // })

            // loadUp.then(() => {
            //     console.log(vm.classData)
            //     vm.showAllUsers = userType;
            //     vm.showAllStatus = status;
            //     vm.slcdAssignments = null;
            //     //vm.$forceUpdate();
            // });
        },

        classColWidth(cls) {
            return (
                (this.showAllUsers === cls.userType) ||
                (this.slcdClass.id === cls.id) ||
                (this.compClasses.length === 1)
            ) ? 'col-xl-12' : 'col-xl-6';
        }
    }
};
</script>
<style lang="scss" scoped>
</style>
