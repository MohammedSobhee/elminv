<template>
    <div class="mb-2 form-worksheet form-worksheet-dim rounded pt-2 pb-2">
        <div class="flex-row row align-items-center">
            <div class="col-md-2">
                <form class="form-inline">
                    <label for="rubric" class="large mr-2"><h4 class="font-weight-normal m-0">Grade:</h4></label>
                    <select
                        id="rubric"
                        v-model="grade"
                        class="custom-select"
                        :class="{ completed: grade }">
                        <option
                            v-for="(option, index) in rubricdata"
                            :key="index"
                            :value="option">
                            {{ option }}
                        </option>
                    </select>
                </form>
            </div>
            <div class="col-md-10 text-muted">
                <span v-if="team">
                    <strong>Team:</strong> {{ project_name }}
                </span>
                <span else>
                    <strong>Student:</strong> {{ student_fullname }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        team: {
            type: Number,
            default: 0
        },
        project_name: {
            type: String,
            default: ''
        },
        student_fullname: {
            type: String,
            default: ''
        },
        wid: {
            type: Number,
            default: 0
        },
        pid: {
            type: Number,
            default: 0
        },
        rubric: {
            type: Object,
            required: true
        }

    },
    data() {
        return {
            //rubricdata: this.rubric
            rubricdata: {
                0: 'A',
                1: 'B',
                2: 'C',
                3: 'D',
                4: 'F'
            },
            grade: 'C'
        }
    },
    created() {

    }
};
</script>

<style lang="scss" scoped>
</style>
