<template>
<!-- Existing users listing -->
<div v-if="table" class="table-responsive">
    <table class="table">
        <thead class="add-accounts-header">
            <tr>
                <template v-for="header in headers">
                    <th v-if="viewable(header.key)" :key="header.key">
                    <a
                        href="#"
                        :class="{ active: sortActiveLink === header.key}"
                        @click.prevent="sortUsers(header.key); sortSwitch = !sortSwitch">
                        {{ header.label }}<i class="fas fa-sort"></i>
                    </a>
                    </th>
                </template>
            </tr>
        </thead>
        <tbody>
            <tr
                v-for="user in current"
                :key="user.id"
                class="row-file row-animate">
                <!-- Label -->
                <template v-for="(prop, propIndex) in Object.keys(user)">
                    <td v-if="viewable(prop)" :key="propIndex" class="medium">
                        <span v-if="prop === 'sent' && isEmail(user.email)" :class="{'text-success':user[prop]}">
                            <i v-if="user[prop]" class="fas fa-check"></i>
                            <span v-else>-</span>
                        </span>
                        <span v-else-if="prop === 'sent' && !isEmail(user.email)">N/A</span>
                        <span
                            v-else-if="prop === 'note'"
                            v-bpopover="user[prop]"
                            class="note-hover"
                            tabindex="0"
                            data-popover-title="Note">{{ user[prop] | shorten }}</span>
                        <span v-else-if="prop === 'role'">{{ user[prop].name }}</span>
                        <span v-else-if="prop === 'class_id'">{{ user.class_name }}</span>
                        <span v-else-if="prop === 'date'">{{ user[prop] | formatDate }}</span>
                        <span v-else>{{ user[prop] }}</span>
                    </td>
                </template>
            </tr>
        </tbody>
    </table>
</div>


<!-- Edit Users Headers -->
<div v-else>
    <div v-if="current.length" class="flex-row row add-accounts-header align-items-center pl-2 pr-3 pb-2">
        <template v-for="header in headers">
            <div v-if="viewable(header.key)" :key="header.key" class="col">
                <strong>
                    <a
                        href="#"
                        :class="{ active: sortActiveLink === header.key}"
                        @click.prevent="sortUsers(header.key); sortSwitch = !sortSwitch">
                        {{ header.label }}<i class="fas fa-sort"></i>
                    </a>
                </strong>
                <template v-if="!complete && !current.every(u => isEmail(u.email))">
                    <span v-if="header.key === 'email' && current.every(obj => obj.first_name && obj.last_name)" v-btooltip="{delay: 1000,title: generateTitle}">
                        <i class="fas fa-sync-alt icon-generate" @click="generateUsernames"></i>
                    </span>
                    <span v-else-if="header.key === 'email'" v-btooltip="{delay: 1000, title: generateTitle}">
                        <i class="fas fa-sync-alt icon-generate icon-generate-inactive"></i>
                    </span>
                    <i v-if="header.key === 'email' && current.some(u => u.error.includes('email'))" class="ml-2 fas fa-arrow-left text-danger small animated slow fadeInRight"></i>
                </template>
            </div>
        </template>
    </div>
    <transition-group
        :name="(animate ? 'row' : 'page')"
        class="row-transition"
        mode="out-in">
        <div
            v-for="user in current"
            :key="user.id"
            :class="{'flex-processed': complete}"
            class="pt-4 pt-lg-0 pb-2 row-file row-animate">
            <!-- Label -->
            <div class="flex-row row align-items-center pl-2" :class="[editing ? 'pr-2' : 'pr-3']">
                <template v-for="(prop, propIndex) in Object.keys(user)">
                    <div v-if="viewable(prop, user) && prop !== 'sent' && prop !=='note'" :key="propIndex" class="col form-group">
                        <span v-if="user.corrected === propIndex && !user.error.length && !complete" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                        <multiselect
                            v-if="prop === 'role'"
                            v-model="user[prop]"
                            :name="prop"
                            :options="user_roles"
                            :class="{'error': user.error.find(err => err === prop)}"
                            :show-labels="false"
                            track-by="id"
                            label="name"
                            class="multiselect-sm"
                            @input="inputCheck(user.id, propIndex, prop)">
                        </multiselect>
                        <multiselect
                            v-else-if="prop === 'class_id' && classes.length"
                            v-model="user[prop]"
                            :name="prop"
                            :options="classes"
                            :class="{'error': user.error.find(err => err === prop)}"
                            :show-labels="false"
                            track-by="id"
                            label="class_name"
                            class="multiselect-sm"
                            placeholder="Optional"
                            @input="inputCheck(user.id, propIndex, prop)">
                        </multiselect>
                        <input
                            v-else
                            v-model="user[prop]"
                            :name="prop"
                            :disabled="(prop === 'code' && user[prop] === null)"
                            type="text"
                            :placeholder="adding && (['password', 'note', 'class_id'].includes(prop)) ? 'Optional' : ''"
                            :class="{'error': user.error.find(err => err === prop)}"
                            class="form-control form-control-sm"
                            @change="inputCheck(user.id, propIndex, prop)"
                            @input="inputCheck(user.id, propIndex, prop)">
                            <!-- <span v-if="prop === 'class_id' && (!adding && !editing)" class="class-name">{{ user.class_name }}</span> -->
                    </div>
                    <div v-else-if="prop === 'note'" :key="propIndex" class="col form-group">
                        <span v-if="user.corrected === propIndex && !user.error.length && !complete" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                        <textarea
                            v-model="user[prop]"
                            :name="prop"
                            rows="1"
                            type="text"
                            :placeholder="adding && prop == 'note' ? 'Optional' : ''"
                            :class="{'error': user.error.find(err => err === prop)}"
                            class="form-control form-control-sm"
                            @input="inputCheck(user.id, propIndex, prop)">
                        </textarea>
                    </div>
                    <div v-else-if="prop === 'sent' && hasSent && isEmail(user.email) && editing" :key="propIndex" class="col form-check ml-2">
                        <input
                            :id="'sent'+user.id"
                            v-model="user[prop]"
                            :name="prop"
                            class="form-check-input"
                            type="checkbox"
                            @click="inputCheck(user.id, propIndex, prop)">
                        <label class="form-check-label small" :for="'sent'+user.id">Re-send Email</label>
                    </div>
                    <div v-else-if="viewable(prop) && prop === 'sent' && hasSent" :key="propIndex" class="col form-check ml-2">
                        &nbsp;
                    </div>
                    <div v-else-if="showBlankCol(prop)" :key="propIndex" class="col form-group">
                        &nbsp;
                    </div>
                </template>
                <i v-if="!complete && !editing" class="far fa-trash-alt delete-action" @click="deleteUser(user.id)"></i>
            </div>
        </div>
    </transition-group>
