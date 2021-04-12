<template>
    <div v-stopFileDrag class="manage-assignments">
        <view-file
            v-if="viewFile"
            :view-file="viewFile"
            @close="closeAssignment">
        </view-file>
        <p>Create, manage and search for pdf, doc, and linked custom assignments.</p>
        <!-- <pre class="small">{{ assignments.course_pages }}</pre> -->
        <!-- Search -->
        <div v-if="updatedFiles.length > 0" class="form-wrapper form-wrapper-search p-2 mb-4">
            <div class="flex-row row">
                <div class="col-6 col-md-3">
                    <div class="form-group">
                        <input
                            id="searchterm"
                            v-model="searchCriteria.term"
                            v-tooltip:term="'Search by date (month, day, and/or year), class name, assignment type, file name and label.'"
                            type="text"
                            placeholder="Search Term"
                            class="form-control form-control-sm">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-group">
                        <multiselect
                            id="searchcategory"
                            v-model="searchCriteria.category"
                            v-tooltip:category="'Points represent total points of activated files for an assignment type.'"
                            :options="categories"
                            :close-on-select="true"
                            select-label="Select"
                            deselect-label="Remove"
                            label="name"
                            class="multiselect-sm"
                            placeholder="Filter by Category">
                            <template
                                v-for="slotName in ['option', 'singleLabel']"
                                :slot="slotName"
                                slot-scope="props">
                                {{ props.option.name }}
                                <!-- eslint-disable-next-line vue/require-v-for-key -->
                                <span
                                    v-if="props.option.totalPoints"
                                    class="points small">
                                    ({{ props.option.totalPoints }} Points)
                                </span>
                            </template>
                        </multiselect>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-group">
                        <multiselect
                            v-model="searchCriteria.class"
                            v-tooltip:class="'Assignments represent total of activated assignments for a class.'"
                            :options="classes"
                            :close-on-select="true"
                            select-label="Select"
                            deselect-label="Remove"
                            label="name"
                            class="multiselect-sm"
                            placeholder="Filter by Class">
                            <template
                                v-for="slotName in ['option', 'singleLabel']"
                                :slot="slotName"
                                slot-scope="props">
                                {{ props.option.name }}
                                <!-- eslint-disable-next-line vue/require-v-for-key -->
                                <span
                                    v-if="props.option.filesCount"
                                    class="points small">
                                    ({{ props.option.filesCount }} Assignments)
                                </span>
                            </template>
                        </multiselect>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="form-group">
                        <multiselect
                            v-model="searchCriteria.status"
                            v-tooltip:status="'Show only activated or deactivated assignments'"
                            :options="statuses"
                            :close-on-select="true"
                            select-label="Select"
                            deselect-label="Remove"
                            class="multiselect-sm"
                            placeholder="Status">
                        </multiselect>
                    </div>
                </div>
                <div class="col-md-1">
                    <button
                        id="searchreset"
                        v-tooltip:reset="'Reset search and sort by date created and activated status'"
                        type="button"
                        class="btn btn-block btn-sm btn-light btn-filter px-1"
                        @click="resetSearchAndSort">
                        Reset
                    </button>
                </div>
            </div>
        </div>
        <!-- End Search -->
        <div
            v-show="dragging"
            ref="fileDrop"
            class="form-wrapper-file-dragging"
            @dragover.prevent
            @drop.prevent="inputFiles">
            <span>Drop files</span>
        </div>
        <div
            ref="formWrapper"
            class="form-wrapper-file form-upload-display"
            @drop.prevent="inputFiles"
            @dragover.prevent
            @dragenter="dragging = true">
            <div class="flex-row row align-items-center p-2">
                <div class="col">
                    <span class="btn btn-sm btn-light btn-block btn-file">
                        <span>Browse Files</span>
                        <input
                            class="form-control-file"
                            type="file"
                            multiple
                            @change="inputFiles" />
                    </span><!-- accept="application/msword, application/pdf, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/rtf"-->
                </div>
                <!-- Uploaded Files -->
                <div class="col-10 text-gray small d-none d-lg-block">
                    <span v-if="uploadedFiles.length == 0">
                        Drag and drop <strong>pdf, doc</strong> and <strong>image</strong> files onto the page or browse
                    </span>
                    <ul v-else class="form-dropped-files">
                        <li v-for="(uf, index) in uploadedFiles" :key="index">
                            <i v-if="uf.type.indexOf('word') !== -1" class="fas fa-file-word"></i>
                            <i v-else-if="['image/jpg', 'image/jpeg', 'image/png', 'image/gif'].includes(uf.type)" class="fas fa-file-image"></i>
                            <i v-else-if="uf.type.indexOf('pdf') !== -1" class="fas fa-file-pdf"></i>
                            <i v-else class="fas fa-file-alt"></i>
                            {{ uf.name }}
                        </li>
                    </ul>
                    <div v-if="uploadError" class="text-danger">
                        {{ uploadError }}
                    </div>
                </div>
                <!-- End Uploaded Files -->
            </div>

            <!-- Upload indicator -->
            <div class="row">
                <div class="col position-relative">
                    <hr class="m-0" />
                    <div class="progress-assignment-wrapper">
                        <div
                            v-show="uploadPercentage && uploadPercentage !== 100"
                            class="progress progress-assignment">
                            <div
                                class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar"
                                :aria-valuenow="uploadPercentage"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                :style="'width:' + uploadPercentage + '%'">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Upload indicator -->

            <!-- Add link -->

            <div class="flex-row row align-items-center p-2">
                <div class="col">
                    <button class="btn btn-sm btn-block btn-light p-1" @click="activateLink">Add Link</button>
                </div>
                <div class="col-10 text-gray small">
                    <span v-if="!addLink" class="py-1" @click="activateLink">Add link to an assignment</span>
                    <input
                        v-else
                        ref="linkInput"
                        v-model="link"
                        type="text"
                        class="form-control form-control-sm placeholder-sm"
                        placeholder="Add link and press enter"
                        @change="saveLink">
                </div>
            </div>
            <!-- Add link -->
            <hr class="m-0 mb-1 mt-2 mt-lg-0 mb-lg-0" />

            <!-- Notices -->
            <notification :msg="notice" class="small" position="left ml-2" style="top: .5rem;" />
            <!-- Notices -->

            <!-- Sort files menu -->
            <div v-if="currentFiles.length > 0" class="row sort-menu p-1">
                <div class="col small">
                    <ul class="wrapper">
                        Sort by:
                        <li><span class="link" :class="{ active: sortActiveLink === 'label'}" @click="sortFilesAction('label'); sortSwitch = !sortSwitch">Label</span></li>
                        <li><span class="link" :class="{ active: sortActiveLink === 'file_name'}" @click="sortFilesAction('file_name'); sortSwitch = !sortSwitch">File Name</span></li>
                        <li><span class="link" :class="{ active: sortActiveLink === 'points'}" @click="sortFilesAction('points'); sortSwitch = !sortSwitch">Points</span></li>
                        <li><span class="link" :class="{ active: sortActiveLink === 'created'}" @click="sortFilesAction('created'); sortSwitch = !sortSwitch">Date</span></li>
                    </ul>
                    <div v-if="checkedDeletes.length" class="btn-wrapper">
                        <button type="button" class="btn btn-sm btn-danger animated fast bounceInRightShort" @click="deleteConfirm = !deleteConfirm">Delete</button>
                        <popover-dialogue v-if="deleteConfirm" :id="1" @answer="deleteFiles">
                            Are you sure? This cannot be undone.
                        </popover-dialogue>
                    </div>
                </div>
            </div>
            <!-- End Sort files menu -->

            <div v-if="isSearching && currentFiles.length === 0">
                No results.
            </div>
            <div v-if="updatedFiles.length === 0" class="file-drop-area text-center">
                <strong>Begin with adding <span class="text-primary">pdf</span> and <span class="text-primary">doc</span> files by dropping them into this area or via the 'Browse files' button.</strong>
            </div>

            <!-- File listing -->
            <transition-group :name="(animate ? 'row' : 'page')" class="row-transition" mode="out-in">
                <div
                    v-for="file in currentFiles"
                    :key="file.id"
                    :class="{'highlight-file': editing_id === file.id}"
                    class="small row-file row-animate alternate-bg p-2 py-3">
                    <!-- File name -->
                    <div class="flex-row row align-items-center">
                        <div
                            class="col-md-12 col-lg-12"
                            :class="[file.new === true ? 'text-success' : 'text-dark-gray']">
                            <a
                                v-if="file.type === 2 && !file.file_screenshot"
                                :href="file.file_location"
                                style="text-decoration: none;"
                                target="_blank">
                                {{ file.file_name }} -
                                <span class="text-gray">{{ file.createdFormatted }}</span>
                            </a>
                            <span
                                v-else
                                style="cursor: pointer;"
                                @click="viewAssignment(file)">
                                {{ file.file_name }} - <span class="text-gray">{{ file.createdFormatted }}</span>
                            </span>
                            <span v-if="file.new && file.type === 2" @click="viewAssignment(file)">
                                 - <span class="succes">Generating screenshot...</span>
                                 <span v-if="screenshotGenerated.includes(file.id)" class="success">done!</span>
                            </span>
                            <span v-if="checkRequestStatus(file.id, 'delete')"><i class="fas text-danger fa-times"></i></span>
                        </div>
                    </div>
                    <!-- Label -->
                    <div class="flex-row row align-items-center">
                        <div class="col-md-7 col-lg-4 form-filename">
                            <div class="form-group">
                                <label>
                                    <i v-if="file.type === 2" class="far fas fa-link" style="font-size: .75rem;"></i>
                                    <i v-else-if="file.file_name.toLowerCase().indexOf('doc') !== -1" class="far fa-file-word"></i>
                                    <i v-else-if="file.file_name.toLowerCase().indexOf('pdf') !== -1" class="far fa-file-pdf"></i>
                                    <i v-else-if="fileIsImage(file.file_name)" class="far fa-file-image"></i>
                                    <i v-else class="far fa-file-image"></i>
                                </label>
                                <span v-if="checkRequestStatus(file.id, 'label')" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                                <input
                                    v-model="file.label"
                                    v-tooltip:label="'This will be used as the assignment\'s name in a student\'s list of assignments.'"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="Assignment Label"
                                    @change="updateFile($event, file.id)">
                            </div>
                        </div>
                        <!-- Points -->
                        <div class="col-md-2 col-lg-1">
                            <div class="form-group">
                                <span v-if="checkRequestStatus(file.id, 'points')" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                                <input
                                    v-model="file.points"
                                    v-tooltip:points="'Assign the amount of points this assignment is worth.'"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="Points"
                                    @change="updateFile($event, file.id, 'points')">
                            </div>
                        </div>
                        <!-- Category -->
                        <div class="col-md-6 col-lg-2">
                            <div class="form-group form-group-multiselect">
                                <span v-if="checkRequestStatus(file.id, 'category_id')" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                                <multiselect
                                    v-model="file.category"
                                    v-tooltip:categories="'Assign a category to each assignment.'"
                                    :options="categories"
                                    :searchable="false"
                                    :close-on-select="true"
                                    :show-labels="false"
                                    label="name"
                                    track-by="id"
                                    class="multiselect-sm"
                                    placeholder="Category"
                                    @select="updateFile($event, file.id, 'category_id')">
                                </multiselect>
                            </div>
                        </div>
                        <!-- Classes -->
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group form-group-multiselect">
                                <span v-if="checkRequestStatus(file.id, 'class')" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                                <multiselect
                                    v-model="file.classes"
                                    v-tooltip:classes="'Assign multiple classes from the list. Unassign classes by clicking on a selected class from the list.'"
                                    :options="classes"
                                    :multiple="true"
                                    :close-on-select="false"
                                    :taggable="false"
                                    :clear-on-select="false"
                                    :preserve-search="true"
                                    :searchable="false"
                                    label="name"
                                    track-by="id"
                                    select-label="Select"
                                    deselect-label="Remove"
                                    class="multiselect-sm"
                                    placeholder="Assign to classes"
                                    @remove="updateFileClass($event, file.id, 0)"
                                    @select="updateFileClass($event, file.id, 1)">
                                    <template slot="selection" slot-scope="{ values }">
                                        <span v-if="values.length" class="multiselect__single">Assigned {{ values.length }} classes</span>
                                    </template>
                                </multiselect>
                            </div>
                        </div>
                        <!-- Due date -->
                        <div class="col-md-2 col-lg-1 text-center position-relative" style="padding: 0;">
                            <!-- <a
                                v-if="file.classes.length"
                                :href="`/edit/duedates/${file.id}/2`"
                                class="text-dark-gray">Due Date<i class="far fa-calendar-alt ml-1" aria-hidden="true"></i>
                            </a>
                            <span v-else class="text-muted" style="position:relative;left:-.4rem">No class</span> -->
                            <a
                                v-if="file.classes.length"
                                href="#"
                                class="text-dark-gray btn-duedate"
                                @click.prevent.stop="toggleDueDate(file.id)">Due Date<i class="far fa-calendar-alt ml-1" aria-hidden="true"></i>
                            </a>
                            <span v-else class="text-muted" style="position: relative; left: -.4rem;">No class</span>
                            <div v-closeDueDate class="duedate-wrapper">
                                <due-dates
                                    v-if="dueDate === file.id"
                                    :assignment="file"
                                    :type="2"
                                    class="border card-box-shadow" />
                            </div>
                        </div>
                        <!-- Active / Delete -->
                        <div class="col-md-12 col-lg-1 custom-control-end">
                            <div class="custom-control custom-checkbox form-check-inline small">
                                <span v-if="checkRequestStatus(file.id, 'status')" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                                <input
                                    :id="'active'+file.id"
                                    v-model="file.status"
                                    type="checkbox"
                                    :disabled="checkedDeletes.length > 0"
                                    class="custom-control-input custom-control-input-active"
                                    @change="updateFile($event, file.id, 'status')">
                                <label class="custom-control-label" :for="'active'+file.id">Active</label>
                            </div>
                            <div class="custom-control custom-checkbox form-check-inline small">
                                <input
                                    :id="'delete'+file.id"
                                    v-model="checkedDeletes"
                                    :value="file.id"
                                    type="checkbox"
                                    :class="checkedDeletes.length && 'custom-control-input-highlight'"
                                    class="custom-control-input custom-control-input-delete">
                                <label
                                    :class="checkedDeletes.includes(file.id) && 'text-danger'"
                                    class="custom-control-label"
                                    :for="'delete'+file.id">
                                    Delete
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="flex-row row align-items-center">
                        <div class="col-md-4 col-lg-5">
                            <div v-if="file.classes.length" class="form-check-inline mr-0">
                                <div class="custom-control custom-checkbox custom-checkbox-insert small">
                                    <span v-if="checkRequestStatus(file.id, 'insert-status')" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                                    <input
                                        :id="'insert'+file.id"
                                        v-model="checkedInsert"
                                        :value="file.id"
                                        type="checkbox"
                                        class="custom-control-input"
                                        @change="updateInsertStatus($event, file.classes)">
                                    <label
                                        class="custom-control-label"
                                        :for="'insert'+file.id">
                                        {{ checkedInsert.includes(file.id) ? 'Inserted' : 'Insert' }} into courseware pages
                                    </label>
                                </div>
                            </div>
                            <div v-if="file.classes.length && checkedInsert.includes(file.id)" class="form-check-inline mr-0">
                                <div
                                    class="custom-control custom-checkbox custom-checkbox-insert small"
                                    :class="{'ml-2': checkRequestStatus(file.id, 'insert-status')}">
                                    <input
                                        :id="'insertall'+file.id"
                                        v-model="checkedInsertAll"
                                        :value="file.id"
                                        type="checkbox"
                                        class="custom-control-input"
                                        @change="updateFilePages($event, file.id, file.classes, true)">
                                    <label
                                        class="custom-control-label"
                                        :for="'insertall'+file.id">
                                        {{ checkedInsertAll.includes(file.id) ? 'Deselect' : 'Select' }} all pages
                                    </label>
                                </div>
                            </div>
                            <!-- <a
                                v-if="checkedInsert.includes(file.id)"
                                href="#"
                                class="small insert-manage"
                                :class="{'ml-2': checkRequestStatus(file.id, 'insert-status')}"
                                @click.prevent="manageInsert = manageInsert === file.id ? 0 : file.id">{{ manageInsert === file.id ? 'Hide' : 'Manage' }}</a> -->
                        </div>
                        <div class="col-md-8 col-lg-5 position-relative">
                            <div class="insert-dropdown">
                                <div v-if="checkedInsert.includes(file.id)" class="form-group form-group-multiselect">
                                    <span v-if="checkRequestStatus(file.id, 'pages')" :class="requestStatusCSS"><i class="fas fa-check"></i></span>
                                    <multiselect
                                        v-model="file.course_pages"
                                        v-tooltip:coursepages="'Add a link to this assignment to the sidebar of a course page.'"
                                        :options="getPageOptions(file.classes)"
                                        :multiple="true"
                                        :close-on-select="false"
                                        :taggable="false"
                                        :clear-on-select="false"
                                        :preserve-search="true"
                                        :searchable="true"
                                        label="title"
                                        track-by="id"
                                        select-label="Select"
                                        deselect-label="Remove"
                                        class="multiselect-xs"
                                        :placeholder="coursewarePlaceholderId === file.id ? 'Search...' : 'Insert Courseware pages'"
                                        @open="coursewarePlaceholderId = file.id"
                                        @close="coursewarePlaceholderId = 0"
                                        @input="updateFilePages($event, file.id, file.classes)">
                                        <template slot="selection" slot-scope="{ values }">
                                            <span v-if="values.length" class="multiselect__single">Inserted into {{ values.length }} pages</span>
                                        </template>
                                        <template
                                            slot="singleLabel"
                                            slot-scope="{ option }">
                                            <span>{{ option.title }}</span>
                                        </template>
                                        <template slot="noOptions">
                                            Assign a class first
                                        </template>
                                        <template slot="noResult">
                                            No pages found.
                                        </template>
                                    </multiselect>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <hr v-if="index != currentFiles.length - 1" class="m-0 my-2" /> -->
                </div>
            </transition-group>
            <!-- End File listing -->
        </div>
        <pagination
            v-show="totalFiles / resultsPerPage > 1"
            :total="totalFiles"
            :results-per-page="resultsPerPage"
            :current-page="currentPage"
            class="mt-3"
            @pagechanged="paginateInanimate">
        </pagination>
        <!-- <div v-if="checkedDeletes.length" class="bottom-btn-wrapper">
            <button type="button" class="btn btn-sm btn-danger animated fast bounceInRightShort" @click="deleteConfirm = !deleteConfirm">Delete files</button>
            <popover-dialogue v-if="deleteConfirm" :id="1" @answer="deleteFiles">
                Are you sure? This cannot be undone.
            </popover-dialogue>
        </div> -->
    </div>
