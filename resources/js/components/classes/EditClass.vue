<template>
<div>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a
                href="#"
                class="nav-link"
                :class="{ active: viewCodes }"
                @click.prevent="goto('add')">
                <i class="fas fa-user-plus"></i> Add Class Members
            </a>
        </li>
        <li class="nav-item">
            <a
                href="#"
                class="nav-link"
                :class="{ active: editClass }"
                @click.prevent="goto('edit')">
                <i class="fas fa-edit"></i> Edit or Delete Class
            </a>
        </li>
    </ul>


    <div ref="gotoArea" :class="{'border border-top-0 rounded-bottom p-3': viewCodes || editClass}">
        <template v-if="viewCodes">
            <span class="medium">Activation codes for this class:</span>
            <view-codes
                :host="host"
                :class_id="class_id"
                :class_type="class_type"
                :assistant_code="assistant_code"
                :student_code="student_code" />

            <div v-if="student_code" class="medium mt-4">
                <p class="mt-2"><a :href="`/upload/accounts/${class_id}`">Manually add or upload accounts <i class="fas fa-angle-right ml-1"></i></a></p>
            </div>
        </template>

        <div v-show="editClass">
            <form>
                <div class="row flex-row mt-2">
                    <div class="form-group col">
                        <label for="name">Edit Class Name:</label>
                        <input v-model="className" type="text" class="form-control" />
                        <em v-if="classNameError" class="error error-class_name">Class name exists</em>
                    </div>
                    <div v-if="classTypes.length > 1" class="form-group col">
                        <label for="class_type_level">Grade Level:</label>
                        <multiselect
                            v-model="classType"
                            :options="classTypes"
                            :searchable="false"
                            :close-on-select="true"
                            :show-labels="false"
                            label="name"
                            track-by="id"
                            placeholder="Choose...">
                        </multiselect>
                    </div>
                </div>
                <div class="row" style="min-height:50px">
                    <div class="col-md-9">
                        <div class="row">
                            <div v-if="user_role !== 'assistant-teacher'" class="col-lg-6">
                                <div class="form-check">
                                    <input
                                        id="delete_class"
                                        v-model="deleteClass"
                                        v-popoverCheck="'<span class=\'text-danger\'>Deleting a class deletes <strong>associated teams</strong> and their projects and cannot be undone.</span>'"
                                        type="checkbox"
                                        class="form-check-input"
                                        value="1"
                                        data-formtoggle="form_delete_students" />
                                    <label for="delete_class" class="form-check-label small text-muted">Delete this class</label>
                                </div>
                            </div>
                            <div
                                v-show="deleteClass"
                                class="col-lg-6 form-delete-class animated faster bounceInLeft">
                                <div class="form-check small">
                                    <input
                                        id="delete_unassign"
                                        v-model="deleteClassStudents"
                                        type="radio"
                                        value="1" />
                                    <label
                                        v-btooltip="{ title: `Place the students back into the pool of unassigned students above and remove this class's assignment from assistant teachers.` }"
                                        for="delete_unassign"
                                        class="form-check-label">Unassign members in this class</label>
                                </div>
                                <div v-if="user_role !== 'assistant-teacher'" class="form-check small">
                                    <input
                                        id="delete_deactivate"
                                        v-model="deleteClassStudents"
                                        type="radio"
                                        value="2" />
                                    <label
                                        v-btooltip="{ title: 'Deactivate students entirely (assistant teachers remain active.) Only school administrators will be able to edit and reactivate.' }"
                                        for="delete_deactivate"
                                        class="form-check-label">Deactivate students in this class</label>
                                </div>
                                <!-- <div v-if="user_role !== 'assistant-teacher'" class="form-check small">
                                    <input
                                        id="delete_delete"
                                        v-model="deleteClassStudents"
                                        type="radio"
                                        value="3" />
                                    <label
                                        v-btooltip="{ title: 'Delete all students in this class, their submitted assignments and grades.' }"
                                        for="delete_delete"
                                        class="form-check-label">Delete students in this class</label>
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-right">
                        <div v-if="saveForm" class="form-completed"></div>
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm"
                            @click="storeClass">
                            Save Class Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</template>


<script>
import apiRequest from '../../functions/apiRequest';

export default {
    name: 'EditClass',

    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },

    directives: {
        popoverCheck: {
            update: (el, binding, vnode) => {
                $(el).popover({
                    content: binding.value,
                    html: true
                });
                const data = vnode.context;
                if (!data.editClass) {
                    data.deleteClass = false;
                    $(el).popover('hide');
                }
            }
        }
    },

    props: {
        host: {
            type: String,
            required: true
        },
        classes: {
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
        class_type: {
            type: Number,
            required: true
        },
        student_code: {
            type: String,
            required: true
        },
        assistant_code: {
            type: String,
            required: true
        },
        show_codes: {
            type: Number,
            default: 0
        },
        class_types: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            classTypes: this.class_types,
            className: this.class_name,
            classType: this.class_types.find(obj => obj.id === this.class_type),
            classNameError: false,
            deleteClass: false,
            deleteClassStudents: 1,
            viewCodes: false,
            editClass: false,
            saveForm: false
        };
    },

    watch: {
        className(val) {
            this.classNameError = false;
            if(
                this.class_name !== val &&
                this.classes.findIndex(obj => obj.class_name.toLowerCase() === val.toLowerCase()) !== -1
            ) {
                this.classNameError = true;
            }
        }
    },

    created() {
        this.viewCodes = this.show_codes;
    },

    methods: {
        goto(action) {
            const vm = this;
            switch(action) {
                case 'add':
                    vm.viewCodes = !vm.viewCodes; vm.editClass = false;
                    break;
                case 'edit':
                    vm.editClass = !vm.editClass; vm.viewCodes = false;
                    break;
            }
            setTimeout(() => vm.$refs.gotoArea.scrollIntoView(
                { behavior: 'smooth', block: 'end', inline: 'nearest' }
            ), 50);
        },

        storeClass() {
            const vm = this;
            const request = {
                class_id: vm.class_id,
                class_name: vm.className,
                class_type: vm.classType.id
            };

            let postURL = '/update/class';

            if (vm.deleteClass === true) {
                postURL = '/delete/class';
                request.delete_class_students = vm.deleteClassStudents;
            }

            vm.className && apiRequest(postURL, request).then(() => {
                this.saveForm = true;
                if (this.deleteClass === true) {
                    location.href = '/deleted/class';
                } else {
                    location.href = '/edit/class/' + vm.class_id;
                }
            })
        }
    }
};
</script>

<style lang="scss" scoped>
.form-completed:after {
    width: 6rem;
    top: 2rem;
    left: auto;

    right: 0;
}
.error-class_name {
    position: absolute;
    bottom: -1.5rem;
    right: 0;
}
</style>
