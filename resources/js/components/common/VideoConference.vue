<template>
<div v-if="canView">
    <div v-if="location === 'dashboard'" class="dashboard-messages row justify-content-between align-items-end">
        <div class="col">
            <h4>
                <i class="far fa-comments ml-1"></i> Live <span>Video</span> Conferencing
            </h4>
        </div>
        <div class="col text-right">
            <span
                v-if="canCreate"
                v-btooltip="{title:'Start a meeting'}"
                type="button"
                class="btn btn-sm btn-light btn-hide-messages btn-open-chat"
                data-toggle="modal"
                data-target="#videocon1Modal"
                @click="createVideoCon">
                <i class="fas fa-plus"></i>
            </span>
            <span
                v-if="videocons.length"
                class="btn btn-sm btn-light btn-hide-messages"
                @click="showConferencing = !showConferencing">
                {{ showConferencing ? 'Hide' : 'Show' }} Conferencing
            </span>
        </div>
    </div>

    <hr v-show="(!showConferencing || !videocons.length) && location === 'dashboard'" class="m-0">

    <!-- {{ conferences }} -->
    <div v-show="showConferencing || location !== 'dashboard'" :class="{'dashboard-alert-wrapper': location === 'dashboard'}">
        <div v-if="videocons.length" :class="{'dashboard-alert border': location === 'dashboard'}">
        <ul class="videocon-alert list-group list-group-flush px-3">
            <li v-for="con in videocons" :key="con.id" class="list-group-item px-0 d-md-flex justify-content-between medium align-items-center">
                <div>
                    <div class="text-gray smaller">
                    <span v-if="listShowRecipientName(con)">{{ con.name }}</span>
                    <span v-if="con.label">{{ listDisplayLabel(con) }}</span>
                    <span v-if="services.length > 1">({{ listGetServiceName(con) }})</span>
                    </div>
                    {{ listGetRecipientType(con) }}
                    Video Conference started by {{ listDisplayUserName(con) }} <span class="text-gray ml-1">{{ con.date }}</span>
                </div>
                <div>
                    <a
                        v-if="con.service === 'zoom'"
                        v-btooltip="{delay: 500, title: 'Copy meeting password to clipboard'}"
                        v-clipboard="{text: con.password, notice: 'Copied'}"
                        href="#"
                        class="edit-action mr-2"
                        @click.prevent.stop><i class="fas fa-key"></i>
                    </a>
                    <span
                        v-if="canEdit(con)"
                        v-btooltip="{title: 'Edit meeting'}"
                        class="edit-action mr-2"
                        data-toggle="modal"
                        data-target="#videocon1Modal"
                        @click="editVideoCon(con.id)"><i class="fas fa-edit"></i>
                    </span>
                    <span
                        v-if="canEdit(con)"
                        v-btooltip="{title: 'Remove meeting'}"
                        class="delete-action mr-2"
                        @click="deleteVideoCon(con.id)"><i class="far fa-trash-alt"></i>
                    </span>
                    <a href="#" class="btn btn-xs btn-primary" @click.prevent.stop="openWindow(con.link)">Join</a>
                </div>
            </li>
        </ul>
        </div>
    </div>
    <modal
        :id="1"
        name="videocon"
        :action-label="actionLabel"
        @action="handleSubmit">
        <template v-slot:header>
            <span class="text-primary">{{ editing ? 'Edit' : 'Start' }} Video Conference</span>
        </template>
        <template v-slot:body>
            <div v-if="services.length > 1" class="form-group">
                <!-- <label>Video conference service:</label> -->
                <multiselect
                    id="class_type"
                    ref="class_type"
                    v-model="service"
                    :options="services"
                    placeholder="Video conference service"
                    track-by="id"
                    label="name"
                    :show-labels="false"
                    :close-on-select="true"
                    :searchable="false"
                    :allow-empty="false"
                    @select="auth = false">
                    <template
                        v-for="slotName in ['option', 'singleLabel']"
                        :slot="slotName"
                        slot-scope="props">
                        {{ props.option.name }}
                        <!-- eslint-disable vue/require-v-for-key -->
                        <span v-if="props.option.error" class="text-danger">({{ props.option.error }})</span>
                    </template>
                </multiselect>
            </div>
            <div v-else>
                {{ service.name }}: {{ service.error }}
            </div>

            <div v-if="service && !service.error" class="form-group">
                <!-- <label class="mt-3">Conference Participants:</label> -->
                <multiselect
                    v-model="participants"
                    :options="participants_list"
                    :searchable="true"
                    :close-on-select="true"
                    :show-labels="false"
                    placeholder="Find participants to alert"
                    class="mt-2"
                    label="display_name"
                    track-by="vtype_id">
                </multiselect>
            </div>
            <div v-else-if="service && service.error" class="mt-2">
                Go to <a href="/edit/settings">settings</a> to set up Zoom.
            </div>
            <div v-show="loading" class="loading-circle form-group"></div>
            <div v-if="!loading && participants && service" class="medium mt-2">
                <div v-if="!link && service.id === 'google'">
                    Go to Google Meet, create a meeting link (<a href="https://support.google.com/meet/answer/9302870?co=GENIE.Platform%3DDesktop&hl=en#meetstart" target="_blank">instructions</a>) and paste it below:
                </div>
                <div v-else-if="(hasFound || editing) && !creating" :class="{'d-flex justify-content-between':!editing}">
                    <div v-if="!editing" class="text-secondary flex-grow-1">
                        Found an existing meeting for {{ participants.name }}.
                    </div>
                    <div v-if="id" class="text-right">
                        <button v-if="id" type="button" class="btn btn-light btn-xs mb-1" @click="deleteVideoCon(id, true)">Delete this meeting</button>
                    </div>
                </div>
                <div v-if="showLink" class="form-group">
                    <input
                        v-model="link"
                        type="text"
                        :disabled="link.indexOf('zoom') !== -1"
                        class="form-control mt-2"
                        placeholder="Meeting link" />
                </div>
            </div>
            <div v-if="showPasswordLabel" class="form-placeholder">
                <div v-if="service.id === 'zoom'" class="form-group">
                    <input v-model="password" type="text" class="form-control mt-2" placeholder="Set own password to join meeting" />
                    <label>Password</label>
                    <small class="form-text text-muted ml-1">Zoom's generated password will be used if not provided.</small>
                </div>
                <div class="form-group">
                    <input v-model="label" type="text" class="form-control mt-2" placeholder="Optional label" />
                    <label>Label</label>
                </div>
            </div>
            <notification :msg="notice" style="top: -.7rem" class="medium" />
        </template>
    </modal>
    <notification v-if="!creating && !editing" :msg="notice" class="medium" />
