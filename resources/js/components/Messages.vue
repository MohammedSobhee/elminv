<template>
    <div class="messages">
        <info-alert v-if="!messages.length">
            {{ introMessage }}
        </info-alert>
        <div v-if="!isArchive">
            <div class="form-wrapper form-wrapper-search p-2 mb-2">
                <div class="row">
                    <div v-if="['teacher', 'assistant-teacher'].includes(user_role)" class="col-4">
                        <multiselect
                            v-model="sendMessageTo.class"
                            :options="classList"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            label="class_name"
                            track-by="id"
                            class="multiselect-sm"
                            placeholder="Class"
                            @select="setRecipient($event)">
                        </multiselect>
                    </div>
                    <div v-if="userList.length" class="col-4">
                        <multiselect
                            v-model="sendMessageTo.user"
                            :options="userList"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            label="fullName"
                            track-by="id"
                            class="multiselect-sm"
                            placeholder="Student"
                            @select="setRecipient($event)">
                        </multiselect>
                    </div>
                    <div v-if="teamList.length" class="col-4">
                        <multiselect
                            v-model="sendMessageTo.team"
                            :options="teamList"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            label="team_name"
                            track-by="id"
                            class="multiselect-sm"
                            placeholder="Team"
                            @select="setRecipient($event)">
                        </multiselect>
                    </div>
                </div>
            </div>
            <!-- Send Message Editor -->
            <editor-wysiwyg
                class="editor-wysiwyg editor-white"
                :clear="clearMessage"
                :disable-clear="true"
                :save-button-text="['Add', 'Add']"
                @saved="saveMessage" />
        </div>

        <!-- Heading / Feedback Notice -->
        <div>
            <h4 :class="(isArchive ? 'mt-2': 'mt-5')">
                {{ isArchive ? 'Archived Messages:' : 'Recently added messages:' }}
            </h4>
            <notification :msg="notice" style="top: -2rem" />
        </div>
        <!-- Search -->
        <div v-if="updatedMessages.length > 0" class="form-wrapper form-wrapper-search p-2 mb-2">
            <div class="row justify-content-between">
                <div class="col-5">
                    <div class="form-group">
                        <input
                            id="searchterm"
                            v-model="searchCriteria.term"
                            v-tooltip:term="'Search by date (month, day, and/or year), name, and type (i.e type student)'"
                            type="text"
                            :placeholder="`Search ${isArchive ? 'Archived' : ''} Messages`"
                            class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-7 text-right">
                    <button
                        v-if="isSearching"
                        id="searchreset"
                        type="button"
                        class="btn btn-sm btn-primary"
                        @click="resetSearchAndSort">
                        Reset Search
                    </button>
                    <a
                        v-if="archiveCount && !isArchive"
                        href="/messages/archive"
                        class="btn btn-sm btn-primary">
                        View Archive
                    </a>
                    <a
                        v-if="isArchive"
                        href="/messages"
                        class="btn btn-sm btn-primary pl-1">
                        <i class="fas fa-chevron-left small mr-2"></i>
                        Back to Recent Messages
                    </a>
                </div>
            </div>
        </div>
        <!-- End Search -->

        <!-- No Results -->
        <div v-if="isSearching && currentMessages.length === 0">
            <hr class="mt-0">
            No results.
        </div>
        <div v-if="updatedMessages.length === 0" class="text-success">
            <hr class="mt-0">
            <span v-if="isArchive">No messages are archived. <a href="/messages" class="text-primary">Return</a> to recent messages.</span>
            <span v-else>No messages have been added.</span>
        </div>
        <!-- No Results -->

        <!-- Messages List -->
        <transition-group
            :name="(animate ? 'row' : 'page')"
            class="row-transition"
            mode="out-in"
            tag="div">
            <div
                v-for="message in currentMessages"
                :key="message.id"
                :class="{ 'bg-white-gray': messageEditedID === message.id }"
                class="border message row-animate p-3 mb-3">
                <div class="messages-actions">
                    <span class="card-icon" @click="openEditMessage(message.id)">
                        <i v-btooltip="{title: 'Edit'}" class="fas fa-edit"></i>
                    </span>
                    <span class="card-icon" @click="deleteMessage(message.id)">
                        <i v-btooltip="{title: 'Trash'}" class="far fa-trash-alt"></i>
                    </span>
                    <span v-if="isArchive" class="card-icon" @click="archiveMessage(message.id, null)">
                        <i v-btooltip="{title: 'Unarchive'}" class="far fa-caret-square-up"></i>
                    </span>
                    <span v-else-if="message.type !== 'grade'" class="card-icon" @click="archiveMessage(message.id, 1)">
                        <i v-btooltip="{title: 'Archive'}" class="far fa-folder-open"></i>
                    </span>
                </div>
                <div class="mb-2">
                    <span class="small text-muted messages-date"><strong>Updated:</strong> {{ message.date }} ({{ message.updated }})</span><br>
                    <span v-if="message.type === 'grade'">
                        <strong>Assignment:</strong> {{ message.name }}
                    </span>
                    <span v-else>
                         <strong>{{ message.type | capitalize }} Dashboard:</strong>
                        {{ message.name }}
                    </span>
                </div>
                <div v-if="messageEditedID !== message.id" class="editor-message-display">
                    <hr class="border-dotted mx-0 mt-0 mb-2">
                    <span class="messages-content" v-html="message.content"></span>
                </div>
                <editor-wysiwyg
                    v-if="messageEditedID === message.id"
                    class="editor-white editor-wysiwyg"
                    :message="message.content"
                    :clear="clearMessage"
                    :disable-clear="true"
                    :save-button-text="['Edit Message', 'Message Edited']"
                    @saved="editMessage" />
            </div>
        </transition-group>
        <!-- Messages List -->

        <pagination
            v-show="totalMessages / resultsPerPage > 1"
            :total="totalMessages"
            :results-per-page="resultsPerPage"
            :current-page="currentPage"
            class="mt-3"
            @pagechanged="paginateInanimate">
        </pagination>
    </div>
