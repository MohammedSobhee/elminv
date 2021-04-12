<template>
<div>
    <div class="dashboard-messages">
        <div class="row justify-content-between align-items-end">
            <div class="col">
                <h4 class="dashboard-chat-header">
                    <i class="far fa-comments ml-1"></i> Live <span>Classroom </span>Discussion
                </h4>
            </div>
            <div v-if="chatID" class="col text-right">
                <span
                    v-if="user_role !== 'student'"
                    v-btooltip
                    title="Download log of this discussion"
                    class="btn btn-sm btn-light btn-hide-messages btn-open-chat"
                    @click="download">
                    <i class="fas fa-download"></i>
                </span>
                <span
                    v-btooltip
                    title="Open discussion in a popup window"
                    class="btn btn-sm btn-light btn-hide-messages btn-open-chat"
                    @click="openChatWindow">
                    <i class="fas fa-external-link-alt"></i>
                </span>
                <span
                    class="btn btn-sm btn-light btn-hide-messages"
                    @click="showChat = !showChat">
                    {{ showChat ? 'Hide' : 'Show' }} Discussion
                </span>
            </div>
        </div>
    </div>
    <hr v-show="!showChat" class="m-0">
    <chat v-show="showChat" v-bind="$props" @select="getClass"></chat>
</div>
</template>

<script>
import apiRequest from '../../functions/apiRequest';
export default {
    name: 'DashboardChat',

    props: {
        user: {
            type: Object,
            required: true
        },
        user_role: {
            type: String,
            required: true
        },
        msgs: {
            type: Array,
            default: () => []
        },
        chatlist: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            showChat: true,
            chatType: 0,
            chatID: 0
        };
    },
    methods: {
        openChatWindow() {
            // https://stackoverflow.com/a/16861050
            function popupWindow(url, title, win, w, h) {
                const y = win.top.outerHeight / 2 + win.top.screenY - h / 2;
                const x = win.top.outerWidth / 2 + win.top.screenX - w / 2;
                return win.open(
                    url,
                    title,
                    `toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=${w}, height=${h}, top=${y}, left=${x}`
                );
            }
            popupWindow(`/chat/${this.chatType}/${this.chatID}`, 'chat', window, 500, 665);
            //window.open('/chat', 'chat', 'width=500,height=475,directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no');
        },

        getClass(emit) {
            this.chatType = emit.ctype;
            this.chatID = emit.ctype_id;
        },

        download() {
            const vm = this;
            apiRequest('/download/chat', {
                ctype: vm.chatType,
                ctype_id: vm.chatID
            }).then(response => {
                const channel = vm.chatlist.find(
                    obj => obj.ctype === vm.chatType && obj.ctype_id === vm.chatID
                ).name;
                const url = window.URL.createObjectURL(new Blob([response]));
                const link = document.createElement('a');
                const filename = `Discussion Log For ${channel}.csv`;
                link.href = url;
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                link.click();
            });
        }
    }
};
</script>
