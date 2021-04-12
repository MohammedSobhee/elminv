<template>
    <div class="sort-menu mb-3">
        <ul class="small wrapper pr-1">
            Sort by:
            <li v-for="header in headers" :key="header.key">
                <span
                    v-if="!(class_id && header.key === 'class_id') && header.key !== 'password' && header.key !== 'code'"
                    :class="{ active: sortActiveLink === header.key}"
                    class="link"
                    @click.print="sortData(header.key); sortSwitch = !sortSwitch">
                    {{ header.label }}
                </span>
            </li>
        </ul>
    </div>
</template>
<script>
export default {
    name: 'AddAccountsSortMenu',
    props: {
        headers: {
            type: Array,
            required: true
        },
        class_id: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            sortSwitch: true,
            sortActiveLink: ''
        }
    },
    methods: {
        sortData(key) {
            this.sortActiveLink = key;
            this.$emit('sort', {
                key: key,
                switch: key === 'date' ? !this.sortSwitch : this.sortSwitch // reverse for date as it's already sorted by desc
            });
        }
    }
}
</script>