</template>

<script>
import apiRequest from '../functions/apiRequest';
import notify from '../mixins/notify';
import { modifySendList } from '../functions/messagesUtils';
import { spliceByID, findObjMoveTop } from '../functions/utils';

export default {
    name: 'Messages',

    components: {
        EditorWysiwyg: () => import(/* webpackChunkName:"editor-wysiwyg" */ './editor/EditorWysiwyg'),
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },

    mixins: [notify],

    props: {
        user_role: {
            type: String,
            default: ''
        },
        messages: {
            type: Array,
            required: true
        },
        class_list: {
            type: Array,
            default: () => []
        },
        user_list: {
            type: Array,
            default: () => []
        },
        team_list: {
            type: Array,
            default: () => []
        },
        teacher_list: {
            type: Array,
            default: () => []
        },
        team: {
            type: Object,
            default: () => {}
        },
        archive_count: {
            type: Number,
            required: true
        }
    },

    data() {
        return {
            animate: true,
            archiveCount: this.archive_count,
            classList: this.class_list,
            userList: this.user_list,
            teamList: this.team_list,
            updatedMessages: this.messages,
            currentMessages: null,
            searchedMessages: null,
            searchCriteria: {
                term: null
            },
            sendMessageTo: {
                user: null
            },
            recipient: {
                id: null,
                type: null
            },
            currentPage: 1,
            resultsPerPage: 10,
            sortSwitch: true,
            sortActiveLink: '',
            openSendMessage: false,
            messageEditedID: false,
            clearMessage: false
        };
    },

    computed: {
        isSearching() {
            return Object.values(this.searchCriteria).some(val => val !== null && val !== '');
        },
        totalMessages() {
            return this.isSearching ? this.searchedMessages.length : this.updatedMessages.length;
        },

        introMessage() {
            let message = ['teacher', 'assistant-teacher', 'school-admin'].includes(this.user_role)
                ? `Add a message to a team, class, and/or student's dashboard. To add a message associated with a team or student's assignment, use the Gradebook.`
                : `Add a message to your teacher or class / team mates' dashboard.`;
            return message += ' Begin here by searching for a recipient from the dropdown menus.';
        }
    },

    watch: {
        searchCriteria: {
            deep: true,
            handler: function(val) {
                this.compileSearch(val);
            }
        }
    },

    created() {
        const vm = this;
        vm.isArchive = window.location.href.indexOf('archive') === -1 ? false : true;

        // Get and set cookie
        vm.cookie = vm.getCookie();
        vm.setupTooltipCookie(vm.cookie, {
            term: 0,
            sendto: 0
        });
        vm.setCookie(vm.cookie);

        Object.keys(vm.updatedMessages).forEach(m => {
            if(vm.updatedMessages[m].type === 'user')
                vm.updatedMessages[m].type = 'student'
        });

        // Show only the first page of files on created
        vm.currentMessages = vm.updatedMessages.slice(0, vm.resultsPerPage);

        // Modify Send Lists to include type and fullname if student
        vm.classList = modifySendList(vm.class_list, 1);
        vm.teamList = modifySendList(vm.team_list, 2);
        vm.userList = modifySendList(vm.user_list, 3);
    },

    methods: {
        //
        // Paginate
        // --------------------------------------------------------------------------
        paginate(page) {
            const vm = this;
            vm.currentPage = page;
            --page; // eslint-disable-line no-param-reassign
            if (vm.isSearching) {
                vm.currentMessages = vm.searchedMessages.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            } else {
                vm.currentMessages = vm.updatedMessages.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            }
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
            // Iterate through search criteria keys and build search string
            Object.keys(val).forEach(k => {
                // If search key is an object (class, categories), use 'name' property value
                if (val[k] !== null) {
                    search += typeof val[k] === 'object' ? ' ' + val[k].name : ' ' + val[k];
                }
            });
            this.searchMessagesByTerm(search);
        },
        //
        // Search Messages
        // --------------------------------------------------------------------------
        searchMessagesByTerm(search) {
            const vm = this;
            if (typeof search === 'undefined' || search === '') {
                vm.currentMessages = vm.updatedMessages;
                vm.reset();
                return;
            }

            // Split multiple search terms into array for iteration
            let searchTerms = typeof search === 'string' ? search.split(' ') : search;

            // Search using all lowercase and remove observer data from file data
            searchTerms = searchTerms.map(s => s.toLowerCase());
            let messages = JSON.parse(JSON.stringify(vm.updatedMessages));

            // Determine if search terms include ^active/a, ^deactive/a, or ^inactive/a
            let status = null;
            const tests = [
                { status: 1, regex: /(^|\s)[aA]ctiv[ea]/g },
                { status: 0, regex: /(^|\s)[dD]eactiv[ea]/g },
                { status: 0, regex: /(^|\s)[iI]nactiv[ea]/g }
            ];

            // Iterate through tests, if found in searchTerms, remove from searchTerms array
            // since there is no value in files obj to search for and assign its status
            tests.forEach(t => {
                let index;
                if ((index = searchTerms.findIndex(term => t.regex.test(term))) !== -1) {
                    // eslint-disable-line no-cond-assign
                    searchTerms.splice(index, 1);
                    status = t.status;
                }
            });

            // Filter assigned status results
            status !== null && (messages = messages.filter(obj => obj.status === status));

            // Filter by iterating through search terms
            searchTerms.forEach(
                term =>
                    (messages = messages.filter(obj =>
                        Object.keys(obj).some(key =>
                            // Determine if values exist for regular term or probable status search
                            String(obj[key]).toLowerCase().includes(term)
                        )
                    ))
            );

            // Get only IDs from results
            let msgIDs = messages.map(f => f.id);

            // Assign the results to searchedMessages (for paginate)
            vm.searchedMessages = vm.updatedMessages.filter(obj => msgIDs.indexOf(obj.id) !== -1);

            vm.paginate(1);
        },

        //
        // Reset Search
        // --------------------------------------------------------------------------
        reset() {
            const vm = this;
            Object.keys(vm.searchCriteria).forEach(k => (vm.searchCriteria[k] = null));
            vm.searchedMessages = null;
            vm.currentMessages = vm.updatedMessages.slice(0, vm.resultsPerPage);
            vm.paginate(1);
            vm.checkedDeletes = [];
        },
        resetSearchAndSort() {
            const vm = this;
            vm.updatedMessages = vm.sortMessages(vm.updatedMessages, 'status', 'created');
            vm.reset();
            vm.setTooltipCookie(vm.cookie, 'reset', vm.cookie);
        },

        //
        // Sort Files
        // --------------------------------------------------------------------------
        sortMessages(obj, ...props) {
            const vm = this,
                sortDate = (a, b) => (b < a ? -1 : b > a ? 1 : 0);

            return obj.sort((a, b) => {
                // Sort Menu
                if (props.length === 1) {
                    // Asc / Desc switch
                    vm.sortSwitch || ([a, b] = [b, a]); // eslint-disable-line no-param-reassign

                    // Loosely match ISO 8601 date
                    if (/([0-5][0-9])(.[0-9]+)?(Z)?$/.test(a[props[0]]) && props[0] === 'updated') {
                        return sortDate(a[props[0]], b[props[0]]);
                    }
                    // Reset Search - status > created
                } else {
                    sortDate(a[props[1]], b[props[1]]);
                }
            });
        },

        //
        // Set / Reset Recipient
        // --------------------------------------------------------------------------
        setRecipient(event) {
            const vm = this;
            vm.recipient.id = event.id;
            vm.recipient.type = event.type;

            Object.keys(vm.sendMessageTo).forEach(k => {
                if (!_.isEqual(vm.sendMessageTo[k], event)) {
                    vm.sendMessageTo[k] = null;
                }
            });
        },
        resetRecipient(obj) {
            Object.keys(obj).forEach(k => {
                obj[k] = null;
            });
        },
        //
        // Archive message
        // --------------------------------------------------------------------------
        archiveMessage(id, action) {
            const vm = this;
            const request = {
                id: id,
                archive: action
            }

            if(action === 1)
                vm.archiveCount += 1;

            apiRequest('/update/message', request);
            spliceByID(vm.updatedMessages, id);
            vm.paginate(vm.currentPage);
            vm.notify('Message archived.');
        },
        //
        // Delete message
        // --------------------------------------------------------------------------
        deleteMessage(id) {
            const vm = this;
            apiRequest('/delete/message', {id: id});
            spliceByID(vm.updatedMessages, id)
            vm.paginate(vm.currentPage);
            vm.notify('Message deleted.');
        },
        //
        // Edit Message
        // --------------------------------------------------------------------------
        openEditMessage(id) {
            this.messageEditedID = this.messageEditedID === id ? null : id;
        },
        editMessage(message) {
            const vm = this;
            const request = {
                id: vm.messageEditedID,
                message: message
            }
            apiRequest('/update/message', request).then(response => {
                let msg = vm.updatedMessages.find(obj => obj.id === vm.messageEditedID);
                msg.content = message;
                msg.date = response.date
                msg.updated = response.updated;
                vm.currentMessages = vm.updatedMessages;
                findObjMoveTop(vm.currentMessages, vm.messageEditedID);
                vm.messageEditedID = null;
                vm.notify('Message has been updated.');
            });
        },

        //
        // Save Message
        // --------------------------------------------------------------------------
        saveMessage(message) {
            const vm = this;

            if(vm.recipient.id === null) {
                vm.notify('Please select a recipient.', 'danger');
                return;
            }

            if(message === '<p></p>') {
                vm.notify('Please write a message first.', 'danger');
                return;
            }

            const postURL = '/send/message';
            const request = {
                id: vm.recipient.id,
                type: vm.recipient.type,
                sender_id: vm.user_id,
                message: message
            };
            vm.clearMessage = false;

            apiRequest(postURL, request).then(response => {
                // Set all classes
                if(typeof response.messages !== 'undefined') {
                    response.messages.forEach((f, i) => {
                        vm.updateMessagesList(message, response.messages[i], i);
                    });
                }
                // Set individual message
                else {
                    vm.updateMessagesList(message, response.message);
                }
                vm.clearMessage = true;
                vm.notify('Message added.');
                vm.resetRecipient(vm.sendMessageTo);
                vm.resetRecipient(vm.recipient);
            })
        },

        updateMessagesList(message, msgResponse, index = 0) {
            const vm = this;
            const target = vm.updatedMessages.find(obj => obj.id === msgResponse.id)
            if(target) {
                vm.updateTargetsMessage(target, message);
            }
            else {
                setTimeout(() => {
                    vm.updatedMessages.unshift(vm.buildNewMessage(msgResponse));
                    vm.paginate(vm.currentPage);
                }, index * 200);
            }
        },

        updateTargetsMessage(target, message) {
            const vm = this;
            target.content = message;
            spliceByID(vm.updatedMessages, target.id);
            vm.updatedMessages.unshift(target);
            vm.currentMessages = vm.updatedMessages;
        },

        buildNewMessage(response) {
            const msg = {
                id: response.id,
                name: response.name,
                type: response.type === 'user' ? 'student' : response.type,
                content: response.content,
                date: response.date,
                updated: response.updated
            }
            return msg;
        }
    }
};
</script>
