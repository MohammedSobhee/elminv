<template>
    <div>
        <!-- <hr class="mb-3 mt-lg-5"> -->
        <div
            class="form-wrapper pr-4 pl-4 pt-4 pb-2">
            <div
                v-for="(cat, index) in compcats"
                :key="cat.id"
                class="pl-1 pb-3 position-relative"
                :class="[
                    !cat.active && 'medium',
                    (type === 'worksheets' && cat.active) && 'text-right'
                ]">
                <input
                    v-if="cat.category_name !== 'Classwork' && user_role === 'teacher'"
                    v-model="cat.active"
                    type="checkbox"
                    :value="cat.active ? 0 : 1"
                    class="form-check-switch"
                    @change="activateCategory(cat.id, $event)">
                <i
                    v-if="type !== 'worksheets' && cat.category_name !== 'Classwork' && user_role === 'teacher'"
                    :class="!cat.active && 'position-relative ml-2'"
                    class="far fa-trash-alt slider-trash"
                    @click="deleteCategory(cat.id)">
                </i>
                <span
                    v-if="type === 'worksheets'"
                    :class="{ 'ml-1': !cat.active }"
                    class="text-success">{{ categoryPercentage[cat.id] }}%</span>
                <strong
                    :class="[
                        !cat.active && 'text-muted font-weight-normal',
                        !cat.active ? 'ml-1' : 'ml-3']">
                        {{ cat.category_name }}
                        <!-- <a v-if="type === 'worksheets' && cat.active" :href="`/edit/duedates/${cat.id}/1`" class="ml-2 text-gray font-weight-normal small">Due Date <i class="fa fa-angle-right" aria-hidden="true"></i></a> -->
                        <template v-if="type === 'worksheets' && cat.active">
                            <a
                                href="#"
                                class="text-gray btn-duedate small ml-2"
                                @click.prevent.stop="toggleDueDate(cat.id)">Due Date<i class="far fa-calendar-alt ml-1" aria-hidden="true"></i>
                            </a>
                            <div v-closeDueDate>
                                <due-dates
                                    v-if="dueDate === cat.id"
                                    :assignment="cat"
                                    :type="1"
                                    class="border card-box-shadow rounded duedate-wrapper small" />
                            </div>
                        </template>
                </strong>
                <vue-slider
                    v-if="cat.active && type === 'worksheets'"
                    :id="cat.id"
                    v-model="cat.category_value"
                    :disabled="user_role === 'assistant-teacher'"
                    :value="cat.category_value"
                    :lazy="true"
                    :interval="5"
                    :tooltip="'always'"
                    @dragging="val => updateNumbers(val, cat.category_value, cat.id)"
                    @change="updateWorksheet($event, cat.id)">
                </vue-slider>
                <vue-slider
                    v-else-if="cat.active"
                    :id="cat.id"
                    v-model="cat.category_value"
                    :disabled="user_role === 'assistant-teacher'"
                    :value="cat.category_value"
                    :lazy="true"
                    :interval="5"
                    :tooltip="'always'"
                    :tooltip-formatter="percentageFormatter"
                    @dragging="val => updateNumbers(val, cat.category_value)">
                </vue-slider>
                <div v-if="index === firstInactive - 1" class="text-right mt-2">
                    <strong>Total:
                        <span
                            :class="[(type !== 'worksheets' && rubricTotalDisplay !== 100) ? 'text-danger' : 'text-success']">
                            {{ rubricTotalDisplay + (type !== 'worksheets' && '%') }}
                        </span>
                    </strong>
                    <notification :msg="notice" class="medium" />
                    <div v-if="user_role === 'teacher'">
                        <button class="btn btn-sm btn-primary" @click="updateCategory">
                            Save {{ type === 'worksheets' ? 'Activity Sheet' : 'Rubric' }} Settings
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="add" class="py-3 border-top">
                <div class="form-inline">
                    <input
                    v-model="rubricNewName"
                    type="text"
                    placeholder="Category Name"
                    class="form-control form-control-sm"
                    @keyup.tab="addCategory"
                    @keyup.enter="addCategory">
                    <!-- <small id="emailHelp" class="form-text text-muted ml-2">
                        Label and assign it a value.
                    </small> -->
                    <button class="ml-2 btn btn-light btn-sm" @click="addCategory">Add</button>
                </div>
                <!-- <vue-slider
                    v-model="rubricNewValue"
                    :disabled="user_role === 'assistant-teacher'"
                    :lazy="true"
                    :interval="5"
                    :tooltip="'always'"
                    @change="addCategory">
                </vue-slider> -->
            </div>
        </div>
        <div
            v-if="type !== 'worksheets'"
            class="pt-1">
            <button
                v-if="user_role === 'teacher'"
                class="btn btn-light btn-sm btn-add"
                @click="resetAddCategory">
                Add a category
            </button>
        </div>
        <!-- <div><pre class="small">{{ categoryPercentage }}</pre></div>
        <div><pre class="small">{{ compcats }}</pre></div> -->
    </div>
</template>


<script>
import apiRequest from '../functions/apiRequest';
import notify from '../mixins/notify';
import dueDatesMixin from '../mixins/dueDatesMixin';
import { getPercentage } from '../functions/utils';

