<template>
    <div>
        <input
            v-if="name && !multiple"
            type="hidden"
            :name="name"
            :value="selection ? selection[id_by] : ''">
        <input
            v-else-if="name"
            type="hidden"
            :name="name"
            :value="multiSelection">
        <multiselect
            v-model.lazy="selection"
            :options="options"
            :multiple="multiple"
            :taggable="false"
            :searchable="searchable"
            :class="css_class"
            :close-on-select="close_select"
            :show-labels="false"
            :label="label_by"
            :track-by="id_by"
            :placeholder="placeholder"
            :preselect-first="preselect_first"
            @select="onSelect">
            <template
                v-for="slotName in ['option', 'singleLabel']"
                :slot="slotName"
                slot-scope="props">
                {{ props.option[label_by] }}
                <!-- eslint-disable vue/require-v-for-key -->
                <span
                    v-if="added_options_data"
                    class="small text-muted">
                    ({{ props.option[added_options_data] }})
                </span>
                <!-- eslint-enable vue/require-v-for-key -->
            </template>
            <template v-if="multiple" slot="selection" slot-scope="{ values }">
                <span v-if="values.length" class="multiselect__single">Selected {{ values.length }} Types</span>
            </template>
        </multiselect>
    </div>
</template>

<script>
export default {
    name: 'VueSelect',

    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },

    props: {
        input: {
            type: String,
            default: ''
        },
        name: {
            type: String,
            default: ''
        },
        select_url: {
            type: String,
            default: ''
        },
        selected: {
            type: [Number, String],
            default: 0
        },
        multiple: {
            type: Boolean,
            default: false
        },
        close_select: {
            type: Boolean,
            default: true
        },
        id_by: {
            type: String,
            required: true
        },
        label_by: {
            type: String,
            required: true
        },
        css_class: {
            type: String,
            default: ''
        },
        options: {
            type: Array,
            required: true
        },
        preselect_first: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: ''
        },
        added_options_data: {
            type: String,
            default: ''
        },
        colors: {
            type: Boolean,
            default: false
        },
        searchable: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            selection: ''
        };
    },

    computed: {
        multiSelection() {
            return Array.isArray(this.selection) ? this.selection.map(obj => obj.id).toString() : this.selection.id;
        }
    },
    created() {
        if (this.selected) {
            // Get selected object by passed selected ID
            this.selection = this.options.find(obj => obj.id === this.selected) || '';
        }
    },

    methods: {
        onSelect(value) {
            const vm = this;
            if (vm.select_url.length) {
                const url = vm.select_url + value[vm.id_by];
                if (window.self !== window.top) window.top.location.href = url;
                else window.location.href = url;
            }
        }
    }
};
</script>
