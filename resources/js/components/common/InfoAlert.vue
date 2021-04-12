<template>
    <div v-if="!close">
        <div class="info-alert">
            <i :class="icon" class="info-icon"></i>
            <slot></slot>
            <i v-if="['chat', 'videocon', 'message'].includes(type)" class="info-close fas fa-times" @click="closeAlert(type)"></i>
        </div>
    </div>
</template>

<script>
import apiRequest from '../../functions/apiRequest'
export default {
    name: 'InfoAlert',
    props: {
        icon: {
            type: String,
            default: 'fas fa-info-circle'
        },
        type: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            close: false
        }
    },
    methods: {
        closeAlert(type) {
            this.close = true;
            if(type === 'chat') {
                apiRequest('/save/settings', { name: 'chat_team', value: 0 });
                apiRequest('/save/settings', { name: 'chat_class', value: 0 });
            }
            if(type === 'videocon') {
                apiRequest('/save/settings', { name: 'videocon_google', value: 0 });
                apiRequest('/save/settings', { name: 'videocon_zoom', value: 0 });
            }
            if(type === 'message') {
                apiRequest('/save/settings', { name: 'message_class', value: 0 });
            }
        }
    }
}
</script>
