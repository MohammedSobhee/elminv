<template>
    <div id="form-worksheet" class="form-worksheet rounded" v-if="loadingComplete">
        <form :id="'worksheet' + this.wid" :class="'worksheet' + this.wid" ref="form" @submit.prevent="onSubmit">
        <input type="hidden" name="worksheet_id" :value="wid">

        <div  class="row flex-row">

            <div class="form-group-wrapper"
                :class="[field.colSize, field.wrapperID, field.groupID]"
                v-for="(field, index) in worksheetFields"
                :key="field.form_field_id">

                <div class="col-md-12 p-0 mb-5" v-if="!field.sameGroup && !field.heading && !field.description">
                    <div class="border-top py-2 text-right">
                        <button
                            class="btn btn-light btn-sm btn-add"
                            @click="addGroup(index,field.group_id, field.form_field_id, field.display_size)">Add row
                        </button>
                    </div>
                </div>

                <!-- <div>{{ field.sameGroup }} - {{ field.group_id }}</div> -->

                <div v-if="field.heading || field.description" class="form-worksheet-text">
                    <h4 v-if="field.heading" v-html="field.heading">{{ field.heading }}</h4>
                    <p v-if="field.description" v-html="field.description">{{ field.description }}</p>
                </div>

                <!-- Check if repeater at this point and do a v-for (array of the added group/fields?) with answers -->


                <div class="form-group" v-if="!field.heading && !field.description">

                    <!-- Label for text, textarea, select -->
                    <label
                        v-html="field.question"
                        v-if="field.type !== 0 && field.type !== 2 && field.type !== 3"
                        :for="field.name">{{ field.question }}
                    </label>

                    <!-- Text Date -->
                    <input
                        v-if="field.type === 1 && field.question == 'Date'"
                        class="form-control"
                        :class="{ completed: field.answer }"
                        :id="field.name"
                        @change="storeAnswer(field.form_field_id, $event)"
                        v-model="startDate"
                        v-flatpickr="startDateOptions">

                    <!-- Text -->
                    <input
                        v-else-if="field.type === 1"
                        type="text"
                        class="form-control"
                        :class="{completed: field.answer }"
                        :id="field.name"
                        @change="storeAnswer(field.form_field_id, $event)"
                        :value="field.answer">

                    <!-- Checkbox -->
                    <input
                        v-else-if="field.type === 2"
                        type="checkbox"
                        class="form-check-input"
                        :id="field.name"
                        :class="{completed: field.answer}"
                        @change="checkboxAnswer(field.form_field_id, $event, field.wrapperID)"
                        :checked="field.answer"
                        :value="field.question">
                    <label
                        v-html="field.question"
                        v-if="field.type !== 0 && field.type === 2"
                        :for="field.name"
                        class="form-check-label">{{ field.question }}
                    </label>

                    <!-- Radio -->
                    <div v-else-if="field.type === 3">
                        <div v-html="field.question">{{ field.question }}</div>
                        <ul>
                            <li v-for="(item, indexr) in field.value.items" :key="indexr">
                                <input
                                    type="radio"
                                    :id="'radio'+indexr"
                                    class="form-check-input"
                                    :class="{completed: field.answer == item}"
                                    @change="radioAnswer(field.form_field_id, $event, field.wrapperID)"
                                    :checked="field.answer == item"
                                    :value="item">
                                <label
                                    v-html="item"
                                    :for="'radio'+indexr"
                                    class="form-check-label">{{ item }}
                                </label>
                            </li>
                        </ul>
                    </div>

                    <!-- Select -->
                    <select
                        v-else-if="field.type == 4"
                        class="custom-select"
                        :id="field.name"
                        :class="{ completed: field.answer }"
                        @change="storeAnswer(field.form_field_id, $event)">
                        <option
                            v-for="(item, index) in field.value.items"
                            :key="index"
                            :value="item"
                            :selected="field.answer == item">{{ item }}
                        </option>
                    </select>

                    <!-- File -->
                    <div v-else-if="field.type === 5" class="form-worksheet-file p-2" :class="{completed: field.answer }">
                        <span class="btn btn-sm btn-secondary btn-file">
                            <span>Browse</span>
                            <input type="file" class="form-control-file" :id="field.name" @change="processImage(field.form_field_id, $event)" accept="image/*" >
                        </span>
                        <div class="image-preview" v-if="imageData[field.name].length">
                            <img class="preview" :src="imageData[field.name]">
                        </div>
                        <a v-if="field.answer && !imageData[field.name].length">
                            <vue-pure-lightbox
                                :thumbnail="'/uploads/' + field.answer"
                                :images="['/uploads/' + field.answer]"
                            />
                            <!-- <img :src="'/uploads/' + field.answer"> -->
                        </a>
                    </div>

                    <!-- Textarea -->
                    <textarea
                        v-else-if="field.type === 6"
                        rows="5" cols="40"
                        class="form-control"
                        :class="{completed: field.answer }"
                        :id="field.name"
                        @change='storeAnswer(field.form_field_id, $event)'
                        :value="field.answer">
                    </textarea>


                </div>
            </div>

        </div>

        <div class="mt-5">
            <div class="form-group-wrapper" :class="{'worksheet-completed': saveWorksheet}">
                <button type="button" class="btn btn-secondary mr-2" @click="saveWorksheet = true">Save Worksheet</button>
                <button type="submit" value="Send to Teacher" class="btn btn-primary">Send to Teacher</button>
            </div>
        </div>
        </form>
        <pre>{{ worksheetFields }}</pre>
    </div>
