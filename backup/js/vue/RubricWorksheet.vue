<template>
    <div>
        <hr class="mb-3 mt-lg-5">
        <h4 class="mb-3">
            Activity Sheet Rubric
        </h4>
        <p class="mt-3">
            Specify your preferred total rubric value and assign a weighted rubric value to each activity sheet according to the amount of work involved by the students. Default rubric values are recommended.
        </p>
        <div
            class="form-wrapper p-4">
            <div class="text-right">
                <div class="pl-1 pb-3">
                    <strong>Step 1: Confidentiality Agreement and Idea Recorder</strong>
                    <vue-slider v-model="rubricValue1" :tooltip="'always'"></vue-slider>
                </div>
                <div class="pl-1 pb-3">
                    <strong>Step 2: Pre-Development Store Search</strong>
                    <vue-slider v-model="rubricValue2" :tooltip="'always'"></vue-slider>
                </div>
                <div class="pl-1 pb-3">
                    <strong>Step 2: Pre-Development Internet Search</strong>
                    <vue-slider v-model="rubricValue3" :tooltip="'always'"></vue-slider>
                </div>
                <div class="pl-1 pb-3">
                    <strong>Step 2: Pre-Development Patent Search</strong>
                    <vue-slider v-model="rubricValue4" :tooltip="'always'"></vue-slider>
                </div>
                <div class="pl-1 pb-3">
                    <strong>Step 2: Pre-Development Targeting a Corporation</strong>
                    <vue-slider v-model="rubricValue5" :tooltip="'always'"></vue-slider>
                </div>
                <div class="pl-1 pb-3">
                    <strong>Step 3: Brainstorming</strong>
                    <vue-slider v-model="rubricValue6" :tooltip="'always'"></vue-slider>
                </div>
                <div class="text-right">
                    <strong class="font-weight-bold">Total:
                        <span class="text-success">
                            {{ rubricTotal }}
                        </span>
                    </strong>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
import VueSlider from 'vue-slider-component'

export default {
    name: 'RubricWorksheet',
    components: {
        VueSlider
    },

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
