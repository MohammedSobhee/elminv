//import './mixfix.scss';
require('./bootstrap');
window.Vue = require('vue');

//
// Register components
// --------------------------------------------------------------------------
Vue.component('add-accounts', () => import(/* webpackChunkName:"add-accounts" */ './components/addaccounts/AddAccounts'));
Vue.component('assignments', () => import(/* webpackChunkName:"assignments" */ './components/assignments/Assignments'));
Vue.component('assignments-custom', () => import(/* webpackChunkName:"assignments-custom" */ './components/assignments/AssignmentsCustom'));
Vue.component('assignments-activity', () => import(/* webpackChunkName:"assignments-activity" */ './components/assignments/AssignmentsActivity'));
Vue.component('chat', () => import(/* webpackChunkName:"chat" */ './components/Chat'));
Vue.component('collapse', () => import(/* webpackChunkName:"collapse" */ './components/common/Collapse'));
Vue.component('create-class', () => import(/* webpackChunkName:"create-class" */ './components/classes/CreateClass'));
Vue.component('create-school', () => import(/* webpackChunkName:"create-school" */ './components/admin/CreateSchool'));
Vue.component('dashboard-assignments', () => import(/* webpackChunkName:"dashboard-assignments" */ './components/dashboard/DashboardAssignments'));
Vue.component('dashboard-activity', () => import(/* webpackChunkName:"dashboard-activity" */ './components/dashboard/DashboardActivity'));
Vue.component('dashboard-custom', () => import(/* webpackChunkName:"dashboard-custom" */ './components/dashboard/DashboardCustom'));
Vue.component('dashboard-chat', () => import(/* webpackChunkName:"dashboard-chat" */ './components/dashboard/DashboardChat'));
Vue.component('dashboard-pending', () => import(/* webpackChunkName:"dashboard-pending" */ './components/dashboard/DashboardPending'));
Vue.component('dashboard-messages', () => import(/* webpackChunkName:"dashboard-messages" */ './components/dashboard/DashboardMessages'));
Vue.component('date-input', () => import(/* webpackChunkName:"date-input" */ './components/common/DateInput'));
Vue.component('edit-class', () => import(/* webpackChunkName:"edit-class" */ './components/classes/EditClass'));
Vue.component('edit-member', () => import(/* webpackChunkName:"edit-member" */ './components/common/EditMember'));
Vue.component('edit-school', () => import(/* webpackChunkName:"edit-school" */ './components/admin/EditSchool'));
Vue.component('edit-account', () => import(/* webpackChunkName:"edit-account" */ './components/EditAccount'));
Vue.component('edit-team', () => import(/* webpackChunkName:"edit-team" */ './components/EditTeam'));
Vue.component('editor-wysiwyg', () => import(/* webpackChunkName:"editor-wysiwyg" */ './components/editor/EditorWysiwyg'));
Vue.component('editor-icons', () => import(/* webpackChunkName:"editor-icons" */ './components/editor/EditorIcons'));
Vue.component('editor-input', () => import(/* webpackChunkName:"editor-input" */ './components/editor/EditorInput'));
Vue.component('gradebook', () => import(/* webpackChunkName:"gradebook" */ './components/gradebook/Gradebook'));
Vue.component('info-alert', () => import(/* webpackChunkName:"info-alert" */ './components/common/InfoAlert'));
Vue.component('login-username', () => import(/* webpackChunkName:"login-username" */ './components/LoginUsername'));
Vue.component('member-list', () => import(/* webpackChunkName:"member-list" */ './components/classes/MemberList'));
Vue.component('messages', () => import(/* webpackChunkName:"messages" */ './components/Messages'));
Vue.component('manage-assignments', () => import(/* webpackChunkName:"manage-assignments" */ './components/ManageAssignments'));
Vue.component('manage-duedates', () => import(/* webpackChunkName:"manage-duedates" */ './components/ManageDueDates'));
Vue.component('pagination', () => import(/* webpackChunkName:"pagination" */ './components/common/Pagination'));
Vue.component('popover-dialogue', () => import(/* webpackChunkName:"popover-dialogue" */ './components/common/PopoverDialogue'));
Vue.component('settings-teacher', () => import(/* webpackChunkName:"settings-teacher" */ './components/SettingsTeacher'));
Vue.component('school-select', () => import(/* webpackChunkName:"school-select" */ './components/admin/SchoolSelect'));
Vue.component('video-conference', () => import(/* webpackChunkName:"video-conference" */ './components/common/VideoConference'));
Vue.component('view-codes', () => import(/* webpackChunkName:"view-codes" */ './components/common/ViewCodes'));
Vue.component('vue-select', () => import(/* webpackChunkName:"vue-select" */ './components/common/VueSelect'));
Vue.component('worksheet', () => import(/* webpackChunkName:"worksheet" */ './components/Worksheet'));

//
// CSRF Token Component
// --------------------------------------------------------------------------
Vue.component('csrf-token', {
    data: function() {
        return {
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
    },
    template: `<input type="hidden" name="_token" :value="csrf">`
});

//
// Register Global Utility Mixin
// --------------------------------------------------------------------------
import utilityMixin from './mixins/utilityMixin';
Vue.mixin(utilityMixin);

//
// Chat Scroll
// --------------------------------------------------------------------------
import VueChatScroll from 'vue-chat-scroll';
Vue.use(VueChatScroll);

//
// Create Vue Instance
// --------------------------------------------------------------------------
new Vue({
    el: '#app'
});


//
// Import Misc jQuery
// --------------------------------------------------------------------------
require('./app.lvl.jq');
