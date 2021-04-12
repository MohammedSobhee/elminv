<template>
    <div>
        <div v-if="user_role === 'teacher'">
            <h4 class="mb-3 mt-lg-3">
                Live Discussion
            </h4>
            <div>
                <input
                    id="chat-class"
                    v-model="courseSettings.chat_class"
                    type="checkbox"
                    class="form-check-switch"
                    @change="updateSettings('chat_class')">
                    <label for="chat-class" class="ml-2">
                        <span v-if="courseSettings.chat_class">Enabled</span>
                        <span v-else>Enable</span>
                        Live Classroom Discussion
                    </label>
            </div>
            <div>
                <input
                    id="chat-team"
                    v-model="courseSettings.chat_team"
                    type="checkbox"
                    class="form-check-switch"
                    @change="updateSettings('chat_team')">
                    <label for="chat-team" class="ml-2">
                        <span v-if="courseSettings.chat_team">Enabled</span>
                        <span v-else>Enable</span>
                        Live Team Discussion
                    </label>
            </div>
            <!-- <div>
                <input
                    id="chat-private"
                    v-model="courseSettings.chat_private"
                    type="checkbox"
                    class="form-check-switch"
                    @change="updateSettings('chat_private')">
                    <label for="chat-private" class="ml-2">
                        <span v-if="courseSettings.chat_private">Enabled</span>
                        <span v-else>Enable</span>
                        Live Private Chats
                    </label>
            </div> -->
            <div v-if="courseSettings.chat_class || courseSettings.chat_team || courseSettings.chat_private" class="text-success small">
                Access to live discussion is placed on the Dashboard.
            </div>

            <template>
                <h4 class="mb-3 mt-lg-5">
                    Video Conferencing
                </h4>
                <div v-if="user_id == 97 || user_id == 115">
                    <input
                        id="videocon-zoom"
                        v-model="courseSettings.videocon_zoom"
                        type="checkbox"
                        class="form-check-switch"
                        @change="updateSettings('videocon_zoom')">
                        <label for="videocon-zoom" class="ml-2">
                            <span v-if="courseSettings.videocon_zoom">Enabled</span>
                            <span v-else>Enable</span> Zoom
                        </label>
                        <span v-if="authorized" class="text-gray small">(Authorized - <a href="/zoom/auth/delete" class="text-gray">Remove authorization)</a></span>
                        <div v-if="courseSettings.videocon_zoom && (!authorized || checkingAuth)" class="form-wrapper p-3 mb-2">
                            <div v-if="checkingAuth" class="text-primary">
                                Checking if authorization is needed...
                            </div>
                            <div v-if="!authorized" class="medium">
                                Authorize Inventionland Institute to create Zoom meetings for you.
                                <br><a href="/redirect/zoom" class="btn btn-xs btn-primary">Authorize</a>
                            </div>
                        </div>
                </div>
                <div>
                    <input
                        id="videocon-google"
                        v-model="courseSettings.videocon_google"
                        type="checkbox"
                        class="form-check-switch"
                        @change="updateSettings('videocon_google')">
                        <label for="videocon-google" class="ml-2">
                            <span v-if="courseSettings.videocon_google">Enabled</span>
                            <span v-else>Enable</span>
                            Google Meet
                        </label>
                </div>
                <div v-if="courseSettings.videocon_google">
                    <input
                        id="videocon-student"
                        v-model="courseSettings.videocon_student"
                        type="checkbox"
                        class="form-check-switch"
                        @change="updateSettings('videocon_student')">
                        <label for="videocon-student" class="ml-2">
                            <span v-if="courseSettings.videocon_student">Enabled</span>
                            <span v-else>Enable</span>
                            students to create Team and One-on-One Conferences with Google Meet
                        </label>
                </div>
                <div v-if="courseSettings.videocon_google || courseSettings.videocon_zoom" class="text-success small">
                    Access to video conferencing is placed on the Dashboard.
                </div>
            </template>
            <hr class="mb-3 mt-lg-5">
        </div>

        <h4 class="mb-3 mt-lg-3">
            Rubric Setup
        </h4>
        <p class="mt-3">Switch on the default rubric categories you'd like to include in your classes and then assign a value to them.</p>
        <settings-rubric :categories="rubricCategories" />

        <template v-if="showWorksheets">
            <hr class="mb-3 mt-lg-5">
            <h4 class="mb-3">
                Activity Sheet Points
            </h4>
            <p class="mt-3">Specify your preferred total points to each activity sheet according to the amount of work involved by the students. Default values are recommended.</p>
            <settings-rubric :categories="rubricWorksheets" type="worksheets" />
        </template>

        <div v-if="user_role === 'teacher'">
            <hr class="mb-3 mt-lg-5">
            <h4 class="mb-3">
                Show Objectives and/or Standards for Students
            </h4>
            <div>
                <input
                    id="objectives"
                    v-model="courseSettings.objectives"
                    type="checkbox"
                    class="form-check-switch"
                    @change="updateSettings('objectives')">
                    <label for="objectives" class="ml-2">Show Objectives</label>
                    <span v-if="courseSettings.objectives" class="ml-3 text-success small">Objectives are viewable by students in the courseware.</span>
            </div>
            <div>
                <input
                    id="standards"
                    v-model="courseSettings.standards"
                    type="checkbox"
                    class="form-check-switch"
                    @change="updateSettings('standards')">
                    <label for="standards" class="ml-2">Show Standards</label>
                    <span v-if="courseSettings.standards" class="ml-3 text-success small">Standards are viewable by students in the courseware.</span>
            </div>
        </div>
    </div>
</template>


<script>
import apiRequest from '../functions/apiRequest';

export default {
    name: 'SettingsTeacher',
    components: {
        SettingsRubric: () => import(/* webpackChunkName:"settings-rubric" */ './SettingsRubric')
    },

    props: {
        rubricWorksheets: {
            type: Array,
            required: true
        },
        rubricCategories: {
            type: Array,
            required: true
        },
        settings: {
            type: Object,
            default: () => {}
        },
        showWorksheets: {
            type: Number,
            required: true
        },
        zoomAuth: {
            type: [Boolean, Object],
            default: null
        }
    },

    data() {
        return {
            courseSettings: this.settings,
            checkingAuth: false,
            authorized: this.zoomAuth
        };
    },

    watch: {
        checkedDeletes(val) {
            this.deleteConfirm = this.deleteConfirm === true && val.length > 0;
        }
    },

    created() {
        Object.keys(this.settings).forEach(s => {
            this.settings[s] === 0 && (this.settings[s] = false);
            this.settings[s] === 1 && (this.settings[s] = true);
        });
    },

    methods: {
        updateSettings(name) {
            const vm = this;
            const value = vm.courseSettings[name] ? '1' : '0';
            const request = {
                name: name,
                value: value
            }
            name === 'videocon_zoom' && (vm.checkingAuth = true);

            apiRequest('/save/settings', request).then(() => {
                if(name === 'videocon_zoom' && value === '1') {
                    apiRequest('/zoom/auth').then(response => {
                        vm.checkingAuth = false;
                        if(response) {
                            vm.authorized = true;
                        }
                    });
                }
            });
        }
    }
};
</script>