</div>
</template>
<script>
import apiRequest from '../../functions/apiRequest';
import notify from '../../mixins/notify';
import clipboard from '../../directives/clipboard';

export default {
    name: 'VideoConference',
    components: {
        Modal: () => import(/* webpackChunkName:"modal" */ './Modal'),
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },
    directives: {
        clipboard
    },
    mixins: [notify],
    props: {
        location: {
            type: String,
            default: ''
        },
        services_available: {
            type: Array,
            required: true
        },
        participants_list: {
            type: Array,
            default: () => []
        },
        conferences: {
            type: Array,
            default: () => []
        },
        student_create: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            participants: null,
            service: null,
            id: 0,
            link: '',
            label: '',
            password: '',
            actionLabel: '',
            loading: false,
            hasFound: false,
            showConferencing: true,
            videocons: this.conferences,
            services: this.services_available,
            auth: false,
            editing: false,
            creating: false
        };
    },

    computed: {
        showLink() {
            return this.service.id !== 'zoom' || this.link.indexOf('zoom') !== -1;
        },
        showPasswordLabel() {
            return !this.loading && this.participants && this.service;
        },
        canView() {
            return this.user_role !== 'student' || (this.videocons.length || this.student_create);
        },
        canCreate() {
            return this.user_role !== 'student'
                || (this.student_create && this.service.id == 'google');
        }
    },

    watch: {
        service(val) {
            if(val && !this.editing && this.participants) {
                this.getExistingVideoCon();
            }
        },

        participants(val) {
            if(val && !this.editing) {
                this.getExistingVideoCon();
            }
        }
    },

    created() {
        if(this.services.length === 1) {
            this.service = this.services[0];
        }

        this.setAuth();

        setTimeout(() => this.joinVideoConAlerts(), 500);
    },
    methods: {
        //
        // List Display
        // --------------------------------------------------------------------------
        listShowRecipientName(con) {
            return !(con.vtype === 3 && con.vtype_id === this.user_id)
        },
        listGetRecipientType(con) {
            return con.vtype === 1 && 'Class'
                || con.vtype === 2 && 'Team'
                || con.vtype === 3 && 'One-on-One';
        },
        listDisplayLabel(con) {
            return (!(con.vtype === 3 && con.vtype_id === this.user_id) ? '- ' : '') + con.label;
        },
        listDisplayUserName(con) {
            if(this.user_role !== 'student' && con.user.role_slug == 'student') {
                return con.user.name;
            } else {
                return con.user.nickname || con.user.name
            }
        },
        listGetServiceName(con) {
            return this.services.find(obj => obj.id === con.service).name;
        },

        //
        // Determine if user can edit
        // --------------------------------------------------------------------------
        canEdit(con) {
            return con.user_id === this.user_id
                || (this.user_role === 'teacher' || this.user_role === 'assistant-teacher');
        },

        //
        // Check for existing video conferences
        // --------------------------------------------------------------------------
        getExistingVideoCon() {
            const vm = this;
            vm.loading = true;
            vm.hasFound = false;
            vm.editing = false;
            apiRequest('/search/videocon', {
                service: vm.service.id,
                vtype: vm.participants.vtype,
                vtype_id: vm.participants.vtype_id
            }).then(response => {
                const resp = response || {
                    id: 0,
                    service: vm.service.id,
                    link: '',
                    password: ''
                };
                vm.id = resp.id;
                vm.service = this.services.length > 1
                    ? this.services.find(obj => obj.id === resp.service)
                    : vm.service;
                vm.link = resp.link;
                vm.password = resp.password;
                vm.label = resp.label;

                vm.loading = false;
                vm.actionLabel = response ? 'Edit' : 'Send Conference Link Alert';
                response && (vm.hasFound = true);
                vm.link && (vm.creating = false);
            });
        },

        //
        // Set Auth
        // --------------------------------------------------------------------------
        setAuth() {
            const vm = this;
            const service = vm.getZoomServiceObject();
            if(vm.user_role !== 'student' && service.id === 'zoom') {
                vm.getZoomAuth();
            } else {
                vm.auth = true;
            }
        },
        getZoomServiceObject() {
            return this.services.length > 1
                ? this.services.find(obj => obj.id === 'zoom')
                : this.service;
        },


        //
        // Start Zoom process
        // --------------------------------------------------------------------------
        getZoomAuth() {
            const vm = this;
            vm.loading = true;
            apiRequest('/zoom/auth').then(response => {
                vm.loading = false;
                vm.auth = response;
                if(!vm.auth) {
                    let service = vm.getZoomServiceObject();
                    service.error = 'Not yet authorized';
                }
            });
        },


        //
        // Join video conference alerts on created
        // --------------------------------------------------------------------------
        joinVideoConAlerts() {
            const vm = this;
            // Join personal
            Echo.join(`videocon.3.${vm.user_id}`)
                .listen('VideoConEvent', event => {
                    vm.handleNewEvent(event.videocon);
                })
                .listen('VideoConhandleDeleteEvent', event => {
                    vm.handleDeleteEvent(event.id);
                })

            // If student join class (students don't get class in chat list)
            if(vm.user_role === 'student') {
                Echo.join(`videocon.1.${vm.user_class_id}`)
                    .listen('VideoConEvent', event => {
                        vm.handleNewEvent(event.videocon);
                    })
                    .listen('VideoConhandleDeleteEvent', event => {
                        vm.handleDeleteEvent(event.id);
                    })
            }

            // Iterate over recipient list and join
            vm.participants_list.forEach(v => {
                Echo.join(`videocon.${v.vtype}.${v.vtype_id}`)
                    .listen('VideoConEvent', event => {
                        vm.handleNewEvent(event.videocon);
                    })
                    .listen('VideoConhandleDeleteEvent', event => {
                        vm.handleDeleteEvent(event.id);
                    })

            });
        },


        //
        // Create Meeting
        // --------------------------------------------------------------------------
        createVideoCon() {
            const vm = this;
            vm.creating = true;
            vm.editing = false;
            vm.participants = null;
            vm.id = 0
            vm.password = ''
            vm.label = ''
            vm.link = ''
        },

        //
        // Delete Video conference
        // --------------------------------------------------------------------------
        deleteVideoCon(id, thisMeeting = null) {
            const vm = this;
            vm.videocons = vm.videocons.filter(obj => obj.id !== id);
            apiRequest('/delete/videocon', { id: id }).then(response => {
                if(thisMeeting && response) {
                    vm.editing = false;
                    vm.id = 0
                    vm.participants = null;
                    vm.password = ''
                    vm.label = ''
                    vm.link = ''
                    vm.actionLabel = '';
                    setTimeout(() => $('#videocon' + id + 'Modal').modal('hide'), 800);
                }
                if(!response) {
                    vm.notify('Failed. Please contact support.', 'danger');
                } else {
                    vm.notify('Meeting deleted');
                }
            });
        },


        //
        // Edit Video conference
        // --------------------------------------------------------------------------
        editVideoCon(id) {
            const vm = this;
            vm.editing = true;
            vm.creating = false;
            vm.hasFound = false;
            const videocon = vm.videocons.find(obj => obj.id === id);
            vm.id = id;
            vm.service = vm.services.find(obj => obj.id === videocon.service);
            vm.participants = vm.participants_list.find(obj => obj.vtype_id === videocon.vtype_id);
            vm.link = videocon.link;
            vm.password = videocon.password;
            vm.label = videocon.label;
            vm.actionLabel = 'Edit';
        },


        //
        // Handle events
        // --------------------------------------------------------------------------
        handleNewEvent(con) { // New Event
            const vm = this;
            if(vm.videocons.findIndex(v => v.id === con.id) === -1) {
                vm.videocons.unshift(con);
            }
            else {
                let updatedCon = vm.videocons.find(v => v.id === con.id);
                updatedCon.date = con.date;
                updatedCon.service = con.service;
                updatedCon.name = con.name;
                updatedCon.password = con.password;
                updatedCon.label = con.label;
                updatedCon.link = con.link;
            }
        },
        handleDeleteEvent(id) { // Delete Event
            this.videocons = this.videocons.filter(obj => obj.id !== id);
        },


        //
        // Open popup
        // --------------------------------------------------------------------------
        openWindow(url) {
            // https://stackoverflow.com/a/16861050
            function popupWindow(url, title, win, w, h) {
                const y = win.top.outerHeight / 2 + win.top.screenY - h / 2;
                const x = win.top.outerWidth / 2 + win.top.screenX - w / 2;
                return win.open(
                    url,
                    title,
                    `toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=yes, copyhistory=no, width=${w}, height=${h}, top=${y}, left=${x}`
                );
            }
            const duration = url.indexOf('zoom') === -1 ? 0 : 1000;
            setTimeout(() => popupWindow(url, 'chat', window, 800, 600), duration);
        },

        //
        // Submit and send video conference alert
        // --------------------------------------------------------------------------
        handleSubmit(data) {
            if(this.service.id === 'google' && this.link.indexOf('https://') === -1) {
                this.notify('Add a valid URL.', 'danger');
                return;
            }
            this.postData(data.id);
        },

        //
        //  Send Alert
        // --------------------------------------------------------------------------
        postData(id) {
            const vm = this;
            apiRequest('/send/videocon', {
                id: vm.id,
                service: vm.service.id,
                vtype: vm.participants.vtype,
                vtype_id: vm.participants.vtype_id,
                vtype_name: vm.participants.name,
                link: vm.link,
                password: vm.password,
                label: vm.label
            }).then(response => {
                if(!response.success) {
                    vm.notify('Failed. Please contact support.', 'danger');
                } else {
                    vm.notify('Alert sent.');
                    setTimeout(() => $('#videocon' + id + 'Modal').modal('hide'), 1000);
                    setTimeout(() => {
                        vm.link = '';
                        vm.label = '';
                        vm.participants = null;
                        vm.hasFound = false
                        vm.creating = false;
                    }, 1100);
                }
            });
        }
    }
}
</script>
<style lang="scss" scoped>
.form-control:disabled, .form-control[readonly] {
    color: $medium-gray;
    background-color: $white-gray;
    opacity: 1;
}
input:not(:placeholder-shown) +label {
    visibility: visible;
    right: .5rem;
}
</style>
