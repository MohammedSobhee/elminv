<template>
    <div>
        <!-- <button
            type="button"
            class="btn btn-light btn-sm"
            :class="{ focus: rubricSetup }"
            @click="rubricSetup = !rubricSetup">
            Setup participation rubric
            <i class="fas" :class="[rubricSetup ? 'fa-caret-up' : 'fa-caret-down']"></i>
        </button> -->
        <p class="mt-3">
            Specify your preferred total rubric value and by how you'd like to judge participation.
        </p>
        <div
            class="form-wrapper-file p-4">
            <div class="form-group form-inline">
                <label><strong>Total Possible Points:</strong></label>
                <input
                    v-model="totalPossible"
                    type="text"
                    class="ml-2 form-control form-control-sm"
                    size="10">
            </div>
            <hr class="mt-2 mb-4" />
            <div class="form-group">
                <label>Judge participation by:</label>
                <select class="custom-select custom-select-sm">
                    <option>by Class</option>
                    <option>by Student</option>
                    <option>by Team</option>
                </select>
            </div>
        </div>
    </div>
</template>


<script>


export default {
    name: 'RubricParticipation',
    data() {
        return {
            rubricSetup: 0,
            totalPossible: 200,
            rubricValue1: 5,
            rubricValue2: 15,
            rubricValue3: 30,
            rubricValue4: 20,
            rubricValue5: 20,
            rubricValue6: 20
        };
    },

    computed: {
        rubricTotal() {
            return this.rubricValue1 +
                this.rubricValue2 +
                this.rubricValue3 +
                this.rubricValue4 +
                this.rubricValue5 +
                this.rubricValue6;
        }

    },

    created() {

    },

    methods: {

    }




}
</script>

<style lang="scss" scoped>
.form-wrapper-file {
    border-color: $light-gray;
}
strong {
    font-weight: normal;
}

.pl-1 {
    font-size: smaller;
}

</style>
