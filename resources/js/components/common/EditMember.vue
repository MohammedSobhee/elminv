<template>
<div>
    <!-- Search enabled -->
    <div v-if="enable_search" class="row mb-2">
        <div v-if="enable_search && updatedUsers.length >= 5" class="col">
            <div class="form-inline">
                <input
                    v-model="searchCriteria"
                    type="text"
                    placeholder="Filter Results"
                    class="form-control form-control-xs">
                <button
                    v-if="isSearching"
                    type="button"
                    class="btn btn-xs btn-light ml-2"
                    @click="reset">
                    Reset Filter
                </button>
            </div>
        </div>
        <div v-if="totalUsers > resultsPerPage" class="col text-right">
            <small class="text-muted small">Results per page:</small>
            <multiselect
                v-model="resultsPerPage"
                :searchable="false"
                :options="resultsOptions"
                :close-on-select="true"
                :show-labels="false"
                class="multiselect-xs d-inline-block w-auto">
            </multiselect>
        </div>
    </div>
    <!-- Member list -->
    <div class="accordion">
        <div
            v-for="(user, i) in currentUsers"
            :key="user.id"
            class="card accordion-animate"
            :class="(selectedMember === user.id && canEdit(user)) && 'collapse-selected'">
            <div
                :ref="'student' + user.id"
                class="card-header"
                :class="!canEdit(user) && 'card-disabled' || 'cursor-pointer'"
                @click="selectMember(user.id)">
                <div class="row">
                    <div
                        class="col-9 collapse-control"
                        :class="[
                            (isAdmin || isSchoolAdmin) ? 'col-12' : 'col-9',
                            (user.edited || user.removed || user.assigned) && 'font-italic'
                        ]">
                        <!-- User Icon -->
                        <span class="card-icon align-self-start d-none d-lg-inline-block">
                            <i
                                v-btooltip="{delay: 100, title: isAdmin ? user.id : ''}"
                                class="fas"
                                :class="[
                                    user.assigned ? 'text-secondary fa-user-plus' : 'fa-user',
                                    user.removed ? 'text-danger fa-user-minus' : 'fa-user'
                                ]">
                            </i>
                        </span>
                        <!-- Admin / School Admin Only -->
                        <div v-if="isAdmin || isSchoolAdmin" class="d-inline-block" :style="isSchoolAdmin ? 'width:94%' : 'width:96%'">
                            <div class="row">
                                <div :class="(isSchoolAdmin ? 'col-md-5' : 'col-md-4')">
                                    <span
                                        v-if="['student', 'teacher', 'assistant-teacher'].includes(user.role.slug) && user.classes.length"
                                        v-btooltip="100"
                                        :title="user.classes.length ? user.classes.map(c => c.class_name).join(', ') : 'No Classes'">
                                        {{ user.fullname }}
                                    </span>
                                    <span v-else>{{ user.fullname }}</span>
                                    <span v-if="!isSchoolAdmin" class="ml-2 small text-muted">(ID: {{ user.id }})</span>
                                    <span v-if="!user.status" class="small text-gray">(Deactivated)</span>
                                    <span v-if="reactivated === user.id" class="text-secondary small mr-2 animated slower fadeOut">Reactivated</span>
                                    <span v-if="deactivated === user.id" class="text-secondary small mr-2 animated slower fadeOut">Deactivated</span>
                                </div>
                                <div v-if="!isSchoolAdmin" class="col-md-4">
                                    <a v-if="!editing_school" :href="`/eduadmin/edit/school/${user.school_id}/`" @click.stop>{{ user.school.name }}</a>
                                    <span v-else>{{ user.school.name }}</span>
                                    <span v-if="!user.school.status" class="text-muted">(Deactivated)</span>
                                </div>
                                <div :class="(isSchoolAdmin ? 'col-md-5' : 'col-md-2')">
                                    {{ user.can_school_admin && user.role.name === 'Teacher' ? 'Hybrid' : '' }}
                                    {{ user.role.name }}
                                </div>
                                <div v-if="user_id !== user.id && !['admin', 'developer', 'manager'].includes(user.role.slug)" class="col-md-2 text-right">
                                    <a :href="`/dashboard/loginas/${user.id}`" class="btn btn-s btn-light" @click.stop>Run as...</a>
                                </div>
                            </div>
                        </div>
                         <!-- Teacher / Assistant Teacher Only -->
                        <span v-else class="card-text">
                            <span v-if="user.role.id === 7">Assistant Teacher:</span>
                            <span v-if="user.role.id === 3">Teacher:</span>
                            {{ user.fullname }}
                            <span v-if="reactivated === user.id" class="text-secondary small mr-2 animated slower fadeOut">Reactivated</span>
                            <span v-if="deactivated === user.id" class="text-secondary small mr-2 animated slower fadeOut">Deactivated</span>
                        </span>
                    </div>
                    <!-- Teacher / Assistant Teacher Only -->
                    <div v-if="!isAdmin && !isSchoolAdmin" class="col-3 text-right card-status">
                        <!-- Updated assignment status -->
                        <span
                            v-show="user.edited || user.removed || user.assigned"
                            class="card-edited">
                            <span v-show="user.assigned" class="text-secondary ml-2">
                                Assigned
                                <i class="fas fa-plus small"></i>
                            </span>
                            <span v-show="user.edited" class="text-secondary ml-2">
                                Edited
                                <i class="fas fa-check small"></i>
                            </span>
                            <span v-show="user.removed" class="text-danger ml-2">
                                Removed
                                <i class="fas fa-minus small"></i>
                            </span>
                        </span>
                        <!-- Login as... -->
                        <a
                            v-if="user_role !== 'student' && user.isAssigned && !(user.role.slug === 'assistant-teacher' && user_role === 'assistant-teacher')"
                            :href="`/dashboard/loginas/${user.id}`"
                            class="btn btn-s btn-light"
                            @click.stop>Run as...</a>
                        <!-- Assign to class -->
                        <div v-if="!user.isAssigned">
                            <input
                                :id="'assign'+user.id"
                                name="add"
                                type="checkbox"
                                @click.stop
                                @change="updateMemberAssignment(user.id, $event)" />
                            <label :for="'assign'+user.id" class="mb-0 text-primary" @click.stop>
                                Assign<span class="mobile-hide"> to this class</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <collapse>
                <!-- Edit user's data -->
                <div
                    v-show="selectedMember === user.id && canEdit(user)"
                    :key="user.id">
                    <div class="card-body">
                        <form class="form-label-status" @submit.prevent>
                        <div v-if="isAdmin" class="row mb-3">
                            <div class="col form-group">
                                <label class="small form-control-label label-status" :for="`role${user.id}`">
                                    Change role
                                </label>
                                <span class="text-danger validation-message">
                                    {{ validationUsers.role }}
                                </span>
                                <multiselect
                                    :id="user.id"
                                    v-model="role"
                                    :searchable="false"
                                    :options="userRoles"
                                    label="name"
                                    track-by="id"
                                    :close-on-select="true"
                                    :show-labels="false"
                                    :placeholder="'Select role'"
                                    class="multiselect-sm d-inline-block"
                                    @select="updateRole">
                                    <template
                                        v-for="slotName in ['option', 'singleLabel']"
                                        :slot="slotName"
                                        slot-scope="props">
                                        {{ props.option.name }}
                                        <!-- eslint-disable vue/require-v-for-key -->
                                        <span v-if="props.option.desc" class="text-danger">({{ props.option.desc }})</span>
                                        <span v-else-if="user.school_id === admin_school && role && role.id == 'admin'" class="text-danger">(DANGER)</span>
                                    </template>
                                </multiselect>
                            </div>
                        </div>
                        <div v-if="user.status || !isSchoolAdmin">
                            <div class="row">
                                <!-- Edit first name -->
                                <div class="col-md-3 form-group">
                                    <label class="small form-control-label label-status" :for="`first_name${user.id}${i}`">
                                        First Name
                                        <span class="text-danger validation-message">
                                            {{ validationUsers.first_name }}
                                        </span>
                                    </label>
                                    <input
                                        :id="`first_name${user.id}${i}`"
                                        v-model="user.first_name"
                                        class="form-control"
                                        type="text"
                                        name="first_name"
                                        @change="updateMember(user.id, i, $event)" />
                                </div>
                                <!-- Edit last name -->
                                <div class="col-md-3 form-group">
                                    <label class="small form-control-label label-status" :for="`last_name${user.id}${i}`">
                                        Last Name
                                        <span class="text-danger validation-message">
                                            {{ validationUsers.last_name }}
                                        </span>
                                    </label>
                                    <input
                                        :id="`last_name${user.id}${i}`"
                                        v-model="user.last_name"
                                        class="form-control"
                                        type="text"
                                        name="last_name"
                                        @change="updateMember(user.id, i, $event)" />
                                </div>
                                <!-- Edit email -->
                                <div class="col-md-6 form-group">
                                    <label class="small form-control-label label-status" :for="`email${user.id}${i}`">
                                        Email
                                        <span class="text-danger validation-message">
                                            {{ validationUsers.email }}
                                        </span>
                                    </label>
                                    <input
                                        :id="`email${user.id}${i}`"
                                        v-model="user.email"
                                        class="form-control"
                                        type="text"
                                        autocomplete="email"
                                        name="email"
                                        @change="updateMember(user.id, i, $event)" />
                                </div>
                            </div>
                            <div class="row">
                                <!-- Edit password -->
                                <div class="col-md-6 form-group">
                                    <label class="small form-control-label label-status" :for="`password${user.id}${i}`">Change Password</label>
                                    <input
                                        :id="`password${user.id}${i}`"
                                        v-model="user.password"
                                        class="form-control"
                                        type="password"
                                        placeholder="New Password"
                                        autocomplete="new-password"
                                        name="password" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="small form-control-label label-status" :for="`confirm_password${user.id}${i}`">
                                        <span v-if="!validationUsers.confirm_password">Confirm Password</span>
                                        <span v-else class="text-danger">
                                            {{ validationUsers.confirm_password }}
                                        </span>
                                    </label>
                                    <input
                                        :id="`confirm_password${user.id}${i}`"
                                        v-model="user.confirm_password"
                                        class="form-control"
                                        type="password"
                                        placeholder="Confirm Password"
                                        autocomplete="new-password"
                                        name="confirm_password"
                                        @change="updateMember(user.id, i, $event)" />
                                </div>
                            </div>
                            <div class="row">
                                <!-- Edit Nickname -->
                                <div class="form-group" :class="[user.avatar ? 'col-6' : 'col-12']">
                                    <label class="small form-control-label label-status" :for="`nickname${user.id}${i}`">
                                        Nickname
                                    </label>
                                    <input
                                        :id="`nickname${user.id}${i}`"
                                        v-model="user.nickname"
                                        class="form-control"
                                        type="text"
                                        name="nickname"
                                        @change="updateMember(user.id, i, $event)" />
                                </div>
                                <!-- Remove avatar -->
                                <div v-if="user.avatar" class="col-6 form-group">
                                    <label class="small form-control-label label-status">&nbsp;</label>
                                    <img
                                        :src="getAvatar(user.avatar)"
                                        width="40"
                                        height="40"
                                        class="mr-2">
                                    <input
                                        :id="'remove_avatar'+user.id"
                                        class="form-check-input"
                                        type="checkbox"
                                        name="remove_avatar"
                                        :value="user.id"
                                        @change="updateMember(user.id, i, $event)" />
                                    <label
                                        class="form-check-label small"
                                        :for="'remove_avatar'+user.id">Remove avatar</label>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Edit Note -->
                                <div class="col-12 form-group">
                                    <label class="small form-control-label label-status" :for="`note${user.id}${i}`">Note</label>
                                    <textarea
                                        :id="`note${user.id}${i}`"
                                        v-model="user.note"
                                        class="form-control form-control-sm"
                                        rows="5"
                                        name="note"
                                        @change="updateMember(user.id, i, $event)">
                                    </textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div
                                class="col-6 pt-2 mb-2 form-group">
                                <!-- Remove from class -->
                                <div v-if="user.isAssigned && canEdit(user) && !isAdmin && !isSchoolAdmin">
                                    <input
                                        :id="'remove'+user.id"
                                        class="form-check-input removeclass"
                                        type="checkbox"
                                        name="remove"
                                        :value="user.id"
                                        @change="updateMemberAssignment(user.id, $event)" />
                                    <label
                                        class="text-danger form-check-label small"
                                        :for="'remove'+user.id">Remove from this class</label>
                                </div>
                                <!-- Deactivate (student only for now) -->
                                <div v-if="!user.isAssigned && canEdit(user) && !isAdmin && !isSchoolAdmin && !(user_role === 'assistant-teacher' && user.role.id === 4)" class="position-relative">
                                    <input
                                        :id="'deactivate'+user.id"
                                        v-model="deactivated"
                                        class="form-check-input removeclass"
                                        type="checkbox"
                                        name="deactivate"
                                        @click="deactivated = !deactivated" />
                                    <label
                                        class="text-danger form-check-label small"
                                        :for="'deactivate'+user.id">Deactivate account</label>
                                    <popover-dialogue
                                        v-if="deactivated && deactivated !== user.id"
                                        :id="user.id"
                                        class="popover-dialogue-center"
                                        style="bottom: 2rem; right: 8rem;"
                                        @answer="deactivateMember">
                                        Are you sure? Only school administrators can reactivate accounts.
                                    </popover-dialogue>
                                </div>
                                <!-- Reactivate member (school admin) -->
                                <div v-if="isSchoolAdmin && !user.status">
                                    <input
                                        :id="'reactivate'+user.id"
                                        class="form-check-input"
                                        type="checkbox"
                                        name="remove"
                                        :value="user.id"
                                        @change="reactivateMember(user.id, $event)" />
                                    <label
                                        class="form-check-label text-success small"
                                        :for="'reactivate'+user.id">Reactivate Member</label>
                                </div>
                            </div>
                            <div
                                v-if="user.status"
                                class="text-right mb-2 form-group col-6">
                                <div v-if="saveForm" class="form-completed"></div>
                                <button
                                    class="btn btn-secondary btn-sm"
                                    type="button"
                                    @click="checkValidation(user.id)">
                                    Save
                                </button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </collapse>
        </div>
    </div>
    <div v-if="enable_search">
        <pagination
            v-show="(totalUsers / resultsPerPage > 1)"
            :total="totalUsers"
            :results-per-page="resultsPerPage"
            :current-page="currentPage"
            class="mt-3"
            @pagechanged="paginate">
        </pagination>
    </div>
