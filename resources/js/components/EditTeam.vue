<template>
<div>
    <notification :msg="notice" type="bootstrap" />
<div
    v-for="cls in teams"
    :key="cls.id">
    <h4 v-if="cls.teams.length > 0 || classId === cls.id" class="mt-5">
        <strong v-if="classId === cls.id">Editing </strong>Teams for class: {{ cls.class_name | capitalize }}
    </h4>


    <div v-if="cls.teams.length > 0" class="accordion">
        <div
            v-for="team in cls.teams"
            :key="team.id"
            class="card"
            :class="(selectedTeam === team.id) && 'collapse-selected'"
            @click="classId = cls.id">
            <div
                class="card-header"
                :class="removed === team.id && 'card-header-removed'"
                @click="selectTeam(team.id)">
                <div class="row">
                    <div
                        class="col-9 cursor-pointer"
                        :class="edited === team.id && 'font-italic'">
                        {{ team.team_name }}
                    </div>
                    <div class="col-3 text-right">
                        <div v-show="edited || removed" class="thing-edited" style="right:1rem">
                            <span v-show="edited === team.id" class="text-secondary ml-2">
                                Edited
                                <i class="fas fa-check"></i>
                            </span>
                            <span v-show="removed === team.id" class="text-danger ml-2">
                                Deleted
                                <i class="fas fa-backspace"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <collapse>
                <div
                    v-if="removed !== team.id && selectedTeam === team.id"
                    :key="team.id">
                    <div class="card-body">
                        <div class="row flex-row">
                            <div class="col-md-2 text-muted small">
                                Assigned students:
                            </div>
                            <div class="col-md-10">
                                <ul v-if="team.members.length !== 0" class="row flex-row">
                                    <li
                                        v-for="member in team.members"
                                        :key="member.id"
                                        class="form-check col-6 col-lg-4">
                                        <input
                                            :id="'member'+member.id+team.id"
                                            type="checkbox"
                                            checked
                                            class="form-check-input"
                                            @change="editTeam('remove', cls.id, team.id, member.id)" />
                                        <label
                                            class="form-check-label small"
                                            :for="'member'+member.id+team.id">{{ member.fullname }}</label>
                                    </li>
                                </ul>
                                <div v-else-if="team.available.length !== 0" class="small text-primary">
                                    Assign students from the list below.
                                </div>
                                <div v-else>
                                    <a :href="'/edit/class/'+cls.id">Assign students</a> to the class first.
                                </div>
                            </div>
                        </div>
                        <div v-if="team.available.length !== 0" class="row flex-row">
                            <div class="col-md-12">
                                <hr class="mt-3 mb-1" />
                            </div>
                            <div class="col-md-2 text-muted small">
                                Available students:
                            </div>
                            <div class="col-md-10">
                                <ul class="row flex-row">
                                    <li
                                        v-for="member in team.available"
                                        :key="member.id"
                                        class="form-check col-6 col-lg-4">
                                        <input
                                            :id="'member'+member.id+team.id"
                                            type="checkbox"
                                            class="form-check-input"
                                            @change="editTeam('add', cls.id, team.id, member.id)" />
                                        <label
                                            class="form-check-label small"
                                            :for="'member'+member.id+team.id">{{ member.fullname }}</label>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row flex-row align-items-end">
                            <div class="col-md-12">
                                <div v-if="team.project_count" class="text-success small mt-3">
                                    This team has activity sheet projects. <i v-btooltip="{delay:100,title: `Members will be added to or removed from this team's existing projects.`}" class="fas fa-question-circle"></i>
                                </div>
                                <hr class="mb-1" />
                            </div>

                            <div class="col-md-4 pt-2">
                                <label
                                    :for="'tname'+team.id"
                                    class="text-muted small form-control-label">
                                    Edit Team Name:
                                    <span class="text-danger validation-message">
                                        {{ validationMessages.edit_team_name }}
                                    </span>
                                </label>
                                <input
                                    :id="team.id"
                                    v-model="team.team_name"
                                    type="text"
                                    name="edit_team_name"
                                    class="form-control form-control-sm"
                                    :placeholder="team.team_name"
                                    @change="editTeamName" />
                            </div>

                            <div class="col-md-6 pt-2">
                                <div class="form-check d-inline-block">
                                    <input
                                        :id="'delete'+team.id"
                                        type="checkbox"
                                        :checked="deleteConfirm"
                                        class="form-check-input"
                                        @change="deleteConfirm = !deleteConfirm" />
                                    <label
                                        class="text-muted form-check-label text-primary small"
                                        :for="'delete'+team.id">Delete Team</label>
                                    <popover-dialogue v-if="deleteConfirm" :id="team.id" @answer="deleteTeam">
                                        Are you sure? Deleting a team cannot be undone<span v-if="team.project_count"> and will also delete their activity sheet projects</span>.
                                    </popover-dialogue>
                                </div>
                            </div>

                            <div class="col-md-2 pt-2 text-right">
                                <div v-if="saved" class="form-completed"></div>
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm"
                                    @click="saveForm">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </collapse>
        </div>
    </div>
    <div v-else-if="classId === cls.id" class="alert alert-success">
        Begin by <a href="#" data-toggle="modal" data-target="#teamAddModal">adding a team</a> for {{ cls.class_name | capitalize }}.
    </div>
