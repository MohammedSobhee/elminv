<template>
    <div>
        <button
            type="button"
            class="btn btn-sm btn-primary mr-2"
            data-toggle="modal"
            data-target="#videocon1Modal">
            Start
        </button>
        <video-conference :chatlist="chatlist"></video-conference>
    </div>
</template>

<script>
export default {
    name: 'Test1',
    components: {
        VideoConference: () => import(/* webpackChunkName:"video-conference" */ './common/VideoConference')
    },
    props: {
        chatlist: {
            type: Array,
            default: () => []
        }
    }

}
</script>

<style lang="scss" scoped>

</style>