export default {
    name: 'SettingsRubric',

    components: {
        VueSlider: () => import('vue-slider-component')
    },

    mixins: [notify, dueDatesMixin],

    props: {
        categories: {
            type: Array,
            required: true
        },
        type: {
            type: String,
            default: ''
        }
    },

    data() {
        return {
            add: false,
            rubricNewValue: 0,
            rubricNewName: '',
            rubricError: '',
            rubricTotalDisplay: 0,
            percentageFormatter: '{value}%',
            rubricType: this.type !== 'worksheets' ? 1 : 2,
            cats: this.categories,
            deactivateNotified: false
        };
    },

    computed: {
        compcats() { // Place inactive at the bottom
            return this.cats.slice().sort((a, b) => b.active - a.active)
        },
        categoryPercentage() {
            return this.compcats.reduce((result, { id, category_value }) => {
                result[id] = getPercentage(category_value, this.rubricTotal)
                return result;
            }, {})
        },

        firstInactive() { // Get first active for rubric total placement
            let result = this.compcats.findIndex(obj => [false, 0].includes(obj.active))
            result = result === -1 ? this.compcats.length : result;
            return result;
        },

        rubricTotal() { // Tally active category values
            return this.compcats
                .filter(obj => [true, 1].includes(obj.active))
                .reduce((total, cat) => total + cat.category_value, 0);
        }
    },

    //
    // Assign initial and updated rubricTotal to displayed rubric total
    // --------------------------------------------------------------------------
    created() {
        const vm = this;
        vm.rubricTotalDisplay = vm.rubricTotal;
    },

    methods: {
        //
        // Reset Add Category Form
        // --------------------------------------------------------------------------
        resetAddCategory() {
            this.add = !this.add;
        },

        //
        // Update displayed total and percentages when dragging slider by adding
        // difference between slider's value and current category value to total
        // --------------------------------------------------------------------------
        updateNumbers(value, catVal, id = 0) {
            const vm = this;
            const difference = value - catVal;
            vm.rubricTotalDisplay = vm.rubricTotal + difference;

            // Update category's percentage
            if(id) {
                const percentage = val => getPercentage(val, vm.rubricTotalDisplay);
                Object.keys(vm.categoryPercentage).forEach(pid => {
                    if(+pid !== id) {
                        const catValue = vm.compcats.filter(obj => obj.id === +pid)[0].category_value;
                        vm.categoryPercentage[pid] = percentage(catValue);
                    } else {
                        vm.categoryPercentage[id] = percentage(value);
                    }
                })
            }
        },

        //
        // Activate Category
        // --------------------------------------------------------------------------
        activateCategory(catid, event) {
            const value = Number(event.target.value);
            const message = !value && 'Note: Custom Assignments that use deactivated categories will need to be assigned a new category.'
            const request = {
                category_id: catid,
                category_active: value,
                category_type: this.rubricType
            };
            this.postData('/activate/rubric', request);
            if(message && !this.deactivateNotified) {
                this.deactivateNotified = true;
                this.notify(message, 'highlight');
            }

        },

        //
        // Update Category / Worksheets
        // --------------------------------------------------------------------------
        updateCategory() {
            const vm = this;

            // Show message for worksheets and do nothing;
            if(vm.type === 'worksheets') {
                this.notify('Saved.', 'success');
                return;
            }

            if(vm.rubricTotal !== 100) {
                vm.notify('Percentages must total 100%.', 'danger');
            } else {
                vm.compcats.forEach(c => {
                    const request = {
                        category_id: c.id,
                        category_value: vm.categoryPercentage[c.id],
                        category_type: vm.rubricType
                    };
                    vm.postData('/update/rubric', request);
                });
                vm.notify('Saved.', 'success');
            }
        },

        updateWorksheet(event, catid) {
            const request = {
                category_id: catid,
                category_value: event,
                category_type: this.rubricType
            };
            this.postData('/update/rubric', request);
        },

        //
        // Delete Category
        // --------------------------------------------------------------------------
        deleteCategory(catid) {
            this.cats.splice(this.cats.findIndex(obj => obj.id === catid), 1);
            this.postData('/delete/rubric', { category_id: catid });
        },

        //
        // Add Category
        // --------------------------------------------------------------------------
        addCategory() {
            const vm = this,
                active = vm.rubricNewValue ? 1 : 0,
                request = {
                    category_name: vm.rubricNewName,
                    category_value: vm.rubricNewValue,
                    category_active: active
                };
            if (vm.rubricNewName !== '') {
                vm.postData('/add/rubric', request);
            }
        },

        //
        // Build New Category Object
        // --------------------------------------------------------------------------
        buildCategory(id, data) {
            let cat = {};
            cat = {
                id: id,
                category_name: data.category_name,
                category_value: data.category_value,
                active: data.category_active
            };
            this.resetAddCategory();
            return cat;
        },

        //
        // Send Data / Update
        // --------------------------------------------------------------------------
        postData(postURL, request) {
            const vm = this;
            vm.rubricTotalDisplay = vm.rubricTotal;
            apiRequest(postURL, request).then(response => {
                if (postURL.indexOf('add') !== -1) {
                    vm.cats.push(vm.buildCategory(response.success, request));
                    vm.rubricNewValue = 0;
                    vm.rubricNewName = '';
                }
            })
        }

    }
};
</script>

<style lang="scss" scoped>
.notification-wrapper {
    min-height: 1.5rem;
}
.form-wrapper-file {
    border-color: $light-gray;
}
.no-switch-percentage {
    margin-left: 38px;
}
</style>
