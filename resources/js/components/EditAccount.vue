<template>
<div class="form-label-status medium">
    <form @submit.prevent>
    <div class="form-group form-group-avatar">
        <img
            v-if="avatar && !setAvatar"
            :src="avatar"
            width="75"
            height="75">
        <a
            v-if="!setAvatar"
            href="#"
            :class="{'ml-4': avatar}"
            @click="setAvatar = !setAvatar">Set Avatar<i class="fas fa-cloud-upload-alt ml-1"></i></a>
        <i v-if="changedAvatar && !setAvatar" class="fas fa-check text-secondary"></i>
        <a
            v-if="avatar && !setAvatar"
            name="remove_avatar"
            class="ml-3"
            href="#"
            @click="save">Remove Avatar<i class="fas fa-trash-alt ml-1"></i></a>

        <div v-if="setAvatar">
            <div class="d-flex flex-row align-items-center">
                <div class="position-relative">
                    <vue-avatar
                        id="vueavatar"
                        ref="vueavatar"
                        :width="200"
                        :height="200"
                        :scale="scale"
                        @imageLoaded="imageLoaded"
                        @vue-avatar-editor:image-ready="onImageReady">
                    </vue-avatar>
                    <div>
                        <label for="vueavatar" class="label-zoom small mb-0"> Zoom : {{ scale }}x</label>
                    </div>
                    <div>
                        <input
                            v-model.number="scale"
                            style="width: 100%;"
                            type="range"
                            :min="1"
                            :max="3"
                            :step="0.02" />
                    </div>
                    <div>
                        <button class="btn btn-xs btn-primary btn-save-avatar" @click="saveAvatar">Save Avatar</button>
                    </div>
                </div>
                <div class="flex-grow-1 text-center text-primary">
                    <h4 v-if="loadedAvatar"><i class="fas fa-arrow-left"></i> Move and zoom the image to set its position</h4>
                    <h4 v-else><i class="fas fa-arrow-left"></i> Click the image to upload</h4>
                </div>
            </div>
        </div>
    </div>
    <notification :msg="notice" />
    <div class="form-group">
        <label class="label-status" for="nickname">Nickname</label>
        <i v-if="user_role === 'teacher' || user_role === 'assistant-teacher'" v-btooltip="{delay:100,title: `Used in chat and references to you such as the 'Send to ${updatedUser.nickname}' button in student activity sheets.`}" class="fas fa-question-circle"></i>
        <input
            id="nickname"
            v-model="updatedUser.nickname"
            type="text"
            name="nickname"
            autocomplete="nickname"
            class="form-control"
            placeholder="Nickname"
            @change="save">
    </div>
    <div v-if="user_role !== 'student'" class="row">
        <div class="form-group col">
            <label class="label-status" for="first_name">First Name</label>
            <input
                id="first_name"
                v-model="updatedUser.first_name"
                type="text"
                name="first_name"
                autocomplete="first_name"
                class="form-control"
                placeholder="First Name"
                @change="save">
        </div>
        <div class="form-group col">
            <label class="label-status" for="last_name">Last Name</label>
            <input
                id="last_name"
                v-model="updatedUser.last_name"
                type="text"
                name="last_name"
                autocomplete="last_name"
                class="form-control"
                placeholder="Last Name"
                @change="save">
        </div>
    </div>
    <div class="row">
        <div v-if="user_role !== 'student'" class="form-group col">
            <label class="label-status" for="email">Username / Email</label>
            <input
                id="email"
                v-model="updatedUser.email"
                type="text"
                name="email"
                autocomplete="email"
                class="form-control"
                placeholder="Username / Email"
                @change="save">
        </div>
    </div>
    <div v-if="user_role !== 'student' || (user_role === 'student' && !updatedUser.provider_id)" class="row">
        <div class="form-group col">
            <label class="label-status" for="password">Change Password</label>
            <input
                id="password"
                v-model="updatedUser.password"
                type="password"
                name="password"
                autocomplete="new-password"
                class="form-control"
                placeholder="New Password">
        </div>
        <div class="form-group col">
            <label class="label-status" for="confirm_password">Confirm Password</label>
            <input
                id="confirm_password"
                v-model="updatedUser.confirm_password"
                type="password"
                name="confirm_password"
                autocomplete="new-password"
                class="form-control"
                placeholder="Confirm Password"
                @change="save" />
        </div>
    </div>
    <div class="form-group">
        <button type="button" class="btn btn-sm btn-secondary" @click="checkForm">Save Changes</button>
        <span v-if="saveForm" class="text-success ml-2">Saved <i class="fas fa-check"></i></span>
    </div>
    </form>