</div>
</div>
</template>

<script>
import apiRequest from '../functions/apiRequest';
import { findObjMoveTop } from '../functions/utils';
export default {
    name: 'EditTeam',

    props: {
        teamList: {
            type: Array,
            required: true
        },

        class_id: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            removed: null,
            edited: null,
            saved: false,
            deleteConfirm: false,
            validationMessages: {
                edit_team_name: ''
            },
            teams: this.teamList,
            classId: this.class_id,
            selectedTeam: null
        };
    },

    created() {

        if(this.class_id) {
            this.classId = this.class_id;
            findObjMoveTop(this.teams, this.class_id);
        }

        this.teams.forEach(c => {
            c.teams.forEach(t => {
                t.available.forEach(u => {
                    u.assigned = 0;
                    u.fullname = !_.isEmpty(u) && this.getFullName(u.first_name, u.last_name);
                })
                t.members.forEach(u => {
                    u.assigned = 1;
                    u.fullname = !_.isEmpty(u) && this.getFullName(u.first_name, u.last_name);
                })
            })
        })
    },

    methods: {
        //
        // Select Team
        // --------------------------------------------------------------------------
        selectTeam(id) {
            this.selectedTeam = this.selectedTeam === id ? null : id;
        },

        //
        // Valid Form
        // --------------------------------------------------------------------------
        validateForm(name, val) {
            if (val === '') {
                this.validationMessages[name] = 'Required';
            } else {
                this.validationMessages[name] = '';
                return true;
            }
            return false;
        },

        //
        // Save Form
        // --------------------------------------------------------------------------
        saveForm() {
            this.saved = true;
            setTimeout(() => {
                this.selectedTeam = null;
            }, 1000);
        },

        //
        // Delete Team
        // --------------------------------------------------------------------------
        deleteTeam(answer) {
            const vm = this;
            vm.edited = null;
            if (answer !== 'cancel') {
                vm.removed = answer;
                apiRequest('/delete/team', { team_id: +answer })
            }
            vm.deleteConfirm = false;
        },

        //
        // Edit Team Name
        // --------------------------------------------------------------------------
        editTeamName(event) {
            const vm = this,
                et = event.target,
                request = {
                    team_id: +et.id,
                    team_name: et.value
                };
            vm.edited = +et.id;
            vm.validateForm(et.name, et.value) && apiRequest('/edit/teamname', request);
        },

        //
        // Edit Team - Remove / Add Members
        // --------------------------------------------------------------------------
        editTeam(action, cid, tid, mid) {
            const vm = this,
                request = {
                    team_id: tid,
                    type: action,
                    user_id: mid
                };

            vm.edited = tid;

            // Find by class id, then by team id
            let team = vm.teams.find(obj => obj.id === cid).teams.find(obj => obj.id === tid);

            apiRequest('/update/teammembers', request).then(response => {
                if (response.success) {
                    if (action === 'add') {
                        team.available = vm.switchMembers(
                            team.members,
                            team.available,
                            'id',
                            mid,
                            'fullname'
                        );
                        vm.removeUserFromAvailable(mid);
                    } else {
                        vm.addUserToAvailable(cid, tid, mid);
                        team.members = vm.switchMembers(
                            team.available,
                            team.members,
                            'id',
                            mid,
                            'fullname'
                        );
                    }
                }
            });
        },

        //
        // Remove member from list of available in other classes
        // --------------------------------------------------------------------------
        removeUserFromAvailable(mid) {
            this.teams.forEach(c => {
                c.teams.forEach(t => {
                    t.available = t.available.filter(user => user.id !== mid);
                });
            });
        },

        //
        // Remove member from list of available in other classes
        // --------------------------------------------------------------------------
        addUserToAvailable(cid, tid, mid) {
            const thisClass = this.teams.find(obj => obj.id === cid);
            const thisTeam = thisClass.teams.find(obj => obj.id === tid);
            const otherTeams = thisClass.teams.filter(obj => obj.id !== tid);

            otherTeams.forEach(t => {
                t.available.push(thisTeam.members.find(user => user.id === mid));
            });
        },

        //
        // Switch Members Array
        // --------------------------------------------------------------------------
        switchMembers(pushedArray, pulledArray, compareKey, compareValue, sortProp) {
            let result = pulledArray.find(obj => {
                return obj[compareKey] === +compareValue;
            });
            result.assigned = result.assigned === 1 ? 0 : 1;
            pushedArray.push(result);
            pushedArray.sort((a, b) => {
                return a[sortProp].localeCompare(b[sortProp]);
            });
            return pulledArray.filter(obj => obj[compareKey] !== +compareValue);
        }
    }
};
</script>

<style lang="scss" scoped>
.fa-question-circle {
  color: $secondary;
  &:hover {
      color: $dark-secondary;
  }
}

.form-completed:after {
    width: 5rem;
    padding-right: 0;
    left: 2rem;
    right: auto;
}
</style>
