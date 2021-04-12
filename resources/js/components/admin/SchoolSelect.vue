<template>
    <div>
        <input
            type="hidden"
            :name="name"
            :value="selection ? selection.id : ''">
        <multiselect
            ref="multiselect"
            v-model="selection"
            :options="schoolList"
            :show-labels="false"
            label="name"
            track-by="id"
            placeholder="Search Schools"
            no-result="No schools found."
            :allow-empty="false"
            :internal-search="false"
            :close-on-select="false"
            :block-keys="blockKeys.cmd ? blockKeys.keys : []"
            :loading="loading"
            @open="open"
            @close="close"
            @search-change="debouncedSearch"
            @select="select">
            <template
                v-for="slotName in ['option', 'singleLabel']"
                :slot="slotName"
                slot-scope="props">
                {{ props.option.name }}
                <!-- eslint-disable vue/require-v-for-key -->
                <span class="text-muted">
                    <span v-if="props.option.settings.payment_due == 1" class="ml-1">
                        <i class="fas fa-exclamation-triangle mr-1 small"></i> Payment Due
                    </span>
                </span>
                <span v-if="!props.option.status" class="text-muted">
                    <i class="far fa-times-circle"></i> Deactivated
                </span>
                <!-- eslint-enable vue/require-v-for-key -->
            </template>
            <span slot="noResult">No schools found.</span>
        </multiselect>
    </div>
</template>

<script>
import apiRequest from '../../functions/apiRequest';
import { deepCopy, waitForThis } from '../../functions/utils';

export default {
    name: 'SchoolSelect',

    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },

    props: {
        name: {
            type: String,
            default: ''
        },
        selected: {
            type: [Number, String],
            default: 0
        },
        options: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            selection: '',
            requestURL: '/eduadmin/edit/school/',
            originalSchoolList: deepCopy(this.options),
            schoolList: this.options,
            loading: false,
            blockKeys: {
                cmd: false,
                keys: ['Tab', 'Enter']
            }
        };
    },

    watch: {
        loading(val) {
            this.blockKeys.cmd = val;
        }
    },

    created() {
        // Get selected object by passed selected ID
        if (this.selected) {
            this.selection = this.options.find(obj => obj.id === this.selected) || '';
        }

        // Slow down search query to Algolia
        this.debouncedSearch = _.debounce(this.search.bind(this.selection), 500);
    },

    methods: {
        open() {
            waitForThis(() => this.$refs.multiselect.search.length, () => this.loading = true);
        },

        close() {
            this.loading = false;
        },

        select(value) {
            const vm = this;
            const url = vm.requestURL + value.id;
            if (window.self !== window.top) window.top.location.href = url;
            else window.location.href = url;
            vm.$refs.multiselect.deactivate();
        },

        search(query) {
            const vm = this;
            if(!query) {
                vm.schoolList = vm.originalSchoolList;
                vm.loading = false;
                return;
            }
            apiRequest('/eduadmin/search/schools', { search: query }).then(response => {
                vm.schoolList = response;
                vm.loading = false;
            });
        }
    }
};
</script>