</div>
</template>
<script>
import apiRequest from '../functions/apiRequest';
import notify from '../mixins/notify';
import { waitForThis, passwordCheck } from '../functions/utils';
export default {
    name: 'EditAccount',
    components: {
        VueAvatar: () => import(/* webpackChunkName:"vue-avatar" */ './common/VueAvatar')
    },
    mixins: [notify],
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            updatedUser: this.user,
            avatar: '',
            setAvatar: false,
            changedAvatar: false,
            loadedAvatar: false,
            saveForm: false,
            scale: 1,
            saved: false
        }
    },
    mounted() {
        this.getAvatar();
    },
    methods: {
        save(event) {
            const vm = this;
            const et = event.target
            let request = {
                user_id: vm.user_id
            }
            vm.saved = false;
            request[et.name] = et.name === 'remove_avatar' ? 1 : et.value;

            if(request.remove_avatar) vm.avatar = '';

            if (vm.updatedUser.password !== vm.updatedUser.confirm_password) {
                vm.notify('Passwords are not the same.', 'danger');
                return;
            }

            apiRequest('/update/user', request).then(response => {
                vm.saved = true;
                if(response.success) {
                    vm.notify('');
                    if(!request.remove_avatar) {
                        const el = document.querySelector(`.label-status[for="${et.name}"]`);
                        el.classList.add('saved-data');
                    }
                    if(request.nickname || request.remove_avatar) {
                        setTimeout(() => window.location.reload(true), 500);
                    }
                } else {
                    vm.notify(Object.values(response.data.errors)[0], 'danger');
                }
            });
        },

        //
        // Check password
        // --------------------------------------------------------------------------
        checkPass() {
            const vm = this;
            if(!passwordCheck.validate(vm.updatedUser.password)) {
                vm.notify(passwordCheck.message, 'danger');
                return;
            } else {
                vm.notify('');
            }
        },

        //
        // Check if Saved indicator can be shown
        // --------------------------------------------------------------------------
        checkForm() {
            this.loadedAvatar && this.saveAvatar();
            waitForThis(() => this.saved && !this.hasErrors, () => this.saveForm = true);
        },

        //
        // Save Avatar
        // --------------------------------------------------------------------------
        saveAvatar() {
            const vm = this;
            const img = vm.$refs.vueavatar.getImageScaled();
            const createdImage = img.toDataURL();
            const request = {
                user_id: vm.user_id,
                avatar: createdImage
            }

            vm.avatar = createdImage;
            vm.changedAvatar = true;

            apiRequest('/update/user', request).then(response => {
                if(response.success) {
                    this.setAvatar = false;
                    setTimeout(() => window.location.reload(true), 500);
                } else {
                    vm.notify(response.data.error, 'danger');
                }
            });
        },

        imageLoaded() {
            this.loadedAvatar = true;
        },
        onImageReady() {
            this.scale = 1;
        },
        getAvatar() {
            if(this.user.avatar) {
                this.avatar = !this.user.avatar || this.user.avatar.indexOf('https://') !== -1
                    ? this.user.avatar
                    : '/avatars/' + this.user.avatar;
            }
        }
    }
}
</script>
<style lang="scss" scoped>
.form-group-avatar {
  border: dashed $input-border-color 2px;
  border-radius: $global-radius;
  padding: 1rem;
}
</style>
