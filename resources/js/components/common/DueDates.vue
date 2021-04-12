<template>
    <div>
    <div v-for="cls in asgmt.classes" :key="cls.id" class="list-group-flush">
        <div class="list-group-item list-group-item-duedate">
            <div class="d-flex align-items-center justify-content-around">
                <div
                    class="col-4 text-left"
                    :class="{'text-muted': !cls.due_date}"
                    style="cursor:auto">
                    {{ cls.name }}
                </div>
                <div class="col form-inline">
                    <div class="d-inline-block date-input date-input-sm">
                    <input
                        :id="'date'+type+asgmt.id+cls.id"
                        v-model="cls.due_date"
                        v-flatpickr="dateOptions"
                        class="form-control form-control-sm form-flatpickr"
                        placeholder="Select Due Date"
                        @change="setDate(asgmt.id, cls.id, $event)" />
                        <i v-show="!cls.due_date" :id="'open-date'+type+asgmt.id+cls.id" class="far fa-calendar-alt input-icon"></i>
                        <i v-show="cls.due_date" :id="'clear-date'+type+asgmt.id+cls.id" class="far fa-trash-alt input-icon delete-action"></i>
                        <i v-if="cls.requestStatus" class="ml-3 small fas fa-check text-success" aria-hidden="true"></i>
                    </div>
                    <a
                        v-if="selectedDueDate.id === cls.id && asgmt.classes.length > 1"
                        href="#"
                        class="animated faster bounceInLeft mt-1 ml-3"
                        @click.prevent="setDateAllClasses(asgmt.id)">
                        Set this for all classes?
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import flatpickr from 'flatpickr';
import apiRequest from '../../functions/apiRequest';
import { clearObject, formatDateTime, shortenString } from '../../functions/utils';
export default {
    name: 'DueDates',
    directives: {
        flatpickr: {
            inserted: (el, binding) => {
                el._flatpickr = new flatpickr(el, binding.value);
                document.getElementById('open-'+el.id).addEventListener('click', () => el._flatpickr.open());
                document.getElementById('clear-'+el.id).addEventListener('click', () => el._flatpickr.clear());
            },
            unbind: el => el._flatpickr.destroy()
        }
    },
    props: {
        assignment: {
            type: Object,
            required: true
        },
        type: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            asgmt: this.assignment,
            selectedDueDate: {},
            dateOptions: {
                dateFormat: 'F j, Y'
            }
        }
    },
    created() {
        this.asgmt.classes.forEach(c => {

            (c.due_date && c.due_date.indexOf('00:00:00') > -1)
                && (c.due_date = formatDateTime(c.due_date, 'long'));
            Vue.set(c, 'requestStatus', 0)
        });
        // shorten URLs for display
        this.asgmt.type === 2 && (this.asgmt.file_name = shortenString(this.asgmt.file_name));
        // Check if all dates are set for an assignment's classes
        Vue.set(this.asgmt, 'allDatesSet', this.checkAllDatesSet(this.asgmt.classes));
    },
    methods: {
        //
        // Count dates set for classes
        // --------------------------------------------------------------------------
        checkAllDatesSet(arr) {
            const count = arr.reduce((total, obj) => total + (!obj.due_date ? 0 : 1), 0);
            const length = arr.length;
            return count === length;
        },
        //
        // Set Date
        // --------------------------------------------------------------------------
        setDate(asgmtID, classID, event) {
            const request = {
                class_id: classID,
                assignment_id: asgmtID,
                type: this.type,
                due_date: event.target.value
            }

            this.selectedDueDate.value = event.target.value;
            this.selectedDueDate.id = classID;

            apiRequest('/update/duedate', request).then(() => {
                this.asgmt.classes.find(obj => obj.id === classID).requestStatus = 1;
                this.asgmt.allDatesSet = this.checkAllDatesSet(this.asgmt.classes);
                //this.asgmt.allDatesSet && this.$emit('update', { asgmt: this.asgmt, type: this.type });
            });


        },

        setDateAllClasses(asgmtID) {
            const event = {
                target: {
                    value: this.selectedDueDate.value
                }
            };
            this.asgmt.classes.filter(c => c.id !== this.selectedDueDate.id).forEach(cls => {
                cls.due_date = this.selectedDueDate.value;
                this.setDate(asgmtID, cls.id, event)
            });

            clearObject(this.selectedDueDate);
        }
    }
}
</script>
<style lang="scss" scoped>
.fa-check {
    position: absolute;
    top: .75rem;
    left: -2.5rem;
}
</style>