</template>

<script>
import flatpickr from "flatpickr";
import 'flatpickr/dist/themes/material_blue.css';

import VuePureLightbox from 'vue-pure-lightbox';
import styles from 'vue-pure-lightbox/dist/VuePureLightbox.css'

export default {
    name: 'Worksheet',
    props: ['wid', 'pid'],
    components: {
        VuePureLightbox
    },
    data() {
        return {
            loadingComplete: false,
            fields: '',
            fieldLabel: 'wfield',
            imageData: {},
            startDate: '',
            startDateOptions: {
                dateFormat: 'F j, Y',
                defaultDate: Date.now(),
            },
            saveWorksheet: false,
            isDataURLregex: /^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+\=[a-z\-]+)?)?(;base64)?,[a-z0-9\!\$\&\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i
        }
    },

    directives: {
        'flatpickr': {
            bind: (el, binding) => {
		        el._flatpickr = flatpickr(el, binding.value);
	        },
	        unbind: el => el._flatpickr.destroy()
        }
    },

    computed: {
        postURL() {
            return '/api/worksheets/' + this.wid + '/' + this.pid
        },
        worksheetFields() {
            var sgroup = 0;
            for(var p in this.fields) {
                if(this.fields.hasOwnProperty(p)) {

                    // JSON.parse fields with json values
                    this.fields[p].value = this.fields[p].value.length
                        && JSON.parse(this.fields[p].value);
                    // Create field name
                    this.fields[p].name = this.fieldLabel + this.fields[p].form_field_id;
                    // Create field wrapper ID
                    this.fields[p].wrapperID = this.fieldLabel + 'wrapper' + this.fields[p].form_field_id;
                    // Create Group ID
                    this.fields[p].groupID = this.fieldLabel + 'group' + this.fields[p].group_id;

                    //sgroup = this.fields[p].group_id;
                    if(this.fields[p].group_id == sgroup) {
                        this.fields[p].sameGroup = 1;
                    } else {
                        sgroup = this.fields[p].group_id;
                        this.fields[p].sameGroup = 0;
                    }

                   // Create col size class
                    this.fields[p].colSize = 'col-md-' + this.fields[p].display_size;
                    // Create answer
                    if(this.fields[p].answer === undefined || this.fields[p].answer === null) {
                        this.fields[p].type == 3 // radio
                        ? Vue.set(this.fields[p], 'answer', 0)
                        : Vue.set(this.fields[p], 'answer', '');
                    }
                    // Create image data
                    if(this.fields[p].type == 5) {
                        var fieldname = this.fields[p].name;
                        Vue.set(this.imageData, fieldname, '');
                    }

                    // Set date
                    if(this.fields[p].question == 'Date') {
                        Vue.nextTick( () => {
                            this.startDate = this.fields[p].answer.length > 0
                            ? this.fields[p].answer
                            : ''
                        });
                    }
                }
            }
            return this.fields;
        }
    },


    methods: {
        onSubmit(event) {
            var submissioncheck = 0;
            if (!submissioncheck) { // check number of fields completed.
                event.preventDefault();
                return false;
            }
        },
        addGroup() {

        },
        showForm() {
            document.getElementById('loading-circle').classList.add('d-none');
            this.loadingComplete = true;
        },

        radioAnswer(fieldID, event, fieldWrapperID) {
            $('.'+fieldWrapperID).find('input').not(event.target).prop('checked', false);
            this.storeAnswer(fieldID, event);
            //status == 200 && this.getAnswer();
        },
        checkboxAnswer(fieldID, event, fieldWrapperID) {
            $('.'+fieldWrapperID).find('input').not(event.target).removeClass('completed');
            var answer = !$(event.target).is(':checked') ? '' : event.target.value;
            this.storeAnswer(fieldID, event, answer);
        },

        getAnswers() { // essentially unused at the moment
            axios
                .get(this.postURL)
                .then(response => {
                    this.fields = response.data;
                })
                .catch(error => {
                    console.log(error)
                    //this.errored = true
                })
                .finally(() => this.initialState = false)
        },

        storeAnswer(fieldID, event, answer) {
            var selectedAnswer = (answer !== undefined) ? answer : event.target.value;
            //console.log(this.isDataURLregex.test(selectedAnswer));
            // axios
            //     .post(this.postURL, {
            //         form_field_id: fieldID,
            //         answer: selectedAnswer
            //     })
            //     .then(response => {
            //         console.log(response.status);
            //         if(response.status === 200) {
            //             event.target.classList.add('completed');
            //             var wrapper = document.getElementsByClassName('wfieldwrapper' + fieldID); // Figure this out the vue way
            //             [].forEach.call(wrapper, function(el) {
            //                 el.classList.add('label-completed');
            //             });
            //         }
            //         return response.status;
            //     })
            //     .catch(error => {
            //         console.log(error);
            //     })
                //.finally(() => console.log(selectedAnswer))
        },

        processImage(fieldID, event) {
            // Reference to the DOM input element
            var input = event.target;
            var fieldname = this.fieldLabel + fieldID;
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                // Define a callback function to run, when FileReader finishes its job
                reader.onload = (e) => {
                    // Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
                    // Read image as base64 and set to imageData
                    this.imageData[fieldname] = e.target.result;
                    this.storeAnswer(fieldID, event, this.imageData[fieldname]);
                }
                // Read file as a data url (base64 format)
                reader.readAsDataURL(input.files[0]);


            }
        },
    },

    mounted() {
        //var initialState = 0;
        var initialState = JSON.parse(window.__INITIAL_STATE__) || {};
        if(initialState.length) {
            this.fields = initialState;
            this.showForm()
        } else {
            this.getAnswers()
        }
    }
};
</script>

