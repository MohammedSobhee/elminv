<template>
<div>
    <div v-if="projects.length > 0 && !createdWithEmptyProjects">
        <h4>
            My Ideas (Activity Sheets)
        </h4>
    </div>
    <div v-if="projects.length > 0 && !createdWithEmptyProjects">
        <div id="projects" class="accordion activity">
            <div
                v-for="project in projects"
                :key="project.project_id"
                class="card"
                :class="projectSelected(project.project_id) && 'collapse-selected'">
                <div
                    class="card-header rounded"
                    @click.self="selectProject(project.project_id)">
                    <i class="fas" :class="[project.team_id ? 'fa-users' : 'fa-folder']"></i>
                    <span v-if="project.team_id">Team Project: </span>
                    {{ project.project_name }}
                    <span v-if="project.locked && (projects.length > 1)" class="text-gray">(Primary Project) <i v-btooltip="{delay:100,title: 'This is the only project that counts towards your grade.'}" class="fas fa-question-circle"></i></span>
                    <span v-if="project.team_id && project.members !== undefined" v-memberlist="project.members.join('<br>')" class="ml-2 badge badge-pill badge-light font-weight-normal text-muted">{{ project.members.length }} Members</span>
                    <span v-show="!projectSelected(project.project_id) && showGrades" class="medium text-success float-right">
                        <template v-if="project.locked">
                            <!-- <span v-if="!hasTotalsData(project)">0/0</span> -->
                            <span>{{ project.totalGrade }}/{{ project.totalPoints }}</span>
                            Points
                            <i v-btooltip="{delay:100,title: `${project.gradePercentage}% out of 100%`}" class="fas fa-question-circle"></i>
                        </template>
                    </span>
                    <!-- <span v-show="!projectSelected(project.project_id)" class="timestamp">Last updated: {{ formatDate(project.updated_at, 'long') }}</span> -->
                    <span
                        v-show="projectSelected(project.project_id)"
                        class="btn btn-sm btn-light btn-options"
                        @click.prevent.stop="projectOptions(project.project_id)">
                        Project Actions
                    </span>
                </div>
                <collapse>
                <div
                    v-show="projectSelected(project.project_id)">
                    <div class="card-body">
                        <ul v-if="worksheets.length > 0" class="list-group-flush">
                            <template v-for="worksheet in project.worksheets">
                            <li
                                v-if="worksheet.active"
                                :key="worksheet.id"
                                class="list-group-item list-group-item-assignment"
                                @click.stop="gotoWorksheet(worksheet, project)">
                                <div
                                    v-btooltip="worksheet.disabled && {delay: 500, title: 'This content is disabled.'}"
                                    class="title">
                                    <span
                                        v-if="user_role !== 'student'"
                                        id="icon"
                                        v-btooltip="{delay:100, title: 'These icons show the status of an activity sheet for students. Click to view these icons.'}"
                                        v-iconTitleSwitch
                                        :class="icon.bg"
                                        class="card-icon align-self-start"
                                        @click.prevent.stop="changeIcon">
                                        <i :class="icon.icon"></i>
                                    </span>
                                    <span
                                        v-else
                                        v-btooltip
                                        :title="getTitle(worksheet)"
                                        :class="getIconBackground(worksheet)"
                                        class="card-icon align-self-start">
                                        <i :class="getIcon(worksheet)"></i>
                                    </span>
                                    <span :class="{'link-label-disabled': worksheet.disabled}" class="list-group-item-label link-label">
                                        {{ worksheet.title }}
                                        <span
                                            v-if="project.locked && worksheet.due_date && !(worksheet.assignment_submitted_date || worksheet.grade)"
                                            v-btooltip="{title: worksheet.dueDateFormatted, delay: 200}"
                                            :class="worksheet.dueDateDiffDays > 2 ? 'text-faded' : 'text-faded-danger'"
                                            class="ml-2">
                                            (Due {{ worksheet.dueDateDiffHuman }})
                                        </span>
                                    </span>
                                </div>
                                <div v-if="project.locked" class="d-inline-block float-right">
                                    <span
                                        v-if="worksheet.message"
                                        v-bpopover="worksheet.message"
                                        class="icon-message"
                                        tabindex="0"
                                        data-popover-title="Message"
                                        @click.prevent.stop>
                                        <i class="far fa-comment-alt"></i>
                                    </span>
                                    <span v-show="showGrades">
                                        <span v-if="worksheet.grade !== null" class="status status-graded">{{ worksheet.grade }}/{{ worksheet.category_value }} Points (<strong>{{ worksheet.gradePercentage }}%</strong>)</span>
                                        <span v-else class="status text-muted">{{ worksheet.category_value }} Points</span>
                                    </span>
                                </div>
                            </li>
                            </template>
                        </ul>
                        <div v-else-if="!createdWithEmptyProjects">
                            Teacher has not allowed access to any activity sheets yet.
                        </div>
                    </div>
                </div>
                </collapse>
            </div>
        </div>
    </div>
    <!-- <div v-else-if="!createdWithEmptyProjects && !projectListOtherCount && !projects.length">
        <div v-if="team === 0">
            <p>Add a project to begin.</p>
        </div>
        <div v-else>
            <p>Send a <a href="/worksheets/">project</a> to your team to begin.</p>
        </div>
    </div> -->
    <modal
        v-if="selectedProject.project_id"
        :id="selectedProject.project_id"
        name="options"
        action-label="Save"
        class="medium"
        @action="handleSubmit">
        <template v-slot:header>
            <span class="text-primary">
                Actions for {{ selectedProject.project_name }}
            </span>
        </template>
        <template v-slot:body>
            <div class="form-group">
                <label :for="'projectrename'+selectedProject.project_id" class="form-check-label text-muted small">Rename Project</label>
                <input
                    :id="'projectrename'+selectedProject.project_id"
                    v-model="projectRename"
                    class="form-control"
                    type="text"
                    :placeholder="selectedProject.project_name"
                    @change="updateProjectName($event)">
            </div>
            <div v-if="team_id && !selectedProject.team_id">
                <div class="form-check">
                    <input
                        :id="'projectsend'+selectedProject.project_id"
                        v-model="optionsType"
                        type="radio"
                        name="type"
                        class="form-check-input"
                        value="1">
                    <label :for="'projectsend'+selectedProject.project_id" class="form-check-label text-primary"><strong>Send to team</strong></label>
                    <p><small>Send this project to your group to work on it as a team.</small></p>
                </div>
            </div>
            <div v-if="projects.length > 1 && !selectedProject.locked">
                <div class="form-check">
                    <input
                        :id="'switchlocked'+selectedProject.project_id"
                        v-model="optionsType"
                        type="radio"
                        name="type"
                        class="form-check-input"
                        value="4"
                        @click="switchConfirm = true; deleteConfirm = false">
                    <label :for="'switchlocked'+selectedProject.project_id" class="form-check-label text-primary">
                        <strong>Set as Primary Project</strong>
                        <popover-dialogue v-if="switchConfirm" :confirm="false">
                            Are you sure? {{ selectedProject.team_id ? 'All members of your team will have this set as their primary project.' : '' }}
                        </popover-dialogue>
                    </label>
                    <p><small>Set this project as your primary project. All Grades from the current primary project will be transferred.</small></p>
                </div>
            </div>
            <div class="form-check">
                <input
                    :id="'sendproject'+selectedProject.project_id"
                    v-model="optionsType"
                    type="radio"
                    class="form-check-input"
                    value="2"
                    @click="deleteConfirm = true; switchConfirm = false">
                <label :for="'sendproject'+selectedProject.project_id" class="form-check-label text-muted small">
                    Delete project
                    <popover-dialogue v-if="deleteConfirm" :confirm="false">
                        WARNING: Deleting a project is irreversable. All submissions and grades will be deleted.
                    </popover-dialogue>
                </label>
            </div>
        </template>
    </modal>
    <modal
        :id="1"
        v-focusInput
        name="create"
        action-label="Save"
        @action="handleSubmit">
        <template v-slot:header>
            <span class="text-primary">Add Project</span>
        </template>
        <template v-slot:body>
            <div class="form-group">
                <label for="projectAddInput">Project Name</label>
                <input
                    id="projectAddInput"
                    v-model="projectName"
                    type="text"
                    class="form-control">
            </div>
        </template>
    </modal>
