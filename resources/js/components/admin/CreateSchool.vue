<template>
    <div>
        <a href="#" onclick="location.href = document.referrer; return false;" class="btn btn-sm btn-primary pl-1"><i class="mr-2 fas fa-angle-left"></i> Back</a>
        <br><br>
        <form
            id="form-createschool"
            class="form-school"
            method="post"
            action="/eduadmin/create/school"
            @submit="submitForm">
            <input type="hidden" name="_token" :value="csrf">
            <h4 class="form-heading">Name and District</h4>
            <div class="form-row">
                <div class="form-group col-md-1 mt-0">
                    <label for="esa">ESA</label>
                    <input
                        id="esa"
                        type="text"
                        name="esa"
                        class="form-control"
                        placeholder="ESA" />
                </div>
                <div class="form-group col-md-6 mt-0">
                    <label class="label-status" data-status="school_name" for="school_name">School Name</label>
                    <input
                        ref="school_name"
                        v-model="schoolName"
                        type="text"
                        name="school_name"
                        class="form-control"
                        placeholder="School Name" />
                </div>
                <div class="form-group col-md-5 mt-0">
                    <label for="school_district">School District</label>
                    <input
                        id="school_district"
                        type="text"
                        name="school_district"
                        class="form-control"
                        placeholder="School District" />
                </div>
            </div>

            <h4 class="form-heading">Contact Information </h4>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="label-status" data-status="first_name" for="first_name">First Name</label>
                    <input
                        ref="school_name"
                        v-model="firstName"
                        type="text"
                        name="first_name"
                        class="form-control"
                        placeholder="First Name" />
                </div>
                <div class="form-group col-md-4">
                    <label class="label-status" data-status="last_name" for="last_name">Last Name</label>
                    <input
                        ref="last_name"
                        v-model="lastName"
                        type="text"
                        name="last_name"
                        class="form-control"
                        placeholder="Last Name" />
                </div>
                <div class="form-group col-md-4">
                    <label for="title">Title</label>
                    <input
                        id="title"
                        type="text"
                        name="title"
                        class="form-control"
                        placeholder="Title" />
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="label-status" data-status="email" for="email">Email</label>
                    <input
                        ref="email"
                        v-model="email"
                        type="text"
                        name="email"
                        class="form-control"
                        placeholder="Email" />
                </div>
                <div class="form-group col-md-4">
                    <label class="label-status" data-status="phone" for="phone">Phone</label>
                    <input
                        ref="phone"
                        v-model="phone"
                        type="text"
                        name="phone"
                        class="form-control"
                        placeholder="Phone" />
                </div>
                <div class="form-group col-md-2">
                    <label for="ext">Ext</label>
                    <input
                        id="ext"
                        type="text"
                        name="ext"
                        class="form-control"
                        placeholder="Ext" />
                </div>
            </div>
            <h4 class="form-heading">Settings</h4>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label class="label-status" data-status="school_type" for="school_type">School Class Types: </label>
                    <multiselect
                        ref="school_type"
                        v-model="types"
                        :options="class_types"
                        track-by="id"
                        label="name"
                        name="school_type"
                        :multiple="true"
                        :show-labels="false"
                        :close-on-select="false"
                        :taggable="true"
                        :allow_empty="false"
                        :searchable="false">
                    </multiselect>
                    <input v-model="schoolTypes" type="hidden" name="school_type">
                </div>
                <div class="form-group col-md-2">
                    <label
                        class="label-status"
                        data-status="standards"
                        for="standards">
                        Standards Alignment:
                    </label>
                    <multiselect
                        id="standards"
                        v-model="standard"
                        placeholder="Select Standard"
                        :options="standards"
                        name="standards"
                        :show-labels="false"
                        :allow_empty="false"
                        class="multiselect"
                        :searchable="true">
                    </multiselect>
                </div>
                <div class="form-group col-md-3">
                    <label for="contract_start_date">Start Date:</label>
                    <date-input
                        name="contract_start_date"
                        :emit="true"
                        focus_placeholder="Month Day, Year"
                        @changed="setStartDate">
                    </date-input>
                </div>
                <div class="form-group col-md-1">
                    <label for="term">Term: </label>
                    <input
                        id="term"
                        type="text"
                        name="term"
                        class="form-control"
                        placeholder="Yrs"
                        @input="setExpDate" />
                </div>
                <div class="form-group col-md-3">
                    <label for="contract_expiration_date">Expiration Date:</label>
                    <input v-model="expDate" class="form-control">
                </div>
            </div>
            <div class="form-row mt-3">
                <div class="form-group col-md-6">
                    <label for="admin_email">Recipient's Email to send the activation notification if different:</label>
                    <input
                        ref="admin_email"
                        v-model="admin_email"
                        type="text"
                        name="admin_email"
                        class="form-control"
                        placeholder="Activation Recipient Email" />
                </div>
                <div class="form-group col-md-6">
                    <label for="admin_name">Recipient's Name:</label>
                    <input
                        ref="admin_name"
                        v-model="admin_name"
                        type="text"
                        name="admin_name"
                        class="form-control"
                        placeholder="Activation Recipient Name" />
                </div>
            </div>
            <input
                type="submit"
                name="submit"
                value="Create School"
                class="btn btn-light mt-4">
        </form>
    </div>
</template>

<script>
import { formatDate } from '../../functions/utils';

export default {
    name: 'CreateSchool',
    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect')
    },
    props: {
        class_types: {
            type: Array,
            required: true
        },
        standards: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            old: {},
            startDate: '',
            expDate: '',
            schoolName: '',
            firstName: '',
            lastName: '',
            email: '',
            phone: '',
            standard: '',
            types: []
        }
    },
    computed: {
        schoolTypes() {
            return Array.isArray(this.types) ? this.types.map(obj => obj.id).toString() : this.types.id;
        }
    },
    methods: {
        setStartDate(event) {
            this.startDate = event.target.value
        },

        setExpDate(event) {
            const d = new Date(this.startDate);
            const newDate = new Date(d.setFullYear(d.getFullYear() + +event.target.value));
            this.expDate = formatDate(newDate, 'long');
        },

        submitForm(event) {
            const vm = this;
            if(vm.schoolName && vm.firstName && vm.lastName && vm.email && vm.phone && vm.schoolTypes) {
                return true;
            }

            Object.keys(vm.$refs).forEach(r => {
                const el = document.querySelector(`.label-status[data-status="${r}"]`);
                el.insertAdjacentHTML('afterend', '<em class="ml-2 error">Required</em>');
            });

            event.preventDefault();

        }
    }
}
</script>
