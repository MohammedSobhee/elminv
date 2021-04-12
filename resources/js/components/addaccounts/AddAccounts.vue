<template>
    <div class="add-accounts">
        <div v-if="class_id" class="row mb-2">
            <div class="col">
                <!-- <p>'View CSV Headers' for headers and class IDs to use in automatically adding students to classes.</p> -->
                Adding accounts for <strong class="text-primary"><a :href="`/edit/class/${class_id}`">{{ className }}</a></strong>
            </div>
        </div>
        <!-- Search -->
        <div v-show="(updatedUsers.length || existingUsers.length)" class="form-wrapper form-wrapper-search p-2">
            <div class="flex-row row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <input
                            v-if="processComplete || viewProcessed || (addingUsers && updatedUsers.length > 10) || editingUsers"
                            id="searchterm"
                            v-model="searchCriteria"
                            type="text"
                            placeholder="Search Accounts"
                            class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-1">
                    <button
                        v-if="isSearching"
                        id="searchreset"
                        type="button"
                        class="btn btn-block btn-sm btn-light btn-filter px-1"
                        @click="resetSearch">
                        Reset
                    </button>
                </div>
                <div
                    class="col-lg-8 text-right pr-3">
                    <button
                        v-if="existingUsers.length && !editingUsers"
                        type="button"
                        class="btn btn-sm btn-link btn-link-gray"
                        @click="switchSection">
                        {{ viewProcessed ? 'Add Accounts' : 'View Processed Accounts' }} <i class="fas fa-angle-right ml-1"></i>
                    </button>
                    <button
                        v-if="processComplete && existingUsers.length"
                        type="button"
                        class="ml-3 btn btn-sm btn-link btn-link-gray"
                        @click="getExistingAccounts(true)">
                        View All Processed Accounts <i class="fas ml-1" :class="loading ? 'fa-circle-notch' : 'fa-angle-right'"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- End Search -->

        <!-- Upload indicator -->
        <div class="position-relative mb-4">
            <div class="progress-wrapper">
                <div
                    v-show="uploadPercentage && uploadPercentage !== 100"
                    class="progress">
                    <div
                        class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar"
                        :aria-valuenow="uploadPercentage"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        :style="'width:' + uploadPercentage + '%'">
                    </div>
                </div>
            </div>
        <!-- End Upload indicator -->

        <!-- Notices -->
            <notification :msg="notice" position="addaccounts" class="small" style="top:.2rem" />
        <!-- Notices -->
        </div>


        <!-- Existing Users -->
        <div v-if="viewProcessed" @dragenter="dragging = true; viewProcessed = false">
            <div class="d-inline-block">
                <!-- <button
                    type="button"
                    class="btn btn-sm btn-light"
                    @click="downloadPDF(existingUsers)">
                    Download Student Fliers (PDF)
                </button> -->
                <button
                    type="button"
                    class="btn btn-sm btn-light"
                    @click="downloadCSV(existingUsers)">
                    Download Data<span class="mobile-hide"> (CSV)</span>
                </button>
                <template v-if="(processComplete && hasUploaded.emails) || !processComplete && hasExisting.emails">
                    <add-accounts-add-note
                        v-if="shouldAskAddNote && noSentEmails"
                        :users="existingUsers"
                        :sent="sentEmail"
                        button-style="light"
                        @action="sendEmail">
                    </add-accounts-add-note>
                    <button
                        v-else-if="!sentEmail && noSentEmails"
                        type="button"
                        class="btn btn-sm btn-light"
                        @click="sendEmail">
                        Send <span class="mobile-hide">Their</span> Login Info By Email
                    </button>
                </template>
                <button
                    v-if="hasExisting.codes && !(processComplete && !hasUploaded.codes)"
                    v-clipboard="{text:`https://${hostname}/activate/custom`}"
                    type="button"
                    title="Copy to clipboard"
                    class="btn btn-sm btn-light">
                    Copy<span class="mobile-hide"> Activation</span> URL
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-light"
                    @click="editUsers">
                    Edit {{ existingUsers.some(obj => obj.sent) ? '/ Re-send Emails' : '' }}
                </button>
            </div>
            <hr class="my-2">

            <!-- Process complete User Listing-->
            <add-accounts-list
                v-if="processComplete"
                :headers="updatedHeaders"
                :table="true"
                :current="currentUsers"
                @sort="sortUsers">
            </add-accounts-list>
            <!-- End Process complete User Listing-->

            <!-- Existing User listing -->
            <add-accounts-list
                v-else
                :headers="existingHeaders"
                :table="true"
                :current="currentExistingUsers"
                @sort="sortUsers">
            </add-accounts-list>
            <!-- End Existing User Listing -->
        </div>
        <!-- Existing Users -->

        <!-- Drop File -->
        <div
            v-show="dragging && !processComplete"
            ref="fileDrop"
            class="form-wrapper-file-dragging"
            @dragover.prevent
            @drop.prevent="inputFile">
            <span>Drop file</span>
        </div>
        <!-- Drop File -->

        <!-- Edit / Uploaded Users -->
        <template v-if="!viewProcessed">
            <div
                ref="formWrapper"
                class="form-wrapper-file form-upload-display p-2 mb-5"
                @drop.prevent="inputFile"
                @dragover.prevent
                @dragenter="dragging = true">
                <div class="flex-row row">
                    <div class="col-lg-4">
                        <!-- Legend -->
                        <div v-if="viewAcceptableFields" class="bg-white border p-2 class-list card-box-shadow">
                            <strong class="medium">Acceptable Headers:</strong>
                            <div class="small">
                                First Name, Last Name, Email or Username, <span :class="{'font-weight-bold':applyDefaultRole}">Role,</span> Password, Note<span v-if="!class_id">, Class</span>
                            </div>
                            <template v-if="userRoles.length">
                                <hr class="my-2">
                                <strong v-btooltip="{ title: 'Provide the ID of the role. See example below.'}" class="medium">Role ID Legend:</strong>
                                <table v-btooltip="{ title: 'Provide the ID of the role. See example below.'}" class="table table-sm small">
                                    <thead>
                                        <tr>
                                            <th>ID</th><th>Role Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="role in userRoles" :key="role.id">
                                            <td class="text-primary"><strong>{{ role.id }}</strong></td>
                                            <td>{{ role.name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                            <template v-if="classes.length && !class_id">
                                <hr class="my-2">
                                <strong v-btooltip="{ title: 'Provide the ID of the class if you wish to add students to classes automatically. See example below.'}" class="medium">Class ID Legend:</strong>
                                <table v-btooltip="{ title: 'Provide the ID of the class if you wish to add students to classes automatically. See example below.'}" class="table table-sm small">
                                    <thead>
                                        <tr>
                                            <th>ID</th><th>Name</th><th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="cls in classes" :key="cls.id">
                                            <td class="text-primary"><strong>{{ cls.id }}</strong></td>
                                            <td>{{ cls.class_name }}</td>
                                            <td>{{ cls.class_type_name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                        </div>
                        <!-- End Legend -->

                        <!-- After upload, before process actions -->
                        <template v-if="!editingUsers">
                            <div style="white-space:nowrap">
                                <span v-if="!dataProcessed && !processComplete && !editingUsers" class="btn btn-sm btn-light btn-file">
                                    <span>Browse</span>
                                    <input
                                        class="form-control-file"
                                        type="file"
                                        accept=".csv"
                                        @change="inputFile" />
                                </span>
                                <template v-if="!processComplete && !editingUsers">
                                    <button
                                        id="view-classes"
                                        v-btooltip="{ title: 'Please use these headers if possible. Contact support if you are unable to.'}"
                                        type="button"
                                        class="btn btn-sm btn-light"
                                        @click="viewAcceptableFields = !viewAcceptableFields">
                                        View CSV Legend
                                    </button>
                                    <button
                                        v-if="!uploadingUsers"
                                        v-btooltip="{delay:1000, title: 'Manually add accounts'}"
                                        type="button"
                                        class="btn btn-sm btn-light"
                                        @click="addAccount">
                                        <i class="fas fa-plus ml-0"></i>
                                    </button>
                                    <button
                                        v-if="currentUsers.length"
                                        type="button"
                                        class="btn btn-sm btn-light"
                                        @click="resetUpload">
                                        {{ addingUsers ? 'Cancel' : 'Reset' }}
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                    <!-- End after upload, before process actions -->

                    <!-- Process Accounts actions -->
                    <div class="col-lg-8 text-right">
                        <span v-if="!updatedUsers.length && !existingUsers.length" class="small text-muted"><strong>Note:</strong> All usernames must be unique within the system so you may need to make adjustments after uploading.</span>
                        <div v-if="currentUsers.length && !hasErrors && !dataProcessed" class="d-inline-block small">
                            <!-- Can process actions -->
                            <div v-if="canProcess" class="form-inline">
                                <template v-if="!hasUploaded.passwords && !editingUsers">
                                    <div class="form-check mr-4">
                                        <input
                                            id="generate_code"
                                            v-model="generate"
                                            value="code"
                                            type="radio"
                                            class="form-check-input">
                                        <label class="form-check-label" :class="{'text-danger': generate === 'error', 'text-success': !generate}" for="generate_code">Generate Activation Codes</label>
                                    </div>
                                    <div class="form-check mr-4">
                                        <input
                                            id="generate_password"
                                            v-model="generate"
                                            value="password"
                                            type="radio"
                                            class="form-check-input">
                                        <label class="form-check-label" :class="{'text-danger': generate === 'error', 'text-success': !generate}" for="generate_password">Generate Passwords</label>
                                    </div>
                                </template>
                                <button
                                    v-if="!processComplete && !editingUsers"
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    :disabled="uploadPercentage > 0 && uploadPercentage !== 100"
                                    @click="processAccounts">
                                    Process Accounts
                                </button>
                            </div>
                            <!-- End Can process actions -->

                            <!-- Save editing actions -->
                            <add-accounts-add-note
                                v-if="editingUsers && shouldAskAddNote && updatedUsers.some(obj => obj.sent && obj.note)"
                                :users="updatedUsers"
                                :sent="sentEmail"
                                :editing="true"
                                button-style="light"
                                @action="saveEdits"
                                @cancel="cancelEditUsers">
                            </add-accounts-add-note>
                            <div v-else-if="editingUsers" class="d-inline-block">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light"
                                    @click="cancelEditUsers">
                                    Stop Editing
                                </button>
                                <button
                                    type="button"
                                    :disabled="!hasEdited"
                                    class="btn btn-sm btn-primary"
                                    @click="saveEdits">
                                    Save Edits
                                </button>
                            </div>
                            <!-- End save editing actions -->
                        </div>

                        <!-- Cancel editing action -->
                        <div v-else-if="editingUsers" class="text-right">
                            <button
                                type="button"
                                class="btn btn-sm btn-light"
                                @click="cancelEditUsers">
                                Cancel Editing
                            </button>
                        </div>
                        <!-- End Cancel editing action -->
                    </div>
                </div>
                <!-- End Process Accounts actions -->


                <!-- File drop area, notices, etc -->
                <hr class="my-2">
                <div v-if="editingUsers && (hasUploaded.passwords || hasExisting.passwords)" class="small pl-2">
                    Edit passwords for active accounts in the
                    <a v-if="user_role === 'teacher'" href="/edit/class">class members area.</a>
                    <a v-else href="/dashboard">school administration area.</a>
                </div>

                <div v-if="isSearching && currentUsers.length === 0" class="pl-3">
                    No results.
                </div>
                <div v-if="!updatedUsers.length">
                    <!-- Notices -->
                    <div class="file-drop-area text-center" :class="{'bg-error text-danger': hasErrors }">
                        <strong>Upload a <span class="text-primary">csv</span> file <span class="d-none d-lg-inline">by dropping it here or</span> via the 'Browse' button.</strong>
                    </div>
                </div>
                <!-- End File drop area, notices, etc -->


                <!-- Edit Users Listing -->
                <add-accounts-list
                    v-if="currentUsers.length"
                    :headers="updatedHeaders"
                    :animate="animate"
                    :current="currentUsers"
                    :class_id="class_id"
                    :classes="classes"
                    :adding="addingUsers"
                    :editing="editingUsers"
                    :user_roles="userRoles"
                    :has-sent="hasSentEmails"
                    :complete="processComplete"
                    class="mt-3"
                    @usernames="generateUsernames"
                    @delete="deleteUser"
                    @check="inputCheck"
                    @sort="sortUsers">
                </add-accounts-list>
                <!-- End Edit Users listing -->

                <!-- Samples -->
                <add-accounts-examples
                    :class_id="class_id"
                    :has-errors="hasErrors"
                    :show="!updatedUsers.length || hasErrors" />
                <!-- Samples -->
            </div>
        </template>
        <pagination
            v-show="totalUsers / resultsPerPage > 1"
            :total="totalUsers"
            :results-per-page="resultsPerPage"
            :current-page="currentPage"
            class="mt-3"
            @pagechanged="paginateInanimate">
        </pagination>
        <!-- <div class="pdf-content text-center">
            <div ref="pdfImage">
                <img src="/assets/images/layout/ili_logo_print_sm.jpg">
                <strong>Hello!</strong>
            </div>
            <div ref="pdfContent">
                <hr>
                    <strong>Hello!</strong>
                <hr>
            </div>
        </div> -->
    </div>
</template>


<script>
import apiRequest from '../../functions/apiRequest';
import * as ua from '../../functions/addAccountsUtils';
import { deepCopy, isEmail } from '../../functions/utils';
import notify from '../../mixins/notify';
import clipboard from '../../directives/clipboard';
import downloadCSV from '../../functions/downloadCSV';
import Papa from 'papaparse';
// import jsPDF from 'jspdf';
// import html2canvas from "html2canvas";

export default {
    name: 'AddAccounts',
    components: {
        AddAccountsAddNote: () => import(/* webpackChunkName: 'add-accounts-add-note' */ './AddAccountsAddNote'),
        AddAccountsExamples: () => import(/* webpackChunkName: 'add-accounts-examples' */ './AddAccountsExamples'),
        AddAccountsList: () => import(/* webpackChunkName: 'add-accounts-list' */ './AddAccountsList')
    },
    directives: {
        clipboard
    },

    mixins: [notify],

    props: {
        school_id: {
            type: Number,
            required: true
        },
        class_id: {
            type: Number,
            default: 0
        },
        school_name: {
            type: String,
            required: true
        },
        classes: {
            type: Array,
            default: () => []
        },
        user_roles: {
            type: Array,
            required: true
        },
        school_admin: {
            type: Boolean,
            default: false
        },
        users: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            loading: false,
            uploadPercentage: 0,
            animate: true,
            dragging: false,
            editingUsers: false,
            uploadingUsers: false,
            addingUsers: false,
            hostname: window.location.hostname,

            idIncrement: 1,
            usernameInc: 1,

            viewAcceptableFields: false,
            viewProcessed: false,

            dataProcessed: false,
            processComplete: false,

            hasExisting: {
                emails: false,
                passwords: false,
                notes: false,
                codes: false,
                classes: false
            },
            hasUploaded: {
                emails: false,
                passwords: false,
                notes: false,
                codes: false,
                classes: false
            },

            hasSentEmails: false,
            hasEdited: false,
            sentEmail: false,
            generate: null,

            currentPage: 1,
            resultsPerPage: 20,
            searchCriteria: '',

            existingHeaders: [],
            existingUsers: [],
            updatedHeaders: [],
            updatedUsers: [],
            currentUsers: [],
            currentExistingUsers: [],
            searchedUsers: []
        };
    },
    computed: {
        isSearching() {
            return this.searchCriteria !== null && this.searchCriteria !== '';
        },

        totalUsers() {
            return this.isSearching
                ? this.searchedUsers.length
                : this.viewProcessed
                    ? this.existingUsers.length
                    : this.updatedUsers.length;
        },

        userRoles() {
            if(this.school_admin) return this.user_roles;
            const role = this.user_roles.find(obj => obj.slug == this.user_role).role;
            return this.user_roles.filter(obj => obj.permissions.includes(role))
        },

        className() {
            let cls = '';
            if(this.class_id)  {
                cls = this.classes.find(obj => obj.id === this.class_id).class_name;
            }
            return cls;
        },

        canProcess() {
            return this.updatedUsers.every(u => u.first_name && u.last_name && u.email);
        },

        noSentEmails() {
            return this.processComplete
                ? true
                : this.existingUsers.every(u => !u.sent)
        },

        shouldAskAddNote() {
            const has = this.processComplete ? this.hasUploaded : this.hasExisting;
            return ((has.passwords && has.notes) || (has.codes && has.notes));
        }
    },

    watch: {
        searchCriteria: {
            handler: function(val) {
                this.compileSearch(val);
            }
        }
    },

    created() {
        // Process existing uploaded users
        if(this.users.length) {
            this.addAccountsToTemplate(this.users, true);
        }

        // Change feedback text to array;
        this.notify.text = [];
    },

    mounted() {
        // Ensure dropfiles overlay goes away since dragleave fails and click away from classes
        this.clickAway = e => {
            this.dragging = false;
            if(this.viewAcceptableFields && e.target.id !== 'view-classes') {
                this.viewAcceptableFields = false;
            }
        }
        document.addEventListener('click', e => this.clickAway(e));
    },
    beforeDestroy() {
        document.removeEventListener('mousedown', this.clickAway);
    },

    methods: {
        //
        // Switch Section
        // --------------------------------------------------------------------------
        switchSection(complete) {
            if(complete !== true) this.viewProcessed = !this.viewProcessed;
            this.resetUpload();
            this.resetSearch();
        },
        //
        // Paginate
        // --------------------------------------------------------------------------
        paginate(page) {
            const vm = this;
            let current;
            vm.currentPage = page;
            --page; // eslint-disable-line no-param-reassign
            let users = vm.viewProcessed ? vm.existingUsers : vm.updatedUsers;
            if (vm.isSearching) {
                current = vm.searchedUsers.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            } else {
                current = users.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            }
            vm.viewProcessed ? (vm.currentExistingUsers = current) : (vm.currentUsers = current);
        },
        paginateInanimate(page) {
            this.animate = false;
            this.paginate(page);
            setTimeout(() => (this.animate = true), 20);
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
            let users = vm.viewProcessed ? vm.existingUsers : vm.updatedUsers;
            if (typeof search === 'undefined' || search === '') {
                vm.viewProcessed
                    ? (vm.currentUsers = users)
                    : (vm.currentExistingUsers = users);
                vm.resetSearch();
                return;
            }

            // Split multiple search terms into array for iteration
            let searchTerms = typeof search === 'string' ? search.split(' ') : search;

            // Search using all lowercase and remove observer data from file data
            searchTerms = searchTerms.map(s => s.toLowerCase());
            let tempUsers = JSON.parse(JSON.stringify(users));

            // Filter by iterating through search terms
            searchTerms.forEach(
                term =>
                    (tempUsers = tempUsers.filter(obj =>
                        Object.keys(obj).some(key =>
                            // Determine if values exist for regular term or probable status search
                            String(obj[key]).toLowerCase().includes(term)
                        )
                    ))
            );

            // Get only IDs from results
            let userIDs = tempUsers.map(f => f.id);

            // Assign the results to searchedUsers (for paginate)
            vm.searchedUsers = users.filter(obj => userIDs.indexOf(obj.id) !== -1);

            vm.paginate(1);
        },

        //
        // Reset Search
        // --------------------------------------------------------------------------
        resetSearch() {
            const vm = this;
            vm.searchCriteria = '';
            vm.searchedUsers = null;
            if(vm.viewProcessed)
                vm.currentExistingUsers = vm.existingUsers.slice(0, vm.resultsPerPage);
            else
                vm.currentUsers = vm.updatedUsers.slice(0, vm.resultsPerPage);
            vm.paginate(1);
        },

        //
        // Sort Users
        // --------------------------------------------------------------------------
        sortUsers(data) {
            const prop = data.key;
            const sortSwitch = data.switch;
            const sortDate = (a, b) => (b < a ? -1 : b > a ? 1 : 0);
            const sortAlpha = (a, b) => {
                const ka = (a === null || typeof a === 'number') ? "-1" : a;
                const ba = (b === null || typeof b === 'number') ? "1" : b;
                return ka.localeCompare(ba);
            };

            const sort = (obj, prop) => {
                return obj.sort((a, b) => {
                    // Asc / Desc switch
                    sortSwitch || ([a, b] = [b, a]); // eslint-disable-line no-param-reassign
                    if(prop === 'date') return sortDate(a[prop], b[prop])
                    else return sortAlpha(a[prop], b[prop]);
                });
            }

            const vm = this;
            vm.sortActiveLink = prop;

            if (vm.isSearching) {
                vm.searchedUsers = sort(vm.searchedUsers, prop);
            } else {
                vm.viewProcessed
                    ? (vm.existingUsers = sort(vm.existingUsers, prop))
                    : (vm.updatedUsers = sort(vm.updatedUsers, prop));
            }

            vm.paginate(1);
        },

        //
        // Input Files
        // --------------------------------------------------------------------------
        inputFile(event) {
            const vm = this;
            const files = event.target.files || event.dataTransfer.files;
            if (!files.length || vm.processComplete) return;

            vm.resetUpload();
            vm.notify.text = [];
            vm.uploadingUsers = true;
            Papa.parse(files[0], {
                header: true,
                skipEmptyLines: true,
                complete: results => {
                    vm.processFileData(results);
                }
            });

            vm.dragging = false; // Remove overlay
        },

        //
        // Process New Data
        // --------------------------------------------------------------------------
        processFileData(results) {
            const vm = this;
            if(results.errors.length) {
                vm.resetUpload();
                vm.hasErrors = true;
                vm.notify(ua.feedback.valid_file, 'danger', 0);
                return;
            }

            const headerData = ua.processHeaders(results.meta.fields, vm.class_id, vm.classes);

            if(!headerData.headers.length) {
                vm.notify(headerData.message, 'danger', 0)
                return;
            }

            let userData = ua.processUploadedUserData(results.data, vm.class_id, vm.userRoles, vm.classes);
            const props = ua.checkPropsExist(headerData.headers);
            // Set prop checks
            vm.hasUploaded.emails = userData.some(u => isEmail(u.email));
            vm.hasUploaded.passwords = props.passwords;
            vm.hasUploaded.classes = props.classes;
            vm.hasUploaded.notes = props.notes;

            // Generate usernames if emails not supplied
            !vm.hasUploaded.emails && (userData = vm.generateUsernames(userData));

            // Check for dupes, passwords, class access on upload
            const validate = ua.validate(userData);
            userData = validate.runAll(vm.classes);
            vm.notify(validate.getMessages(), 'danger', 0);

            vm.updatedHeaders = headerData.headers;
            vm.updatedUsers = userData;
            vm.paginate(1);
        },

        //
        // Reset upload
        // --------------------------------------------------------------------------
        resetUpload() {
            const vm = this;
            if(vm.processComplete && vm.existingUsers.length) {
                vm.getExistingAccounts();
            }
            vm.updatedUsers = [];
            vm.currentUsers = [];
            vm.updatedHeaders = [];
            vm.dataProcessed = false;
            vm.processComplete = false;
            vm.addingUsers = false;
            vm.editingUsers = false;
            vm.uploadingUsers = false;
            vm.hasUploaded.emails = false;
            vm.hasUploaded.passwords = false;
            vm.hasUploaded.codes = false;
            vm.hasUploaded.notes = false;
            vm.notify('');
            vm.paginate(1);
        },

        //
        // Process Data
        // --------------------------------------------------------------------------
        processAccounts() {
            const vm = this;

            if(vm.hasErrors) {
                return;
            }

            if(vm.addingUsers) {
                vm.updatedUsers.forEach(u => {
                    !u.first_name && (u.error.push('first_name'))
                    !u.last_name && (u.error.push('last_name'))
                    !u.email && (u.error.push('email'))
                })
                if(vm.hasAnyErrors()) {
                    vm.notify(ua.feedback.missing_fields, 'danger', 0);
                    return;
                }
            }

            const request = {
                school_id: vm.school_id,
                accounts: deepCopy(vm.updatedUsers)
            }

            if(!vm.hasUploaded.passwords) {
                if(!vm.generate || vm.generate === 'error') {
                    vm.generate = 'error';
                    return;
                }
                request[`generate_${vm.generate}`] = 1;
            }

            const waiting = setInterval(() => {
                vm.uploadPercentage++
            }, 1000);

            apiRequest('/upload/accounts', request).then(response => {
                if(response.success) {
                    vm.processComplete = true;
                    vm.viewProcessed = true;
                    vm.notify(response.success, 'success', 0);
                    vm.addAccountsToTemplate(response.accounts);
                } else {
                    vm.processResponseErrors(response);
                }
                vm.uploadPercentage = 0;
                clearInterval(waiting);
                vm.dataProcessed = true;
                vm.paginate(1);
            })
        },

        //
        // Process Response Errors
        // --------------------------------------------------------------------------
        processResponseErrors(response) {
            const vm = this;
            vm.notify(response.data.error, 'danger', 0);
            vm.hasErrors = true;
            const errors = response.data.accounts.filter(obj => obj.error.length);
            vm.updatedUsers = response.data.accounts.filter(obj => !obj.error.length);
            vm.updatedUsers.unshift(...errors);
        },

        //
        // Get existing accounts
        // --------------------------------------------------------------------------
        getExistingAccounts(viewProcessed = null) {
            const vm = this;
            vm.loading = true;
            apiRequest('/get/upload/accounts').then(res => {
                vm.addAccountsToTemplate(res, true);
                vm.loading = false;
                if(viewProcessed) {
                    vm.viewProcessed = true;
                    vm.resetUpload();
                }
            });
        },

        //
        // Add Existing Data
        // --------------------------------------------------------------------------
        addAccountsToTemplate(users, existingOnly = null) {
            const vm = this;
            const existingData = ua.processExistingData(users, vm.classes);
            if(!existingOnly) {
                // Has Code
                if(users[0].code) {
                    vm.hasUploaded.codes = true;
                // Has Password
                } else if(users[0].password) { //  && !vm.hasUploaded.passwords
                    vm.hasUploaded.passwords = true;
                }
                vm.updatedHeaders = existingData.headers;
                vm.updatedUsers = existingData.users;
                vm.currentUsers = vm.updatedUsers.slice(0, vm.resultsPerPage);
            }
            vm.existingHeaders = existingData.headers;
            vm.existingUsers = existingData.users;
            vm.hasExisting = existingData.props;
            vm.currentExistingUsers = vm.existingUsers.slice(0, vm.resultsPerPage);
        },

        //
        // Add Account
        // --------------------------------------------------------------------------
        addAccount() {
            const vm = this;
            vm.addingUsers = true;
            let headers = [
                'Role',
                'First Name',
                'Last Name',
                'Username',
                'Password',
                'Note'
            ]
            vm.classes.length && headers.push('Class');
            vm.updatedHeaders = ua.createHeaders(headers);
            vm.updatedHeaders.find(obj => /^email/gi.test(obj.key)).key = 'email';

            let userObj = {
                id: vm.idIncrement++,
                role: vm.userRoles.find(obj => obj.id === 1),
                first_name: '',
                last_name: '',
                email: '',
                password: '',
                note: '',
                class_id: '',
                error: [],
                sent: 0
            }
            vm.class_id && (userObj.class_id = vm.classes.find(c => c.id === vm.class_id));
            vm.updatedUsers.push(userObj)

            vm.paginate(1);
        },

        //
        // Generate Usernames
        // --------------------------------------------------------------------------
        generateUsernames(data) {
            const vm = this;
            const school = vm.school_name.replace(/ /g, '');
            const schoolAbbr = vm.school_name.match(/\b(\w)/g).join('');
            const regen = data === 'generate';
            let newData = regen ? vm.updatedUsers : data;

            const method = (user, i = null) => {
                const n = i ? i : '';
                const methods = {
                    1: `${user.first_name}.${user.last_name}${n}.${school}`,
                    2: `${user.last_name}.${user.first_name}${n}.${school}`,
                    3: `${school}.${user.first_name}.${user.last_name}${n}`,
                    4: `${school}.${user.last_name}.${user.first_name}${n}`,
                    5: `${user.first_name}.${user.last_name}${n}.${schoolAbbr}`,
                    6: `${user.last_name}.${user.first_name}${n}.${schoolAbbr}`,
                    7: `${schoolAbbr}.${user.first_name}.${user.last_name}${n}`,
                    8: `${schoolAbbr}.${user.last_name}.${user.first_name}${n}`
                }
                return methods[vm.usernameInc].toLowerCase();
            };

            // Don't increment to next method if errors found
            newData.some(u => !u.error.includes('email')) && vm.usernameInc++;
            vm.usernameInc > 8  && (vm.usernameInc = 1);

            newData.forEach((user, i) => {
                if(!isEmail(user.email)) {
                    user.email = (user.error.includes('email') && regen)
                        ? method(user, i)
                        : method(user);
                }
                user.error = user.error.filter(err => err !== 'email');
            });

            if(regen) {
                const validate = ua.validate(newData);
                newData = validate.dupes();
                vm.notify(validate.getMessages(), 'danger', 0)
                vm.hasAnyErrors();
                vm.paginate(1);
            }
            return newData;
        },

        //
        // Check for any errors
        // --------------------------------------------------------------------------
        hasAnyErrors() {
            const vm = this;
            vm.hasErrors = true;
            if(!vm.updatedUsers.some(u => u.error.length)) {
                vm.dataProcessed = false;
                vm.notify('');
            }
            vm.paginate(1);
            return vm.hasErrors;
        },

        //
        // Delete user
        // --------------------------------------------------------------------------
        deleteUser(userID) {
            const vm = this;
            vm.updatedUsers.splice(vm.updatedUsers.findIndex(obj => obj.id === userID), 1);
            vm.paginate(1);
            const validate = ua.validate(vm.updatedUsers);
            vm.updatedUsers = validate.runAll(vm.classes);
            vm.notify(validate.getMessages(), 'danger', 0);
            vm.hasAnyErrors();
        },

        //
        // Input check
        // --------------------------------------------------------------------------
        inputCheck(args) {
            const vm = this;
            this.hasEdited = true;
            const messages = ua.inputCheck(vm.currentUsers, vm.updatedUsers, vm.classes, args);
            messages.length && vm.notify(messages, 'danger', 0);


            if(vm.addingUsers) {
                const props = ua.checkValuesExist(vm.updatedUsers);
                vm.hasUploaded.passwords = props.passwords;
                vm.hasUploaded.classes = props.classes;
                vm.hasUploaded.notes = props.notes;
                vm.hasUploaded.emails = props.emails;
            }

            vm.hasAnyErrors();
        },

        //
        // Send Email
        // --------------------------------------------------------------------------
        sendEmail(sendNotes = null) {
            const vm = this;
            const users = vm.processComplete ? vm.updatedUsers : vm.existingUsers;

            if(!users.length) return;

            const note = sendNotes === 'yes' ? 1 : 0;
            const accounts = users.filter(u => isEmail(u.email) && !u.sent)
            const requestIDs = accounts.map(u => u.id);

            const waiting = setInterval(() => {
                vm.uploadPercentage++
            }, 1000);

            apiRequest('/upload/accounts/send', {IDs: requestIDs, note: note}).then(response => {
                if(response.success) {
                    accounts.forEach(d => d.sent = 1)
                    vm.notify(ua.feedback.email_sent, 'success');
                } else if(response.data.error) {
                    vm.notify(response.data.error, 'danger', 0)
                }
                vm.uploadPercentage = 0;
                clearInterval(waiting);
                vm.sentEmail = true;
            })
        },

        //
        // Edit Users
        // --------------------------------------------------------------------------
        editUsers() {
            const vm = this;
            vm.resetUpload();
            vm.editingUsers = true;
            vm.viewProcessed = false;
            vm.processComplete = false;

            vm.updatedHeaders = deepCopy(vm.existingHeaders);
            vm.updatedHeaders = vm.updatedHeaders.filter(obj => obj.key !== 'password');
            vm.updatedUsers = deepCopy(vm.existingUsers);

            // Filter out sent header if emails haven't been previously sent
            if(!vm.updatedUsers.some(obj => obj.sent)) {
                vm.updatedHeaders = vm.updatedHeaders.filter(obj => obj.key !== 'sent');
            // Set everything sent 0 for checkbox use
            } else {
                vm.hasSentEmails = true;
                vm.updatedUsers.forEach(u => u.sent = 0);
            }
            // In case of skipping an add/upload in progress
            vm.updatedUsers.forEach(u => u.error = []);
            vm.paginate(1);
        },
        cancelEditUsers() {
            this.hasEdited = false;
            this.editingUsers = false;
            this.viewProcessed = true;
            this.resetUpload();
        },

        //
        // Save Edits
        // --------------------------------------------------------------------------
        saveEdits(sendNotes = null) {
            const vm = this;

            const accounts = deepCopy(vm.updatedUsers);


            // Set re-send email user not yet sent for ActivationController@sendUpload
            if(vm.hasSentEmails) accounts.forEach(obj => obj.sent = obj.sent ? 0 : 1);

            const request = {
                school_id: vm.school_id,
                accounts: accounts
            }

            apiRequest('/upload/accounts/edit', request).then(response => {
                if(response.success) {
                    // Update existing with new data
                    vm.existingUsers = vm.existingUsers.map(k => {
                        let user = k;
                        const newUser = accounts.find(obj => obj.id === k.id);
                        if(newUser) {
                            user = newUser;
                        }
                        return user;
                    })

                    if(vm.hasSentEmails) {
                        // Send only to those not yet sent
                        const sendEmailData = accounts.filter(obj => obj.sent === 0)
                        sendEmailData.length && vm.sendEmail(sendNotes);
                    }

                    // Reset editing & set feedback
                    vm.cancelEditUsers();
                    vm.notify(response.success, 'success', 10000);

                } else {
                    vm.processResponseErrors(response);
                }
                vm.paginate(1);
            });

        },

        //
        // Download CSV
        // --------------------------------------------------------------------------
        downloadCSV(data) {
            let downloadUsers = data.map(({id, corrected, error, sent, user_id, ...obj}) => obj);
            // Add class name
            if(this.hasUploaded.classes) {
                downloadUsers.forEach(u => {
                    u.class_name = this.classes.find(obj => obj.id === +u.class_id).class_name;
                })
            }
            // Create and download CSV
            downloadCSV(downloadUsers, 'accounts_inventionland.csv');
        }

        // //
        // // Download PDF
        // // --------------------------------------------------------------------------
        // downloadPDF(data) {
        //     this.showPDFContent = true;
        //     const school_name = this.school_name.replace(/ /g, '_').toLowerCase();
        //     const doc = new jsPDF({
        //         format: 'letter'
        //     });
        //     doc.setFont('sans-serif');
        //     const canvasElement = document.createElement('canvas');
        //     // let tempImg = document.createElement('img');
        //     // tempImg.setAttribute('src', '/assets/images/layout/ili_logo_print_sm.jpg');
        //     // tempImg = this.$refs.pdfImage.appendChild(tempImg);
        //     html2canvas(this.$refs.pdfImage, { canvas: canvasElement, allowTaint : true }).then(canvas => {
        //         const img = canvas.toDataURL("image/jpeg", 1.0);


        //         // const contentHtml = this.$refs.pdfContent.innerHTML;
        //         // doc.fromHTML(contentHtml, 15, 15, {
        //         //     width: 1000
        //         // });

        //         doc.addImage(img, 'JPEG', 20, 20);
        //         doc.save(school_name + '_fliers.pdf');
        //         this.showPDFContent = false;
        //     });
        // }
    }
};
</script>
