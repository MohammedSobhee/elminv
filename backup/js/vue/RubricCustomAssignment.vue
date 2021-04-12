<template>
    <div>
        <!-- <button
            type="button"
            class="btn btn-light btn-sm"
            :class="{ focus: rubricSetup }"
            @click="rubricSetup = !rubricSetup">
            Setup activity rubric
            <i class="fas" :class="[rubricSetup ? 'fa-caret-up' : 'fa-caret-down']"></i>
        </button> -->
        <p class="mt-3">
            Specify your preferred total rubric value and assign a weighted rubric value to each assignment.
        </p>
        <div class="form-wrapper p-4">
            <div>
                <h5 class="mb-4">
                    Classwork <span>35 Points</span>
                </h5>
                <div class="text-right">
                    <div class="pl-1 pb-3">
                        <strong>Get to know our 3D Printer</strong>
                        <vue-slider v-model="rubricValue1" :tooltip="'always'"></vue-slider>
                    </div>
                    <div class="pl-1 pb-3">
                        <strong>Homework Assignment 1</strong>
                        <vue-slider v-model="rubricValue2" :tooltip="'always'"></vue-slider>
                    </div>
                    <div class="pl-1 pb-3">
                        <strong>Critical Thinking 2</strong>
                        <vue-slider v-model="rubricValue3" :tooltip="'always'"></vue-slider>
                    </div>
                </div>
                <div>
                    <strong class="font-weight-bold">Total:
                        <span :class="[rubricTotal1 <= 35 ? 'text-success' : 'text-danger']">
                            {{ rubricTotal1 }}
                        </span>
                    </strong>
                </div>

                <hr>
                <h5 class="mt-5 mb-4">
                    Homework <span>15 Points</span>
                </h5>
                <div class="text-right">
                    <div class="pl-1 pb-3">
                        <strong>Homework Assignment 2</strong>
                        <vue-slider v-model="rubricValue4" :tooltip="'always'"></vue-slider>
                    </div>
                    <div class="pl-1 pb-3">
                        <strong>Homework Assignment 3</strong>
                        <vue-slider v-model="rubricValue5" :tooltip="'always'"></vue-slider>
                    </div>
                </div>
                <div>
                    <strong class="font-weight-bold">Total:
                        <span :class="[rubricTotal2 <= 15 ? 'text-success' : 'text-danger']">
                            {{ rubricTotal2 }}
                        </span>
                    </strong>
                </div>

                <hr>
                <h5 class="mt-5 mb-4">
                    Quizes <span>30 Points</span>
                </h5>
                <div class="text-right">
                    <div class="pl-1 pb-3">
                        <strong>9 Step Quiz</strong>
                        <vue-slider v-model="rubricValue6" :tooltip="'always'"></vue-slider>
                    </div>
                </div>
                <div>
                    <strong class="font-weight-bold">Total:
                        <span :class="[rubricTotal3 <= 30 ? 'text-success' : 'text-danger']">
                            {{ rubricTotal3 }}
                        </span>
                    </strong>
                </div>


                <hr>
                <h5 class="mt-5 mb-4">
                    Custom Category Name <span>10 Points</span>
                </h5>
                <div class="text-right">
                    <div class="pl-1 pb-3">
                        <strong>Custom assignment name</strong>
                        <vue-slider v-model="rubricValue7" :tooltip="'always'"></vue-slider>
                    </div>
                </div>
                <div>
                    <strong class="font-weight-bold">Total:
                        <span :class="[rubricTotal4 <= 10 ? 'text-success' : 'text-danger']">
                            {{ rubricTotal4 }}
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
    name: 'RubricCustomAssignment',
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
            rubricValue4: 5,
            rubricValue5: 7,
            rubricValue6: 20,
            rubricValue7: 10
        };
    },

    computed: {
        rubricTotal1() {
            return this.rubricValue1 +
                this.rubricValue2 +
                this.rubricValue3;
        },

        rubricTotal2() {
            return this.rubricValue4 +
                this.rubricValue5;
        },

        rubricTotal3() {
            return this.rubricValue6;
        },

        rubricTotal4() {
            return this.rubricValue7;
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
