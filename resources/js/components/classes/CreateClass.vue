<template>
<div
    id="classAddModal"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    aria-labelledby="classAddModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="classAddModalLabel" class="modal-title">
                    Add Class
                </h5>
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/create/class" method="post" @submit="submitForm">
            <csrf-token />
            <div class="modal-body">
            <div class="row">
                <div :class="[class_types.length > 1 ? 'col-md-6' : 'col']" class="form-group">
                    <label class="label-status" data-status="class_name" for="class_name">Class Name:</label>
                    <input
                        id="class_name"
                        ref="class_name"
                        v-model="className"
                        type="text"
                        class="form-control"
                        name="class_name">
                        <em v-if="classNameError" class="error error-class_name">Class name exists</em>
                </div>
                <div v-if="class_types.length > 1" class="col-md-6 form-group">
                    <label class="label-status" data-status="class_type" for="class_type">Grade Level:</label>
                    <multiselect
                        id="class_type"
                        ref="class_type"
                        v-model="classType"
                        :options="class_types"
                        placeholder="Choose..."
                        track-by="id"
                        label="name"
                        :show-labels="false"
                        :close-on-select="true"
                        :searchable="false">
                    </multiselect>
                    <input
                        v-if="classType"
                        type="hidden"
                        name="class_type"
                        :value="classType.id">
                </div>
                <input
                    v-else
                    type="hidden"
                    name="class_type"
                    :value="class_types[0].id">
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                <input
                    type="submit"
                    name="submit"
                    class="btn btn-sm btn-primary"
                    value="Add Class">
            </div>
            </form>
        </div>
    </div>
</div>
</template>

<script>
import { elementExists } from '../../functions/utils';
export default {
    name: 'CreateSchool',

    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },

    props: {
        class_types: {
            type: Array,
            required: true
        },
        classes: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            classType: this.class_types.length > 1 ? null : true,
            className: '',
            classNameError: false
        }
    },

    watch: {
        className(val) {
            this.classNameError = false;
            if(this.classes.findIndex(obj => obj.class_name.toLowerCase() === val.toLowerCase()) !== -1) {
                this.classNameError = true;
            }
        }
    },

    mounted() {
        // Auto focus for class add modal - nothing else seems to work!
        $('*[data-target="#classAddModal"]').on('click', function() {
            elementExists('#classAddModal').then(function() {
                $('#classAddModal').on('shown.bs.modal', function() {
                    $(this).find('#class_name').focus();
                });
            });
        });
    },

    methods: {
        submitForm(event) {
            const vm = this;
            if(vm.className && vm.classType && !vm.classNameError) {
                return true;
            } else {
                Object.keys(vm.$refs).forEach(r => {
                    const el = document.querySelector(`.label-status[data-status="${r}"]`);
                    el.insertAdjacentHTML('afterend', '<em class="ml-2 error">Required</em>');
                });
            }

            event.preventDefault();

        }
    }
}
</script>
<style lang="scss" scoped>
.error-class_name {
    position: absolute;
    bottom: -1.5rem;
    left: .5rem;
}
</style>
