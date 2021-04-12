<template>
<div>
    <!-- <pre>{{chatlist}}</pre> -->
    <div v-if="chatlist.length > 1">
        <multiselect
            v-model="selectedChat"
            :options="chatlist"
            :searchable="true"
            :close-on-select="true"
            :show-labels="false"
            label="display_name"
            track-by="ctype_id"
            :class="{'chat-multiselect-rounded': showActiveUsers, 'chat-multiselect-force-rounded': !chatID }"
            class="multiselect-sm chat-multiselect"
            placeholder="Select a live discussion"
            @select="selectChat($event)">
        </multiselect>
    </div>

    <div v-if="chatID" class="chat">
        <div
            :class="{'chat-right-border': showActiveUsers}"
            class="flex-grow-1">
            <div
                :class="{
                    'chat-body-classlist': chatlist.length > 1,
                    popup: popup
                }"
                class="chat-body">
                <div v-if="messages.length > 3" class="chat-log-shadow"></div>
                <span class="icon-show-active">
                    <span
                        class="position-relative"
                        @click="showActiveUsers = !showActiveUsers">
                        <i
                            title="Show active participants"
                            class="fas fa-user-friends"
                            :class="{'text-primary':showActiveUsers}"></i>
                    </span>
                </span>

                <div v-if="!loading && !messages.length" :class="{popup:popup}" class="chat-log">
                    <div class="mt-5 mb-5 pt-5 mb-5 text-center text-muted">
                        Be the first to start a discussion!
                    </div>
                </div>
                <ul
                    v-else
                    v-chat-scroll
                    :class="{popup:popup}"
                    class="list-unstyled chat-log">
                    <li v-for="(message, index) in messages" :key="index" class="chat-log-message">
                        <img v-if="message.user.avatar" :src="getAvatar(message.user.avatar)" class="avatar">
                        <div v-else class="avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="chat-user-info">
                            <strong class="mr-1">{{ message.user.nickname || message.user.name }}</strong>
                            <span class="chat-date">{{ message.date }}</span>
                            <i v-if="user_role !== 'student'" class="far fa-trash-alt chat-delete" @click="deleteMessage(message.id)"></i>
                        </div>
                        <div v-if="deleteNotice.id && deleteNotice.id === message.id" class="text-danger">
                            {{ deleteNotice.msg }}
                        </div>
                        <div v-else class="chat-message">
                            {{ message.message }}
                        </div>
                    </li>
                </ul>
                <div class="chat-input">
                    <textarea
                        v-model="newMessage"
                        type="text"
                        name="message"
                        placeholder="Enter your message..."
                        class="form-control form-control-sm"
                        rows="2"
                        @keydown="sendTypingEvent"
                        @keyup.enter.exact="sendMessage"></textarea>
                    <span v-if="activeUser" class="chat-is-typing">{{ activeUser.name }} is typing...</span>
                </div>
            </div>
        </div>

        <div v-if="showActiveUsers">
            <div :class="{'chat-active-list-classlist': chatlist.length > 1}" class="p-3 chat-active-list">
                <strong class="text-primary d-block mb-2">Active Users</strong>
                <ul class="list-unstyled">
                    <li v-for="(usr, index) in users" :key="index">
                        {{ usr.name }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import { currentTime } from '../functions/utils';
import apiRequest from '../functions/apiRequest';
export default {

    name: 'Chat',
    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },
    props: {
        user: {
            type: Object,
            required: true
        },
        user_role: {
            type: String,
            required: true
        },
        chatlist: {
            type: Array,
            default: () => []
        },
        //
        // Chat popup
        // --------------------------------------------------------------------------
        popup: {
            type: Boolean,
            default: false
        },
        msgs: {
            type: Array,
            default: () => []
        },
        ctype: {
            type: String,
            default: ''
        },
        ctype_id: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            messages: this.msgs || [],
            newMessage: '',
            users: [],
            chatType: 0,
            chatID: 0,
            selectedChat: null,
            activeUser: false,
            typingTimer: false,
            showChat: true,
            showActiveUsers: false,
            loading: false,
            deleteNotice: {}
        };
    },
    created() {
        const vm = this;
        vm.loading = true;
        // Chat popup
        if(vm.ctype && vm.ctype_id) {
            vm.chatType = +vm.ctype;
            vm.chatID = +vm.ctype_id;
            vm.joinChat(+vm.ctype, +vm.ctype_id);
        }
        // Typically a student with only the class or team chat
        else if(vm.chatlist.length === 1) {
            vm.loadChat(vm.chatlist[0].ctype, vm.chatlist[0].ctype_id);
        // Load selection from cookie
        } else {
            vm.cookie = vm.getCookie();
            typeof vm.cookie.chatID !== 'undefined'
                && vm.loadChat(vm.cookie.chatType, vm.cookie.chatID);
        }
    },

    methods: {
        fetchMessages(chatType, chatID) {
            const url = `/msgs/${chatType}/${chatID}`;
            axios.get(url).then(response => {
                this.messages = response.data;
                this.loading = false;
            });
        },
        //
        // Send Message
        // --------------------------------------------------------------------------
        sendMessage() {
            if(this.newMessage.trim() === '') return;

            const vm = this;
            vm.thisMessage = vm.newMessage;
            vm.messages.push({
                ctype: vm.chatType,
                ctype_id: vm.chatID,
                user: vm.user,
                message: vm.newMessage,
                date: currentTime()
            });
            apiRequest('/msgs', {
                message: vm.newMessage,
                ctype: vm.chatType,
                ctype_id:  vm.chatID
            }).then(response => {
                // Find newly added message by content and user, add message.id via
                // response.id for later use in deleting a teacher's own messages
                vm.messages.find(
                    obj => (obj.message === vm.thisMessage) && (obj.user === vm.user)
                ).id = response.id;
                vm.thisMessage = '';
            });
            vm.newMessage = '';
        },
        //
        // Delete message
        // --------------------------------------------------------------------------
        deleteMessage(id) {
            const vm = this;
            vm.deleteNotice = {
                id: id,
                msg:'Message deleted.'
            };
            apiRequest('/delete/msgs', { id: id }).then(() => {
                vm.messages = vm.messages.filter(obj => obj.id !== id);
                setTimeout(() => vm.deleteNotice = {}, 1000);
            });
        },
        //
        // Send typingevent
        // --------------------------------------------------------------------------
        sendTypingEvent() {
            Echo.join(`chat.${this.chatType}.${this.chatID}`).whisper('typing', this.user);
        },
        //
        // Select chat
        // --------------------------------------------------------------------------
        selectChat(event) {
            const vm = this;
            // Emit selected chat to Dashboard, etc
            vm.$emit('select', {
                ctype: event.ctype,
                ctype_id: event.ctype_id
            });
            vm.chatType = event.ctype;
            vm.chatID = event.ctype_id;
            vm.joinChat(event.ctype, event.ctype_id);
        },
        //
        // Load chat
        // --------------------------------------------------------------------------
        loadChat(chatType, chatID) {
            const vm = this;
            vm.selectedChat = vm.chatlist.find(
                obj => (obj.ctype === chatType && obj.ctype_id === chatID)
            );
            typeof vm.selectedChat !== 'undefined' && vm.selectChat(vm.selectedChat);
        },
        //
        // Join chat
        // --------------------------------------------------------------------------
        joinChat(chatType, chatID) {
            const vm = this;
            vm.fetchMessages(chatType, chatID);
            typeof vm.cookie !== 'undefined' && vm.setCookie(vm.cookie, { chatType, chatID });
            Echo.join(`chat.${chatType}.${chatID}`)
                .here(user => {
                    vm.users = user;
                })

                .joining(user => {
                    vm.users.push(user);
                })

                .leaving(user => {
                    vm.users = vm.users.filter(u => u.id != user.id);
                })

                .listen('ChatEvent', event => {
                    let chat = event.chat;
                    chat.date = currentTime();
                    vm.messages.push(chat);
                })

                .listen('ChatDeleteEvent', event => {
                    vm.messages = vm.messages.filter(obj => obj.id !== event.id);
                })

                .listenForWhisper('typing', user => {
                    vm.activeUser = user;
                    if (vm.typingTimer) {
                        clearTimeout(vm.typingTimer);
                    }
                    vm.typingTimer = setTimeout(() => {
                        vm.activeUser = false;
                    }, 1000);
                });
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