</div>
</template>

<script>
import { isEmail, shortenString, formatDate } from '../../functions/utils';
export default {
    name: 'AddAccountsList',
    filters: {
        shorten(str) {
            return shortenString(str);
        },
        formatDate(date) {
            return formatDate(date);
        }
    },
    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },
    props: {
        headers: {
            type: Array,
            required: true
        },
        current: {
            type: Array,
            required: true
        },
        classes: {
            type: Array,
            default: () => []
        },
        user_roles: {
            type: Array,
            default: () => []
        },
        table: {
            type: Boolean,
            default: false
        },
        class_id: {
            type: Number,
            default: 0
        },
        complete: {
            type: Boolean,
            default: false
        },
        editing: {
            type: Boolean,
            default: false
        },
        adding: {
            type: Boolean,
            default: false
        },
        animate: {
            type: Boolean,
            default: true
        },
        hasSent: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            sortSwitch: true,
            sortActiveLink: ''
        }
    },
    computed: {
        hasClassIDError() {
            return this.current.some(u => u.error.includes('class_id'))
        }
    },

    created() {
        // Request status animation css classes
        this.requestStatusCSS = 'form-request-status animated faster bounceInLeftShort complete';
        this.generateTitle = 'Optionally re-generate usernames using first and last names.';
    },

    methods: {
        //
        // (Not) Viewable Fields
        // --------------------------------------------------------------------------
        viewable(prop, user = null) {
            //TODO: Check class_id value and if has access but is not the same as vm.class_id, show
            let props = ['id', 'error', 'corrected', 'class_name', 'user_id', 'dual'];
            this.editing && props.push('password');
            //(this.class_id && prop === 'class_id') && !this.hasClassIDError && props.push('class_id');
            !this.table && props.push('date');
            if(user && typeof user.role !== 'undefined') {
                (!['student', 'assistant-teacher'].includes(user.role.slug)) && props.push('class_id');
            }
            !this.classes.length && props.push('class_id');
            return !props.includes(prop);
        },

        showBlankCol(prop) {
            return this.classes.length > 0 && prop === 'class_id' && (
                !this.current.every(u => ['student', 'assistant-teacher'].includes(u.role.slug))
                    && !this.current.some(u => u.class_id)
            )
        },

        isEmail(str) {
            return isEmail(str);
        },

        deleteUser(id) {
            this.$emit('delete', id)
        },

        // eslint-disable-next-line no-unused-vars
        inputCheck(userID, index, prop) {
            this.$emit('check', arguments);
        },

        generateUsernames() {
            this.$emit('usernames', 'generate');
        },

        sortUsers(key) {
            this.sortActiveLink = key;
            this.$emit('sort', {
                key: key,
                switch: key === 'date' ? !this.sortSwitch : this.sortSwitch // reverse for date as it's already sorted by desc
            });
        }
    }

}
</script>
