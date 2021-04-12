<template>
    <div>
        <rubric-categories v-bind="$props"></rubric-categories>
        <!-- <rubric-worksheet></rubric-worksheet> -->
    </div>
</template>

<script>
export default {
    name: 'RubricSetup',
    props: {
        categories: {
            type: Array,
            required: true
        },
        user_id: {
            type: Number,
            required: true
        }
    }
}
</script>