</div>
</template>

<script>
import gradeIcons from '../../mixins/gradeIcons';
import { formatDate, deepCopy} from '../../functions/utils';
import apiRequest from '../../functions/apiRequest';


export default {
    name: 'AssignmentsActivity',
    directives: {
        focusInput: {
            bind: el => {
                $(el).on('shown.bs.modal', function() {
                    $(this).find('.modal-body :input:enabled:visible:first').focus();
                });
            }
        },
        iconTitleSwitch: {
            inserted: (el, binding, vnode) => {
                const setTitle = () => {
                    el.setAttribute('data-original-title', vnode.context.icon.title);
                    $(el).tooltip('show');
                }
                el.addEventListener('click', () => setTitle());
                el.$destroy = () => el.removeEventListener('click', setTitle);
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

    components: {
        Modal: () => import(/* webpackChunkName:"modal" */ '../common/Modal')
    },

    mixins: [gradeIcons],

    props: {
        projectList: {
            type: Array,
            required: true
        },
        team_id: {
            type: Number,
            default: 0
        },
        showGrades: {
            type: Boolean,
            required: true
        }
    },

    data() {
        return {
            projects: this.projectList,
            projectName: '',
            createdWithEmptyProjects: false,
            worksheets: [],
            selectedProject: {},
            optionsType: '',
            projectRename: '',
            responseMessage: '',
            deleteConfirm: false,
            switchConfirm: false,
            iconIncrement: -1,
            icon: null
        };
    },

    computed: {
        projectLockedId() {
            const project = this.projectList.find(proj => proj.locked);
            return typeof project !== 'undefined' ? project.project_id : 0;
        }
    },

    // watch: {
    //     projects: {
    //         deep: true,
    //         handler: function() {
    //             if(this.team) {
    //                 this.selectProject(this.projects[0].project_id);
    //             }
    //         }
    //     }
    // },

    mounted() {
        const vm = this;

        // Create worksheet list for 'Add projects' use
        if(vm.projects.length > 0) {
            vm.makeWorksheetList();
        }

        // Set  icons for teacher
        this.changeIcon();

        // Remember previously selected project
        this.rememberProject();

        // Automatically 'open' a project and set its cookie if its the only one
        if (vm.projects.length === 1) {
            vm.selectedProject = vm.projects[0];
        }
    },

    methods: {
        //
        // Go to worksheet
        // --------------------------------------------------------------------------
        gotoWorksheet(worksheet, project) {
            if(!worksheet.disabled)
                location.href='/worksheets/' + (project.team_id > 0 ? 'team/' : '') + worksheet.id + '/' + project.project_id;
            else
                location.href='#';
        },
        // //
        // // Determine if
        // // --------------------------------------------------------------------------
        // hasTotalsData(data) {
        //     return data.totalGrade !== 1 && data.totalPoints !== 1;
        // },
        //
        // Format Date
        // --------------------------------------------------------------------------
        formatDate(date, type) {
            return formatDate(date, type);
        },
        //
        // Get projct from cookie
        // --------------------------------------------------------------------------
        rememberProject() {
            const vm = this;
            // Get cookie and select project if it exists
            vm.cookie = vm.getCookie();
            vm.projects.find(obj => obj.project_id === vm.cookie.projectID) !== undefined
                && vm.selectProject(vm.cookie.projectID);
        },

        //
        // Select Project and add cookie to remember it
        // --------------------------------------------------------------------------
        selectProject(id) {
            const vm = this;
            if (vm.selectedProject.project_id === id) {
                vm.selectedProject = {};
            } else {
                vm.selectedProject = vm.projects.find(obj => obj.project_id === id);
            }
            vm.setCookie(vm.cookie, { projectID: vm.selectedProject.project_id });
        },

        //
        // Determine if project selected
        // --------------------------------------------------------------------------
        projectSelected(id) {
            return this.selectedProject.project_id
                && this.selectedProject.project_id === id;
        },

        //
        // Trigger project options modal
        // --------------------------------------------------------------------------
        projectOptions(id) {
            $('#options' + id + 'Modal').modal('show');
        },
        //
        // Update project name hack
        // --------------------------------------------------------------------------
        updateProjectName(event) {
            this.projectRename = event.target.value;
            this.optionsType = '3';
        },
        //
        // Send to team emit to Assignments.vue
        // --------------------------------------------------------------------------
        sendToTeam() {
            // this.$emit('get', id);
            window.location.reload();
        },
        //
        // Form handler
        // --------------------------------------------------------------------------
        handleSubmit(data) {
            // Check modal $emit name
            if (data.name !== 'options') {
                this.createProjectRequest(data);
                return;
            }

            const vm = this;
            let request = {
                project_id: data.id,
                type: +vm.optionsType
            };

            switch(vm.optionsType) {
                // case '1': // Send to team
                //     vm.sendToTeam(data.id);
                //     break;
                case '2': // Delete project
                    break;
                case '3': // Rename project
                    request.project_name = vm.projectRename;
                    vm.projects.find(proj => proj.project_id === data.id).project_name = vm.projectRename;
                    break;
                case '4': // Lock project switch
                    vm.setResponseMessage('Switching primary project...', 'highlight', 0)
                    request.locked_project_id = vm.projectLockedId;
                    break;
            }

            apiRequest('/project/management', request).then(response => {
                if (response.success) {
                    request.type === 1 && vm.sendToTeam();
                    // Remove project from projects object
                    request.type !== 3 && (vm.projects = vm.projects.filter(obj =>
                        obj.project_id !== request.project_id)
                    );
                    // Switched project redirect
                    if(request.type === 4) {
                        location.href = '/switched/project';
                    } else {
                        // Show response message
                        vm.setResponseMessage(response.success, 'success', 0);
                        // Reset form v-models
                        vm.optionsType = '';
                    }
                } else if (response.error) {
                    vm.setResponseMessage(response.error, 'danger', 0);
                }
            });
            // Hide bootstrap modal
            vm.deleteConfirm = false;
            vm.switchConfirm = false;
            vm.optionsType && $('#options' + data.id + 'Modal').modal('hide');

        },
        setResponseMessage(msg) {
            this.$emit('message', msg);
        },

        //
        // Create Project Rquest
        // --------------------------------------------------------------------------
        createProjectRequest(data) {
            const vm = this;
            const request = { project_name: vm.projectName };

            apiRequest('/create/project', request).then(response => {
                vm.setResponseMessage(response.success, 'success', 0);
                // Add new project to projects object
                vm.projects.unshift({
                    project_id: response.id,
                    project_name: request.project_name,
                    updated_at: formatDate(Date.now(), 'long'),
                    worksheets: vm.worksheets
                });
                // Reload for first timers
                if(vm.projects.length === 1) {
                    vm.createdWithEmptyProjects = true;
                    setTimeout(() => location.reload(), 1000);
                    vm.setResponseMessage(response.success += ' Initializing project...');
                }
                vm.projectName = '';
                // Automatically open newly created project
                vm.selectProject(vm.projects[0].project_id);
            });
            vm.projectName && $('#create' + data.id + 'Modal').modal('hide');
        },


        //
        // Make worksheet list
        // --------------------------------------------------------------------------
        makeWorksheetList() {
            const vm = this;
            vm.worksheets = deepCopy(vm.projectList[0].worksheets);
            vm.worksheets = vm.worksheets.map(w => {
                const worksheet = w;
                worksheet.grade = null;
                worksheet.status = null;
                worksheet.has_answers = null;
                return worksheet;
            });
        },
        //
        // Change icon for teachers
        // --------------------------------------------------------------------------
        changeIcon() {
            const icons = [
                {
                    bg: 'card-icon-gray',
                    icon: 'far fa-file-alt',
                    title: 'No status'
                },
                {
                    bg: 'card-icon-pending',
                    icon: 'fas fa-circle-notch',
                    title: 'Assignment in progress...'
                },
                {
                    bg: 'card-icon-sent',
                    icon: 'fas fa-ellipsis-h',
                    title: 'Assignment has been submitted.'
                },
                {
                    bg: 'card-icon-graded',
                    icon: 'fas fa-check',
                    title: 'Assignment has been graded.'
                }
            ];
            this.iconIncrement++;
            this.iconIncrement === icons.length  && (this.iconIncrement = 0);
            this.icon = icons[this.iconIncrement];
        }
    }
};
</script>
<style lang="scss" scoped>
.popover-dialogue {
    //top: -2.5rem;
    right: auto;

    &::after {
        right: auto;
        left: 2.3rem;
    }
}
</style>
