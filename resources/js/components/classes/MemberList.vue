<template>
    <div>
        <h4>
            Assigned Class Members
        </h4>
        <div v-if="usersAssigned.length > 0">
            <!-- <p>Click the students and assistant teachers you'd like to edit or remove from this class.</p> -->
            <edit-member
                :users="usersAssigned"
                :student_id="student_id"
                :class_id="class_id"
                :assigned="1"
                :enable_search="true"
                class="mt-3"
                @switched="updateUsers">
            </edit-member>
        </div>
        <div v-else>
            <p>There are no assigned members.</p>
        </div>


        <div v-if="usersUnassigned.length > 0">
            <!-- <hr class="mt-lg-5 mb-3" /> -->
            <div class="mt-5 text-primary">
                Unassigned Members
            </div>
            <!-- <button
                v-if="initialNumAssigned > 0"
                type="button"
                class="btn btn-light btn-sm mb-2"
                :class="{ focus: viewUnassigned }"
                @click="viewUnassigned = !viewUnassigned">
                Edit Members or Assign
                <i class="fas" :class="[viewUnassigned ? 'fa-caret-up' : 'fa-caret-down']"></i>
            </button> -->
            <edit-member
                v-show="viewUnassigned"
                :users="usersUnassigned"
                :class_id="class_id"
                :enable_search="true"
                class="mt-1"
                @switched="updateUsers">
            </edit-member>
        </div>
  </div>
</template>

<script>
export default {
    name: 'MemberList',

    // components: {
    //     EditMember: () => import('./EditMember')
    // },

    props: {
        users: {
            type: Array,
            required: true
        },
        users_assigned: {
            type: Array,
            required: true
        },
        class_id: {
            type: Number,
            required: true
        },
        class_name: {
            type: String,
            required: true
        },
        student_id: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            usersUnassigned: this.users,
            usersAssigned: this.users_assigned,
            initialNumAssigned: 0,
            viewUnassigned: true
        };
    },

    created() {
        const vm = this;
        vm.usersUnassigned = vm.usersUnassigned.filter(u => { // Hide assistant teachers from an assistant teacher
            if(u.role.id === 4 || (u.role.id === 7 && vm.user_role === 'teacher')) {
                return u;
            }
        });
        //vm.viewUnassigned = vm.usersAssigned.length <= 0; // Show unhidden unassigned list if no assigned
        vm.initialNumAssigned = vm.users_assigned.length; // Don't show unassigned button if no assigned
    },

    methods: {
        updateUsers(data) {
            const vm = this;
            if (data.assigned === 1) {
                vm.switchUser(vm.usersAssigned, vm.usersUnassigned, data.user, 'removed');
                vm.viewUnassigned = true;
            } else {
                vm.switchUser(vm.usersUnassigned, vm.usersAssigned, data.user, 'assigned');
            }
        },
        switchUser(sArray, uArray, user, status) {
            // TODO: Fix status so it's status = assigned or something
            sArray.splice(sArray.findIndex(obj => obj.id === Number(user.id)), 1);
            user[status] = true;
            if(status === 'assigned') {
                user['removed'] = false;
                user.cls = { id: this.class_id }
            } else {
                user.isAssigned = false;
                user.cls = null;
                user['assigned'] = false;
            }
            uArray.unshift(user);
            uArray.sort((a, b) => {
                return (
                    Number(a.role.id) - Number(b.role.id) || a.fullname.localeCompare(b.fullname)
                );
            });
        }
    }
};
</script>
