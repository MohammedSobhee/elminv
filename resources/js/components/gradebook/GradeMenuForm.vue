<template>
<div class="menu-grade-form" @click.prevent.stop="">
    <div class="form-row">
        <div
            v-if="classItem.asgmtType && classItem.userType == 'team' && classItem.teamList"
            class="col-auto">
            <label class="menu-grade-label">Grading:</label>
            <multiselect
                v-if="classItem.teamList.length > 1"
                v-model="classItem.slcdUser"
                v-tooltip:teamlist="'Hover over selection for a few moments to get a list of members.'"
                :options="classItem.teamList"
                :searchable="false"
                :close-on-select="true"
                :allow-empty="false"
                class="multiselect-xs multiselect-user"
                :max-height="400"
                :show-labels="false"
                label="team_name"
                placeholder="Type"
                @select="menuGradeSelect($event, classItem.id, 'user')">
                <template
                    slot="singleLabel"
                    slot-scope="{ option }">
                    <span id="members" v-btooltip="{delay:1000,title: classItem.teamList[0].members.join('<br>')}">{{ option.team_name }}</span>
                </template>
            </multiselect>
            <span
                v-else
                id="members"
                v-btooltip="{title: classItem.teamList[0].members.join('<br>')}"
                class="menu-single">{{ classItem.teamList[0].team_name }}</span>
        </div>
        <div
            v-if="classItem.asgmtType && classItem.userType == 'user' && classItem.userList"
            class="col-auto">
            <label class="menu-grade-label">Grading:</label>
            <multiselect
                v-if="classItem.userList.length > 1"
                v-model="classItem.slcdUser"
                :options="classItem.userList"
                :searchable="false"
                :close-on-select="true"
                :allow-empty="false"
                class="multiselect-xs multiselect-user"
                :max-height="400"
                open-direction="bottom"
                :show-labels="false"
                label="fullname"
                placeholder="Type"
                @select="menuGradeSelect($event, classItem.id, 'user')">
                <template
                    v-for="slotName in ['option', 'singleLabel']"
                    :slot="slotName"
                    slot-scope="props">
                    {{ props.option.fullname }}
                    <!-- eslint-disable-next-line vue/require-v-for-key -->
                    <span v-if="props.option.pendingAsgmts" class="ml-1 badge badge-pill badge-pending small">{{ props.option.pendingAsgmts }}</span>
                </template>
            </multiselect>
            <span v-else class="menu-single">{{ classItem.userList[0].fullname }}</span>
        </div>
        <div
            v-if="classItem.asgmtType && categoryName == 'Classwork'"
            class="col-auto">
            <label class="menu-grade-label">Type:</label>
            <multiselect
                v-model="classItem.asgmtType"
                :options="assignmentTypes"
                :searchable="false"
                :close-on-select="true"
                :allow-empty="false"
                class="multiselect-xs"
                open-direction="bottom"
                :show-labels="false"
                placeholder="Type"
                @select="menuGradeSelect($event, classItem.id, 'assignment')">
            </multiselect>
        </div>
        <!-- <div
            v-if="classItem.asgmtType"
            class="col-auto">
            <label class="menu-grade-label">Filter:</label>
            <multiselect
                v-model="classItem.filterType"
                :options="filterTypes"
                :searchable="false"
                :close-on-select="true"
                class="multiselect-xs multiselect-filter"
                open-direction="bottom"
                :show-labels="false"
                placeholder="Type"
                @select="menuGradeSelect($event, classItem.id, 'filter')">
            </multiselect>
        </div> -->
    </div>
</div>
</template>

<script>
//import { elementExists } from '../../functions/utils';

export default {
    name: 'GradeMenuForm',

    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },

    // directives: {
    //     tooltip: {
    //         bind: (el, binding, vnode) => {
    //             const vm = vnode.context;
    //             elementExists('#members').then(elm => {
    //                 let show = 500,
    //                     hide = 2000;

    //                 !$('.tooltip').length &&
    //                     vm.cookie.tooltip.teamlist >= vm.tooltipsNum &&
    //                     ([show, hide] = [2000, 500]);

    //                 $(el)
    //                     .tooltip({
    //                         title: () => {
    //                             return !$('.tooltip').length &&
    //                                 vm.cookie.tooltip.teamlist >= vm.tooltipsNum
    //                                 ? elm.dataset.title
    //                                 : binding.value;
    //                         },
    //                         trigger: 'hover focus',
    //                         html: true,
    //                         delay: {
    //                             show: show,
    //                             hide: hide
    //                         }
    //                     })
    //                     .on('shown.bs.tooltip', () => vm.setTooltipCookie(vm.cookie, binding.arg));
    //             });
    //         },
    //         updated: el => {
    //             const title = document.querySelector('#members').dataset.title;
    //             el.setAttribute('data-original-title', title);
    //         }
    //     }
    // },

    props: {
        classItem: {
            type: Object,
            required: true
        },

        categoryName: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            filterTypes: ['All', 'Pending', 'Graded'],
            assignmentTypes: ['Activity', 'Custom']
        };
    },

    methods: {
        //
        // Grade Menu Select dropdown
        // --------------------------------------------------------------------------
        // eslint-disable-next-line no-unused-vars
        menuGradeSelect(value, classID, selectTarget) {
            this.$emit('menuUpdate', arguments);
        }
    }
};
</script>
