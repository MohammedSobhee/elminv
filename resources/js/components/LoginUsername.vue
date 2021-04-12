<template>
  <div class="row">
    <div class="col-md-6 m-auto center">
        <div class="box box-login">
            <form
                id="form-login"
                class="form-login form-placeholder"
                @submit.prevent>
                <csrf-token />
                <input type="hidden" name="school_id" :value="schoolID">
                <h3>
                    Sign in with
                    <img
                        v-if="driver === 'clever'"
                        src="/assets/images/layout/clever_logo.png"
                        width="70"
                        alt="Clever"
                        class="clever-logo">
                    <span v-else>{{ driverName }}</span>
                </h3>
                <div v-if="error" class="notification alert alert-danger alert-dismissible">
                    {{ error }}
                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="form-group">
                    <input
                        id="username"
                        v-model="username"
                        v-focus
                        type="text"
                        name="username"
                        class="form-control"
                        :placeholder="`${driverName} Username / Email`"
                        autocomplete="username"
                        @input="error = ''"
                        @keyup.enter="findSchool"
                        @change="findSchool">
                        <label v-if="!ready" for="email">Username</label>
                        <span v-if="username.length && ready" class="press-enter animated bounceInLeft">Press enter</span>
                </div>
                <div v-show="loading" class="loading-circle form-group"></div>
                <div v-if="schoolName" class="form-group school-name">
                    <strong class="text-primary">{{ schoolName }}</strong>
                </div>
                <!-- <div v-if="user" class="form-group mt-4">
                    <a :href="`/processlogin/${driver.toLowerCase()}/${username}`" class="btn btn-block btn-action">Login</a>
                </div> -->
            </form>
        </div>
        <div class="box-login p-2">
            <a href="/login" class="small"><i class="fas fa-angle-left"></i> Back to normal login</a>
        </div>
    </div>
</div>
</template>

<script>
import { capitalizeWords } from '../functions/utils';
import apiRequest from '../functions/apiRequest';

export default {
    name: 'LoginClever',
    directives: {
        focus: {
            inserted: (el, binding, vnode) => {
                setTimeout(() => {
                    vnode.context.username.length && (vnode.context.ready = true);
                    el.focus()
                }, 1000);
            }
        }
    },
    props: {
        driver: {
            type: String,
            required: true
        },
        user: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            driverName: capitalizeWords(this.driver),
            username: this.user,
            schoolName: '',
            schoolID: 0,
            loading: false,
            ready: false,
            error: ''
        }
    },
    watch: {
        username() {
            this.username.length && (this.ready = true);
        }
    },
    methods: {
        findSchool() {
            this.loading = true;
            this.error = '';
            apiRequest('/api/findschool', { username: this.username }).then(response => {
                if(response.error) {
                    this.error = response.error
                    this.loading = false;
                    return;
                }
                this.schoolName = response.school_name;
                this.schoolID = response.school_id
                this.loading = false;
                setTimeout(() =>
                    window.location.href=`/processlogin/${this.driver.toLowerCase()}/${this.username}`,
                800);
            });
        }
    }

}
</script>
<style lang="scss" scoped>
.clever-logo {
    position: relative;
    top: -.1rem;
    left: .1rem;
}
.loading-circle {
    width: 20px;
    height: 20px;
    margin: 0 auto;
    border-left-color: $primary;
}
 .school-name {
    min-height: 20px;
 }
 .press-enter {
     font-size: .7rem;
     color: $medium-gray;
     position: absolute;
     bottom: -1.35rem;
     right: 0;
 }
</style>