</template>


<script>
import { formatDate, findObjMoveTop, shortenString, fileNameIsImage } from '../functions/utils';
import apiRequest from '../functions/apiRequest';
import notify from '../mixins/notify';
import dueDatesMixin from '../mixins/dueDatesMixin';
import stopFileDrag from '../directives/stopFileDrag';

export default {
    name: 'ManageAssignments',

    directives: {
        stopFileDrag
    },

    components: {
        Multiselect: () => import(/* webpackChunkName:"vue-multiselect" */ 'vue-multiselect'),
        ViewFile: () => import(/* webpackChunkName: 'view-file' */ './common/ViewFile')
    },

    mixins: [notify, dueDatesMixin],

    props: {
        view: {
            type: String,
            default: ''
        },
        editing_id: {
            type: Number,
            default: 0
        },
        assignments: {
            type: Object,
            required: true
        }
    },

    data() {
        const vm = this;
        return {
            animate: true,
            dragging: false,
            uploadedFiles: [],
            uploadPercentage: 0,
            uploadError: '',
            currentPage: 1,
            resultsPerPage: 9,
            searchCriteria: {
                term: null,
                category: null,
                class: null,
                status: null
            },
            link: '',
            addLink: false,
            viewFile: null,
            deleteConfirm: false,
            checkedDeletes: [],
            checkedInsert: [],
            checkedInsertAll: [],
            statuses: ['Activated', 'Deactivated'],
            categories: vm.assignments.categories,
            classes: vm.assignments.classes,
            updatedFiles: vm.assignments.files,
            currentFiles: null,
            searchedFiles: null,
            totalPoints: 0,
            coursewarePlaceholderId: 0,
            cookie: {},
            sortSwitch: true,
            sortActiveLink: '',
            requestStatus: {
                id: null,
                type: null
            },
            screenshotGenerated: [],
            screenshotPolling: []
        };
    },
    computed: {
        isSearching() {
            return Object.values(this.searchCriteria).some(val => val !== null && val !== '');
        },
        totalFiles() {
            return this.isSearching ? this.searchedFiles.length : this.updatedFiles.length;
        }
    },

    watch: {
        searchCriteria: {
            deep: true,
            handler: function(val) {
                this.compileSearch(val);
            }
        },
        checkedDeletes(val) {
            this.deleteConfirm = this.deleteConfirm === true && val.length > 0;
        }
    },

    created() {
        const vm = this;

        // Request status animation css classes
        vm.requestStatusCSS = 'form-request-status animated faster bounceInLeftShort complete';

        // Get and set cookie
        vm.cookie = vm.getCookie();
        vm.setupTooltipCookie(vm.cookie, {
            term: 0,
            category: 0,
            class: 0,
            reset: 0,
            status: 0,
            label: 0,
            points: 0,
            classes: 0,
            categories: 0,
            team: 0,
            coursepages: 0
        });
        vm.setCookie(vm.cookie);

        // Format created and updated propertes
        vm.assignments.files.map(f => {
            // shorten URLs for display
            f.type === 2 && (f.file_name = shortenString(f.file_name));

            // Don't display label in placeholder if the same as file_name
            f.file_name === f.label && (f.label = '');

            // Check for insert status
            if(f.classes.some(obj => obj.insert_status)) {
                vm.checkedInsert.push(f.id);
            }

            // Check for insert all pages
            if(f.course_pages.length === vm.getPageOptions(f.classes).length) {
                vm.checkedInsertAll.push(f.id);
            }

            // Create searchable, formatted date properties
            f.createdFormatted = formatDate(f.created);
            f.createdFormattedLong = formatDate(f.created, 'long');
            return f;
        });

        // Tally up total category points and assign to a categories prop
        if (vm.updatedFiles.filter(obj => obj.points !== null).length) {
            vm.getTotalPoints(vm.updatedFiles);
        }

        // Tally up total assigned files for a class
        vm.getTotalClassFiles(vm.updatedFiles);

        // Editing / Viewing
        if(vm.editing_id) {
            findObjMoveTop(vm.updatedFiles, vm.editing_id, false)
            if(vm.view) {
                const file = vm.updatedFiles.find(f => f.id == vm.editing_id);
                vm.viewAssignment(file);
            }
        }

        // Show only the first page of files on created
        vm.currentFiles = vm.updatedFiles.slice(0, vm.resultsPerPage);
    },

    mounted() {
        const vm = this;
        // Set height of file drop overlay to div wrapping the file list
        vm.$refs.fileDrop.style.height = vm.$refs.formWrapper.offsetHeight + 'px';
        vm.$refs.fileDrop.style.width = vm.$refs.formWrapper.offsetWidth + 'px';
    },

    beforeDestroy() {
        if(this.screenshotPolling.length) {
            this.screenshotPolling.forEach((sp,i) => clearInterval(i));
        }
    },

    methods: {
        getPageOptions(classes) {
            const slcdClasses = classes.map(c => c.class_type);
            const pages = this.assignments.course_pages.filter(page => slcdClasses
                .some(clsType => page.class_types.includes(clsType)));
            return [ ...new Set(pages) ];
        },
        //
        // Paginate
        // --------------------------------------------------------------------------
        paginate(page) {
            const vm = this;
            vm.currentPage = page;
            --page; // eslint-disable-line no-param-reassign
            if (vm.isSearching) {
                vm.currentFiles = vm.searchedFiles.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            } else {
                vm.currentFiles = vm.updatedFiles.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            }
        },
        paginateInanimate(page) {
            this.animate = false;
            this.paginate(page);
            setTimeout(() => (this.animate = true), 500);
        },

        //
        // View / Close Assignment
        // --------------------------------------------------------------------------
        viewAssignment(file) {
            this.viewFile = {
                id: file.id,
                name: file.label,
                location: file.file_location,
                screenshot: file.file_screenshot
            };
        },

        closeAssignment() {
            if(this.view) {
                window.history.back();
            } else {
                this.viewFile = null;
            }
        },

        //
        //  Compile Search
        // --------------------------------------------------------------------------
        compileSearch(val) {
            let search = '';
            // Iterate through search criteria keys and build search string
            Object.keys(val).forEach(k => {
                // If search key is an object (class, categories), use 'name' property value
                if (val[k] !== null) {
                    search += typeof val[k] === 'object' ? ' ' + val[k].name : ' ' + val[k];
                }
            });
            this.searchFilesByTerm(search);
        },
        //
        // Search Files
        // --------------------------------------------------------------------------
        searchFilesByTerm(search) {
            const vm = this;
            if (typeof search === 'undefined' || search === '') {
                vm.currentFiles = vm.updatedFiles;
                vm.reset();
                return;
            }

            // Split multiple search terms into array for iteration
            let searchTerms = typeof search === 'string' ? search.split(' ') : search;

            // Search using all lowercase and remove observer data from file data
            searchTerms = searchTerms.map(s => s.toLowerCase());
            let files = JSON.parse(JSON.stringify(vm.updatedFiles));

            // Exclude properties that shouldn't be searched
            files = files.map(({ file_location, created, updated, ...obj }) => obj);

            // Reduce classes to array of name values
            // Reduce category object to name of category
            Object.keys(files).forEach(f => {
                files[f].classes = files[f].classes.map(c => c.name);
                files[f].category.name &&
                    (files[f].category = files[f].category.name.toLowerCase());
            });

            // Determine if search terms include ^active/a, ^deactive/a, or ^inactive/a
            let status = null;
            const tests = [
                { status: 1, regex: /(^|\s)[aA]ctiv[ea]/g },
                { status: 0, regex: /(^|\s)[dD]eactiv[ea]/g },
                { status: 0, regex: /(^|\s)[iI]nactiv[ea]/g }
            ];
            // Iterate through tests, if found in searchTerms, remove from searchTerms array
            // since there is no value in files obj to search for and assign its status
            tests.forEach(t => {
                let index;
                if ((index = searchTerms.findIndex(term => t.regex.test(term))) !== -1) {
                    // eslint-disable-line no-cond-assign
                    searchTerms.splice(index, 1);
                    status = t.status;
                }
            });

            // Filter assigned status results
            status !== null && (files = files.filter(obj => obj.status === status));

            // Filter by iterating through search terms
            searchTerms.forEach(
                term =>
                    (files = files.filter(obj =>
                        Object.keys(obj).some(key =>
                            // Determine if values exist for regular term or probable status search
                            String(obj[key]).toLowerCase().includes(term)
                        )
                    ))
            );

            // Get only IDs from results
            let fileIDs = files.map(f => f.id);

            // Assign the results to searchedFiles (for paginate)
            vm.searchedFiles = vm.updatedFiles.filter(obj => fileIDs.indexOf(obj.id) !== -1);

            vm.paginate(1);
        },

        //
        // Reset Search
        // --------------------------------------------------------------------------
        reset() {
            const vm = this;
            Object.keys(vm.searchCriteria).forEach(k => (vm.searchCriteria[k] = null));
            vm.searchedFiles = null;
            vm.currentFiles = vm.updatedFiles.slice(0, vm.resultsPerPage);
            vm.paginate(1);
            vm.checkedDeletes = [];
        },
        resetSearchAndSort() {
            const vm = this;
            vm.updatedFiles = vm.sortFiles(vm.updatedFiles, 'status', 'created');
            vm.reset();
            vm.setTooltipCookie(vm.cookie, 'reset', vm.cookie);
        },

        //
        // Sort Files
        // --------------------------------------------------------------------------
        sortFiles(obj, ...props) {
            const vm = this,
                sortDate = (a, b) => (b < a ? -1 : b > a ? 1 : 0),
                sortNum = (a, b) => b - a,
                sortAlpha = (a, b) => a.localeCompare(b);

            return obj.sort((a, b) => {
                // Sort Menu
                if (props.length === 1) {
                    // Asc / Desc switch
                    vm.sortSwitch || ([a, b] = [b, a]); // eslint-disable-line no-param-reassign

                    // Loosely match ISO 8601 date
                    if (/([0-5][0-9])(.[0-9]+)?(Z)?$/.test(a[props[0]]) && props[0] === 'created') {
                        return sortDate(a[props[0]], b[props[0]]);
                    } else if (['status', 'points'].includes(props[0])) {
                        return sortNum(a[props[0]], b[props[0]]);
                    } else if (['label', 'file_name'].includes(props[0])) {
                        return sortAlpha(a[props[0]], b[props[0]]);
                    }

                    // Reset Search - status > created
                } else {
                    return sortNum(a[props[0]], b[props[0]]) || sortDate(a[props[1]], b[props[1]]);
                }
            });
        },
        sortFilesAction(prop) {
            const vm = this;
            vm.sortActiveLink = prop;

            if (vm.isSearching) {
                vm.searchedFiles = vm.sortFiles(vm.searchedFiles, prop);
            } else {
                vm.updatedFiles = vm.sortFiles(vm.updatedFiles, prop);
            }
            vm.paginate(1);
        },

        //
        // Get Total Points
        // --------------------------------------------------------------------------
        getTotalPoints(filesObj) {
            const vm = this;
            Object.keys(vm.categories).forEach(c => {
                const cat = vm.categories[c];

                // Get activated files that have assigned cat and points > 0
                const temp = filesObj.filter(
                    obj => obj.status && obj.category.name === cat.name && obj.points > 0
                );

                // Tally up points and add a totalPoints prop if these files exist
                if (temp.length) {
                    cat.totalPoints = temp
                        .map(obj => obj.points)
                        .reduce((total, current) => +total + +current);
                }
            });
            vm.$forceUpdate(); // Slap multiselect's singleLabel slot
        },

        //
        // Get Total Points
        // --------------------------------------------------------------------------
        getTotalClassFiles(filesObj) {
            const vm = this;
            Object.keys(vm.classes).forEach(c => {
                const cls = vm.classes[c];

                // Get activated  files that have assigned class
                const temp = filesObj.filter(
                    obj => obj.status && obj.classes.some(assignedCls => assignedCls.id === cls.id)
                );

                // Assign numbers of files to filesCount prop if there are any
                cls.filesCount = temp.length > 0 && temp.length;
            });

            vm.$forceUpdate(); // Slap multiselect's singleLabel slot
        },

        //
        // Process New Files
        // --------------------------------------------------------------------------
        processNewFile(fileID, fileName, fileLoc, created, type) {
            const fileNameModified = type === 2 ? shortenString(fileName) : fileName;
            const file = {
                id: fileID,
                file_name: fileNameModified,
                file_location: fileLoc,
                file_screenshot: '',
                type: type,
                label: '',
                points: null,
                classes: [],
                category: '',
                course_pages: [],
                created: created,
                createdFormatted: formatDate(created),
                createdFormattedLong: formatDate(created),
                status: 1,
                new: true
            };
            return file;
        },

        //
        // Update File Class
        // --------------------------------------------------------------------------
        updateFileClass(value, fileID, status) {
            const vm = this;
            const classes = vm.updatedFiles.find(obj => obj.id === fileID).classes;
            const request = {
                assignment_id: fileID,
                class_id: value.id,
                status: status,
                insert_status: 0
            };

            if(status) {
                // Copy other classes in the file's page selections and insert status
                const existingPages = classes.filter(obj => obj.course_pages !== null);

                if(existingPages.length) {
                    const pages = existingPages.flatMap(obj => obj.course_pages);
                    request.course_pages = [ ...new Set(pages) ]; // Remove dupes
                    request.insert_status = classes.some(obj => obj.insert_status) ? 1 : 0;
                }
            }
            vm.postData(request, '/update/assignment/class').then(response => {
                if(vm.checkedInsertAll.indexOf(fileID) !== -1) {
                    const file = vm.updatedFiles.find(obj => obj.id === fileID);
                    if(file.course_pages.length !== vm.getPageOptions(classes)) {
                        vm.checkedInsertAll = vm.checkedInsertAll.filter(el => el !== fileID);
                    }
                    // const file = vm.updatedFiles.find(obj => obj.id === fileID);
                    // console.log(vm.getPageOptions(classes));
                    // file.course_pages = vm.getPageOptions(classes);
                }
                vm.updateRequestStatus(response, fileID, 'class')
            });
        },
        //
        // Update File Append Pages
        // --------------------------------------------------------------------------
        updateFilePages(value, fileID, classes, all = false) {
            const vm = this,
                et = all ? value.target : {};

            const pages = all
                ? !et.checked
                    ? []
                    : vm.getPageOptions(classes)
                : value

            const request = {
                assignment_id: fileID,
                class_id: classes.map(c => c.id),
                course_pages: pages.length ? pages.map(p => p.id) : '',
                status: 1,
                insert_status: pages.length ? 1 : 0
            };

            vm.postData(request, '/update/assignment/insertpage').then(response => {
                const file = vm.updatedFiles.find(obj => obj.id === fileID);
                all && (file.course_pages = pages)
                vm.updateRequestStatus(response, fileID, 'pages')
            });
        },

        //
        // Update Insert Status
        // --------------------------------------------------------------------------
        updateInsertStatus(event, classes) {
            const vm = this;
            const et = event.target;
            const fileId = +et.id.replace('insert', '');
            const request = {
                assignment_id: fileId,
                class_id: classes.map(c => c.id),
                status: 1,
                insert_status: et.checked ? 1 : 0
            };
            vm.postData(request, '/update/assignment/insertstatus').then(response =>
                vm.updateRequestStatus(response, fileId, 'insert-status')
            );

        },

        //
        // Update File
        // --------------------------------------------------------------------------
        updateFile(event, fileID, type = 'label') {
            const vm = this;
            let request = { id: fileID };

            request[type] =
                type === 'status' || type === 'team'
                    ? +event.target.checked
                    : type === 'category_id'
                        ? event.id
                        : event.target.value;

            vm.postData(request).then(response =>
                vm.updateRequestStatus(response.success, fileID, type)
            );
        },
        deleteFiles(answer) {
            const vm = this;
            if (answer !== 'cancel') {
                vm.checkedDeletes.forEach((fileID, i) => {
                    setTimeout(() => {
                        vm.updatedFiles.splice(
                            vm.updatedFiles.findIndex(obj => obj.id === fileID), 1
                        );
                        if (vm.isSearching) {
                            vm.searchedFiles.splice(
                                vm.searchedFiles.findIndex(obj => obj.id === fileID), 1
                            );
                        }
                        //TODO: Make animation (pagination(currentPage)) work again. Deleting an entire page worth of files...
                        // leads to confusion
                        vm.paginate(1);
                        vm.postData({ id: fileID }, '/delete/assignment').then(() => {
                            vm.notify('Assignments have been deleted.')
                        });
                    }, i * 150);
                });
            }
            vm.checkedDeletes = [];
        },


        //
        // File is image
        // --------------------------------------------------------------------------
        fileIsImage(fileName) {
            return fileNameIsImage(fileName);
        },

        //
        // Input Files
        // --------------------------------------------------------------------------
        inputFiles(event) {
            const files = event.target.files || event.dataTransfer.files;
            if (!files.length) return;

            const vm = this;
            let formData = new FormData();

            vm.uploadError = ''; // Reset any error messages
            vm.dragging = false; // Remove overlay
            vm.uploadedFiles = []; // Remove any previously uploaded files

            // Add user_id to formData request
            // formData.append('user_id', vm.user_id);
            // Add type
            formData.append('type', 1);

            [...files].forEach(file => {
                // Check for acceptable file types.
                if (!/pdf|doc|docx|jpg|jpeg|png|gif/i.test(file.name) || file.name.indexOf('gdoc') !== -1) {
                    // Generate file type error.
                    vm.uploadError = `.pdf, .doc, .docx, .jpg, and .png files only.
                        Incompatible files have been skipped.`;
                } else {
                    // Add files to formData
                    formData.append('files[]', file);
                    // Add files to uploaded files area
                    vm.uploadedFiles.push(file);
                }
            });

            vm.uploadedFiles.length && vm.uploadFiles(formData);
        },

        //
        // Upload Files
        // --------------------------------------------------------------------------
        uploadFiles(request) {
            const vm = this;
            apiRequest('/store/assignment', request, {
                onUploadProgress: event => {
                    vm.uploadPercentage = parseInt(
                        Math.round((event.loaded * 100) / event.total),
                        10
                    );
                }
            }).then(response => {
                vm.reset();
                vm.uploadedFiles.forEach((f, i) => {
                    // Build files properties from response data
                    setTimeout(() => {
                        vm.updatedFiles.unshift(
                            vm.processNewFile(
                                response[i].id,
                                response[i].file_name,
                                response[i].file_location,
                                response[i].created,
                                response[i].type
                            )
                        );
                        vm.paginate(vm.currentPage);
                    }, i * 200);
                    setTimeout(() => vm.notify('Assignments have been added.'), vm.uploadedFiles.length * 350);
                });
            })
        },

        //
        // Post / Get Data from API
        // --------------------------------------------------------------------------
        postData(request, postURL = '/update/assignment') {
            const vm = this;
            return apiRequest(postURL, request).then(response => {
                vm.getTotalPoints(vm.updatedFiles);
                vm.getTotalClassFiles(vm.updatedFiles);
                return response
            })
        },
        updateRequestStatus(response, fileID, type) {
            if (response) {
                this.requestStatus.id = fileID;
                this.requestStatus.type = type;
            }
        },

        //
        // Check request Status
        // --------------------------------------------------------------------------
        checkRequestStatus(id, type) {
            return this.requestStatus.id === id && this.requestStatus.type === type;
        },

        //
        // Save link
        // --------------------------------------------------------------------------
        activateLink() {
            this.addLink = !this.addLink;
            this.$nextTick(() => this.$refs.linkInput.focus());
        },
        saveLink(event) {
            const link = event.target.value;
            if(!/https?:\/\/\S+\.+\S+/.test(link)) {
                this.notify('Add valid URLs starting with https:// or http:// only.', 'danger');
                return;
            }
            if(/inventionlandinstitute.\w{2,4}\/worksheets/gi.test(link)) {
                this.notify('Creating a custom assignment link to an activity sheet is not possible.', 'danger');
                return;
            }

            const vm = this;
            const request = {
                // user_id: vm.user_id,
                type: 2,
                link: link
            }
            apiRequest('/store/assignment', request).then(response => {
                vm.link = '';
                vm.updatedFiles.unshift(
                    vm.processNewFile(
                        response.id,
                        response.file_name,
                        response.file_location,
                        response.created,
                        response.type
                    )
                );
                vm.paginate(vm.currentPage);

                vm.screenshotPolling[response.id] = setInterval(() => {
                    apiRequest('/get/assignment/screenshot/'+response.id).then(response => {
                        if(response.success) {
                            const updatedFile = vm.updatedFiles.find(obj => obj.id === response.id);
                            updatedFile.file_screenshot = response.file_screenshot;

                            vm.screenshotGenerated.push(response.id);

                            if(vm.viewFile && vm.viewFile.id === response.id) {
                                vm.viewFile.screenshot = response.file_screenshot;
                            }

                            clearInterval(vm.screenshotPolling[response.id]);
                        }
                    });
                }, 3000);
            })
        }
    }
};
</script>