<style lang="scss" scoped>
.completed {
    position: relative;
    color: $dark-secondary;
    background-color: lighten($light2-secondary, 5%);
}

input[type=checkbox].completed:checked+label:before,
input[type=radio].completed:checked+label:before {
    background-color: lighten($light2-secondary, 5%);
}

input[type=checkbox].completed:checked+label:after,
input[type=radio].completed:checked+label:after {
    background: url(data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%227.692%22%20height%3D%225.894%22%20viewBox%3D%220%200%207.692%205.894%22%3E%3Ctitle%3Eicons%3C/title%3E%3Cpath%20d%3D%22M7.552%201.488L3.96%205.08l-.675.674a.48.48%200%200%201-.676%200l-.675-.674-1.8-1.8a.48.48%200%200%201%200-.676L.81%201.93a.48.48%200%200%201%20.675%200l1.46%201.463L6.2.137a.484.484%200%200%201%20.676%200l.674.674a.476.476%200%200%201%200%20.677z%22%20fill%3D%22%23198c5e%22/%3E%3C/svg%3E) 0 0 / 11px no-repeat;
}

.label-completed,
.worksheet-completed {
    &:after {
        display: block;
        padding-right: 1rem;
        position: absolute;
        font-size: .7rem;
        top: .5rem;
        right: 1rem;
        color: $secondary;
        background: transparent get-icon(checkmark, $secondary) 100% 50% / 11px no-repeat;
        content: 'Saved';
    }
}

.worksheet-completed {
    display: inline-block;
    clear: both;
    &:after {
        top: -2rem;
        left: 0;
        right: 0;
        font-size: 1rem;
        background-position: 20% 50%;
        background-size: 1rem;
    }
}

.flatpickr-input.form-control[readonly]:not(.completed) {
    background-color: $white;
}

</style>
