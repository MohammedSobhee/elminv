<template>
<div v-if="hasMessages" class="dashboard-messages">
<div class="row justify-content-between align-items-end">
    <div class="col">
        <h4 class="mt-0 mb-2">
            <i class="fas fa-envelope-open-text ml-1"></i> Messages:
            <span v-if="checkRecent()" class="text-success small">(You have new messages!)</span>
        </h4>
    </div>
    <div class="col text-right">
        <span
            class="btn btn-sm btn-light btn-hide-messages"
            @click="toggleMessages">
            {{ showMessages ? 'Hide' : 'Show' }} Messages
        </span>
    </div>
</div>
<hr v-show="!showMessages" class="m-0">

<div v-show="showMessages" class="editor-message-display dashboard-alert-wrapper">
    <div v-for="(messages, index) in allMessages" :key="index">
        <div v-if="messages.data.length" class="mb-3 border p-3 dashboard-alert" role="alert">
            <div class="alert-heading">
                <strong v-if="messages.type == 'user'">Your messages:</strong>
                <strong v-else-if="messages.type == 'team'">Team messages:</strong>
                <strong v-else-if="messages.type == 'class'">Class messages:</strong>
            </div>
            <div
                v-for="message in messages.data"
                :key="message.id">
                <div class="dashboard-alert-content">
                    <div class="text-muted small dashboard-alert-updated">
                        <span :class="{'text-success font-weight-bold': checkRecent(message.updated_at)}">Updated {{ message.updated }}</span>
                    </div>
                    <p v-html="message.content"></p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</template>

<script>
import { getDifferenceInDays } from '../../functions/utils';
import { getDaysOld, numRecentFromList } from '../../functions/messagesUtils';

export default {
    name: 'DashboardMessages',

    props: {
        user_messages: {
            type: Array,
            required: true
        },
        team_messages: {
            type: Array,
            required: true
        },
        class_messages: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            showMessages: true,
            allMessages: [
                {
                    type: 'user',
                    data: this.user_messages
                },
                {
                    type: 'team',
                    data: this.team_messages
                },
                {
                    type: 'class',
                    data: this.class_messages
                }
            ]
        };
    },
    computed: {
        hasMessages() {
            const vm = this;
            return vm.user_messages.length || vm.team_messages.length || vm.class_messages.length;
        }
    },

    created() {
        this.cookie = this.getCookie();
        if(this.checkRecent()) {
            this.showMessages = true;
            this.sortMessageLists();
        } else {
            (typeof this.cookie.showMessages !== 'undefined') &&
                (this.showMessages = this.cookie.showMessages)
        }
    },

    methods: {
        toggleMessages() {
            const vm = this;
            vm.showMessages = !vm.showMessages;
            const showMessages = vm.showMessages;
            vm.setCookie(vm.cookie, { showMessages });
        },

        checkRecent(date = '') {
            if(date) {
                return getDifferenceInDays(date) >= getDaysOld();
            } else {
                return (
                    numRecentFromList(this.user_messages) +
                    numRecentFromList(this.team_messages) +
                    numRecentFromList(this.class_messages)
                )
            }
        },

        sortMessageLists() {
            this.allMessages.sort((a, b) => numRecentFromList(b.data) - numRecentFromList(a.data));
        }
    }
};
</script>
