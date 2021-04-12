<template>
    <!-- Absolute Notification -->
    <div v-if="type === 'absolute'" class="notification-wrapper">
        <transition
            name="message"
            enter-active-class="animated bounceInLeft"
            leave-active-class="animated fadeOut">
                <div
                    v-if="msg.text.length"
                    class="notification"
                    :class="[msg.color, 'notification-' + position]">
                    <template v-if="typeof msg.text === 'object'">
                        <span v-for="(str, index) in msg.text" :key="index">
                            {{ str }}
                        </span>
                    </template>
                    <template v-else>
                        {{ msg.text }}
                    </template>
                </div>
        </transition>
    </div>

    <!-- Bootstrap Notification -->
    <div v-else-if="type === 'bootstrap'">
        <transition
            name="message"
            enter-active-class="animated bounceInLeft"
            leave-active-class="animated fadeOut">
            <div
                v-if="msg.text.length"
                class="alert alert-dismissible"
                :class="msg.bgColor">
                <template v-if="typeof msg.text === 'object'">
                    <span v-for="(str, index) in msg.text.length" :key="index">
                        {{ str }}
                    </span>
                </template>
                <template v-else>
                    {{ msg.text }}
                </template>
                <button
                    type="button"
                    class="close"
                    data-dismiss="alert"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: 'Notification',
    props: {
        type: {
            type: String,
            default: 'absolute'
        },
        msg: {
            type: Object,
            required: true
        },
        position: {
            type: String,
            default: 'right'
        }
    }
}
</script>