</div>
</template>

<script>
import apiRequest from '../../functions/apiRequest';
import { spliceByID, passwordCheck } from '../../functions/utils';

export default {
    name: 'EditMember',
    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },

    props: {
        users: {
            type: Array,
            required: true
        },
        class_id: {
            type: Number,
            default: 0
        },
        assigned: {
            type: Number,
            default: 0
        },
        student_id: {
            type: Number,
            default: 0
        },
        enable_search: {
            type: Boolean,
            default: false
        },
        editing_school: {
            type: Number,
            default: 0
        },
        can_school_admin: {
            type: Boolean,
            default: false
        },
        admin_school: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            animate: true,
            validationUsers: {
                email: '',
                first_name: '',
                last_name: '',
                confirm_password: '',
                role: ''
            },
            selectedMember: null,
            saveForm: false,
            updatedUsers: [],
            currentUsers: null,
            searchedUsers: null,
            currentPage: 1,
            resultsPerPage: 10,
            searchCriteria: null,
            resultsOptions: [10, 15, 25, 50, 100],
            reactivated: 0,
            deactivated: 0,
            role: null,
            roles: [
                { id: 'hybrid', name: 'Hybrid Teacher' },
                { id: 'school-admin', name: 'School Admin' },
                { id: 'teacher', name: 'Teacher' },
                { id: 'assistant-teacher', name: 'Assistant Teacher' },
                { id: 'student', name: 'Student'},
                { id: 'admin', name: 'Inventionland Institute Admin', desc: 'DANGER' }
            ]
        };
    },

    computed: {
        isSearching() {
            return this.searchCriteria !== null && this.searchCriteria !== '';
        },
        totalUsers() {
            return this.isSearching ? this.searchedUsers.length : this.updatedUsers.length;
        },
        isAdmin() {
            return this.user_role === 'admin'
        },
        isSchoolAdmin() {
            return (this.user_role === 'school-admin' || this.can_school_admin)
        },
        userRoles() {
            return this.admin_school !== this.editing_school
                ? this.roles.filter(obj => obj.id !== 'admin')
                : this.roles;
        }
    },

    watch: {
        searchCriteria(val) {
            this.compileSearch(val);
        },

        resultsPerPage(val) {
            this.currentUsers = this.updatedUsers.slice(0, val);
        }
    },

    created() {
        const vm = this;
        vm.updatedUsers = vm.setupUsers(vm.users);
        vm.enable_search ? vm.paginate(1) : (vm.currentUsers = vm.updatedUsers);
    },

    mounted() {
        const vm = this;
        // if student_id exists, select and scroll to (for school-admin list-members)
        if(vm.student_id > 0) {
            vm.selectedMember = vm.student_id;
            setTimeout(() => {
                const el = vm.users.length > 0
                    ? vm.$refs['student' + vm.student_id][0]
                    : vm.$refs['student' + vm.student_id];
                el.scrollIntoView({behavior: 'smooth', block: 'start', inline: 'nearest'})
            }, 200)
        }
        // Watch for updates
        vm.startUserWatcher();
    },

    methods: {
        //
        // Watcher for member class assignments, name changes, etc
        // --------------------------------------------------------------------------
        startUserWatcher() {
            const vm = this;
            vm.$watch('users', function() {
                vm.updatedUsers = vm.setupUsers(vm.users);
                if(!this.isAdmin && !this.isSchoolAdmin) {
                    const updated = vm.users.filter(u => u.assigned || u.removed);
                    vm.updatedUsers = vm.updatedUsers.filter(u => !u.assigned && !u.removed)
                    vm.updatedUsers.unshift(...updated);
                    vm.paginate(1);
                }
            }, { deep: true })
        },

        //
        // Prep incoming user data
        // --------------------------------------------------------------------------
        setupUsers(array) {
            array.forEach(user => {
                Vue.set(user, 'fullname', this.getFullName(user.first_name, user.last_name));
                if(!this.isAdmin && !this.isSchoolAdmin) {
                    user.assigned || Vue.set(user, 'assigned', false);
                    user.removed || Vue.set(user, 'removed', false);
                }
                user.can_school_admin = user.permissions
                    ? user.permissions.find(p => p.slug === 'school-admin') : false;
                user.edited || Vue.set(user, 'edited', false);
                if(!this.isAdmin && !this.isSchoolAdmin) {
                    Vue.set(user, 'isAssigned', this.isAssigned(user.role.id, user.cls));
                }
            });
            return array;
        },

        //
        // Edit user permissions
        // --------------------------------------------------------------------------
        canEdit(user) {
            // Assistant teachers can edit themselves or students only
            return (
                user.role.id === 4 ||
                (user.role.id === 7 && this.user_role === 'teacher') ||
                this.user_role === 'admin' ||
                this.user_role === 'school-admin' ||
                this.can_school_admin ||
                user.id === this.user_id
            )
        },

        //
        // Select member
        // --------------------------------------------------------------------------
        selectMember(id) {
            this.selectedMember = this.selectedMember === id ? null : id;
        },

        //
        // Determine if assigned
        // --------------------------------------------------------------------------
        isAssigned(userRoleID, userClass) {
            const vm = this;
            const userClassID = userClass && userClass.id;
            if (vm.assigned === 0) {
                if (userRoleID === 7) {
                    return userClassID === vm.class_id;
                } else {
                    return userClassID > 0;
                }
            } else {
                return true;
            }
        },

        //
        // Check for validation errors
        // --------------------------------------------------------------------------
        checkValidation(userID) {
            const vm = this;
            let errors = 0;
            Object.keys(vm.validationUsers).forEach(u => {
                errors = vm.validationUsers[u] === '' ? errors + 0 : errors + 1;
            });
            if (errors === 0) {
                vm.saveForm = true;
                setTimeout(() => {
                    $('#name' + userID).collapse('hide');
                    $('#name' + userID)
                        .prev('.card-header')
                        .removeClass('collapse-selected');
                }, 1000);
            }
        },

        validateForm(userID, name, val) {
            const vm = this;
            if (val === '') {
                vm.validationUsers[name] = 'Required';
            } else if (
                name === 'confirm_password' &&
                val !== vm.currentUsers.find(obj => obj.id === userID).password
            ) {
                vm.validationUsers[name] = 'Passwords are not the same.';
            } else if(
                name === 'confirm_password' &&
                !passwordCheck.validate(val)
            ) {
                vm.validationUsers[name] = passwordCheck.message;
            } else {
                vm.validationUsers[name] = '';
                return true;
            }
            return false;
        },

        //
        // Store member updates
        // --------------------------------------------------------------------------
        updateMember(userID, index, event) {
            const vm = this,
                et = event.target,
                targetUser = vm.updatedUsers.find(obj => obj.id === userID);
            let request = { user_id: userID };

            if (et.name === 'confirm_password') {
                request.confirm_password = et.value;
            } else if (et.name === 'remove_avatar' ) {
                request.remove_avatar = 1;
                targetUser.avatar = '';
            }
            else {
                request[et.name] = et.value;
            }

            vm.validateForm(userID, et.name, et.value)
                && apiRequest('/update/user', request).then(response => {
                    if(response.success) {
                        targetUser.edited = true;
                        const el = document.querySelector(`.label-status[for="${et.name}${userID}${index}"]`);
                        el.classList.add('saved-data');
                    } else {
                        vm.validationUsers[et.name] = Object.values(response.data.errors)[0][0];
                    }
                });
        },

        //
        // Update Role (Admin only)
        // --------------------------------------------------------------------------
        updateRole(event, userID) {
            const request = {
                user_id: userID,
                role: event.id
            }
            apiRequest('/update/user', request).then(response => {
                if(response.success) {
                    const el = document.querySelector(`.label-status[for="role${userID}"]`);
                    el.classList.add('saved-data');
                    const user = this.currentUsers.find(u => u.id === userID);
                    switch(event.id) {
                        case 'hybrid':
                            user.can_school_admin = true;
                            user.role.name = 'Teacher';
                            break;
                        case 'school-admin':
                            user.role.name = 'School Admin';
                            break;
                        case 'admin':
                            user.role.name = 'Admin';
                            break;
                        case 'teacher':
                            user.role.name = 'Teacher';
                            break;
                        case 'assistant-teacher':
                            user.role.name = 'Assistant Teacher';
                            break;
                        case 'student':
                            user.role.name = 'Student';
                            break;
                    }
                } else {
                    this.validationUsers.role = response.data.error
                }
            });
        },



        //
        // Update Member Assignment
        // --------------------------------------------------------------------------
        updateMemberAssignment(userID, event) {
            const vm = this;
            const et = event.target;
            const targetUser = vm.updatedUsers.find(obj => obj.id === userID);
            const request = {
                user_id: userID,
                class_id: vm.class_id,
                type: et.name
            };
            apiRequest('/update/classmembers', request).then(() => {
                vm.$emit('switched', {
                    user: targetUser,
                    assigned: vm.assigned
                });
            });
        },


        //
        // Reactivate / Deactivate
        // --------------------------------------------------------------------------
        reactivateMember(userID) {
            apiRequest('/update/user', { user_id: userID, status: 1 }).then(() => {
                this.reactivated = userID;
                const user = this.updatedUsers.find(obj => obj.id === userID);
                user.status = 1;
                setTimeout(() => this.reactivated = 0, 2000);
            });
        },

        deactivateMember(userID) {
            const vm = this;
            if(userID !== 'cancel') {
                vm.deactivated = userID;
                apiRequest('/update/user', { user_id: userID, status: 0 }).then(() => {
                    setTimeout(() => {
                        vm.deactivated = 0;
                        spliceByID(vm.updatedUsers, userID)
                        vm.currentUsers = vm.updatedUsers;
                    }, 1000);
                });
            } else {
                vm.deactivated = 0;
            }
        },

        //
        // Paginate
        // --------------------------------------------------------------------------
        paginate(page) {
            const vm = this;
            vm.currentPage = page;
            --page; // eslint-disable-line no-param-reassign
            if (vm.isSearching) {
                vm.currentUsers = vm.searchedUsers.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            } else {
                vm.currentUsers = vm.updatedUsers.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            }
        },
        //
        //  Compile Search
        // --------------------------------------------------------------------------
        compileSearch(val) {
            let search = '';
            search += val;
            this.searchByTerm(search);
        },
        //
        // Search Files
        // --------------------------------------------------------------------------
        searchByTerm(search) {
            const vm = this;
            if (typeof search === 'undefined' || search === '') {
                vm.currentUsers = vm.updatedUsers;
                vm.reset();
                return;
            }

            // Split multiple search terms into array for iteration
            let searchTerms = typeof search === 'string' ? search.split(' ') : search;

            // Search using all lowercase and remove observer data from file data
            searchTerms = searchTerms.map(s => s.toLowerCase());
            let users = JSON.parse(JSON.stringify(vm.updatedUsers));

            // Exclude properties that shouldn't be searched
            users = users.map(({ created_at, updated_at, subject, email_verified_at, status, message, assigned, removed, edited, password, confirm_password, ...obj }) => obj);

            // // Filter assigned status results
            // status !== null && (messages = messages.filter(obj => obj.status === status));

            // Filter by iterating through search terms
            searchTerms.forEach(
                term =>
                    (users = users.filter(obj =>
                        Object.keys(obj).some(key =>
                            // Determine if values exist for regular term or probable status search
                            String(obj[key]).toLowerCase().includes(term)
                        )
                    ))
            );

            // Get only IDs from results
            let userIDs = users.map(f => f.id);

            // Assign the results to searchedUsers (for paginate)
            vm.searchedUsers = vm.updatedUsers.filter(obj => userIDs.indexOf(obj.id) !== -1);

            vm.paginate(1);
        },
        //
        // Reset Search
        // --------------------------------------------------------------------------
        reset() {
            const vm = this;
            vm.searchCriteria = '';
            vm.searchedUsers = null;
            vm.currentUsers = vm.updatedUsers.slice(0, vm.resultsPerPage);
            vm.paginate(1);
        },

        //
        // Get Avatar
        // --------------------------------------------------------------------------
        getAvatar(avatar) {
            if(avatar) {
                return !avatar || avatar.indexOf('https://') !== -1 ? avatar : '/avatars/' + avatar;
            }
        }
    }
};
</script>

<style lang="scss" scoped>
.form-completed::after {
  display: inline-block;
  width: 6rem;
  position: relative;
  top: 0;
  left: 0;
}
</style>
