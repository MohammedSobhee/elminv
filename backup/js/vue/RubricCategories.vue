<template>
    <div>
        <!-- <hr class="mb-3 mt-lg-5"> -->
        <div
            class="form-wrapper p-4">
            <div
                v-for="(cat, index) in compcats"
                :key="cat.id"
                class="pl-1 pb-3 position-relative"
                :class="[
                    !cat.active && 'small',
                    (type === 'worksheets' && cat.active) && 'text-right'
                ]">
                <input
                    v-if="cat.category_name !== 'Classwork'"
                    v-model="cat.active"
                    type="checkbox"
                    :value="cat.active ? 0 : 1"
                    class="form-check-switch"
                    @change="activateCategory(cat.id, $event)">
                 <i
                    v-if="type !== 'worksheets' && cat.category_name !== 'Classwork'"
                    :class="!cat.active && 'position-relative ml-2'"
                    class="far fa-trash-alt slider-trash"
                    @click="deleteCategory(cat.id)">
                </i>
                <span
                    :class="[
                        { 'ml-1': !cat.active },
                        { 'no-switch-percentage': cat.category_name === 'Classwork'}
                    ]"
                    class="text-success">{{ categoryPercentage[cat.id] }}%</span>
                <strong
                    :class="[
                        !cat.active && 'text-muted font-weight-normal',
                        !cat.active ? 'ml-1' : 'ml-3']">
                        {{ cat.category_name }}
                </strong>
                <vue-slider
                    v-if="cat.active"
                    :id="cat.id"
                    v-model="cat.category_value"
                    :value="cat.category_value"
                    :lazy="true"
                    :tooltip="'always'"
                    @dragging="val => updateNumbers(val, cat.category_value, cat.id)"
                    @change="updateCategory(cat.id, $event)">
                </vue-slider>
                <div v-if="index === firstInactive - 1" class="text-right mt-2">
                    <strong>Total:
                        <span class="text-success">
                            {{ rubricTotalDisplay }}
                        </span>
                    </strong>
                </div>
            </div>
            <div v-if="add" class="pl-1">
                <div class="pl-3 form-inline">
                    <input
                    v-model="rubricNewName"
                    type="text"
                    placeholder="Label category"
                    class="form-control form-control-sm"
                    @keyup.tab="addCategory"
                    @keyup.enter="addCategory">
                    <small id="emailHelp" class="form-text text-muted ml-2">
                        Label and assign it a value.
                    </small>
                </div>
                <vue-slider
                    v-model="rubricNewValue"
                    :lazy="true"
                    :tooltip="'always'"
                    @change="addCategory">
                </vue-slider>
            </div>
        </div>
        <div
            v-if="type !== 'worksheets'"
            class="pt-1">
            <button
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
import { getPercentage } from '../functions/utils';

export default {
    name: 'RubricCategories',

    components: {
        VueSlider: () => import('vue-slider-component')
    },

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
            rubricType: this.type !== 'worksheets' ? 1 : 2,
            cats: this.categories
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
        this.rubricTotalDisplay = this.rubricTotal;
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
        updateNumbers(value, catVal, id) {
            const vm = this;
            const difference = value - catVal;
            vm.rubricTotalDisplay = vm.rubricTotal + difference;
            const percentage = val => getPercentage(val, vm.rubricTotalDisplay);

            // Update category's percentage
            Object.keys(vm.categoryPercentage).forEach(pid => {
                if(+pid !== id) {
                    const catValue = vm.compcats.filter(obj => obj.id === +pid)[0].category_value;
                    vm.categoryPercentage[pid] = percentage(catValue);
                } else {
                    vm.categoryPercentage[id] = percentage(value);
                }
            })
        },

        //
        // Activate Category
        // --------------------------------------------------------------------------
        activateCategory(catid, event) {
            const request = {
                category_id: catid,
                category_active: Number(event.target.value),
                category_type: this.rubricType
            };
            this.postData('/api/activate/rubric', request);
        },

        //
        // Update Category
        // --------------------------------------------------------------------------
        updateCategory(catid, event) {
            const request = {
                category_id: catid,
                category_value: event,
                category_type: this.rubricType
            };
            this.postData('/api/update/rubric', request);
        },

        //
        // Delete Category
        // --------------------------------------------------------------------------
        deleteCategory(catid) {
            const vm = this;
            vm.cats.splice(
                vm.cats.findIndex(obj => obj.id === catid),
                1
            );
            vm.postData('/api/delete/rubric', { category_id: catid });
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
                    category_active: active,
                    user_id: vm.user_id
                };
            if (vm.rubricNewName !== '') {
                vm.postData('/api/add/rubric', request);
                vm.rubricNewValue = 0;
                vm.rubricNewName = '';
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
                }
            })
        }
    }
};
</script>

<style lang="scss" scoped>
.form-wrapper-file {
    border-color: $light-gray;
}
.no-switch-percentage {
    margin-left: 38px;
}
</style>
